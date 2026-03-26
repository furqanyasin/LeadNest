<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LeadNest_Crawler {

    private $visited   = array();
    private $max_pages = 20;

    /**
     * Crawl an entire site starting from a URL.
     * Tries sitemap.xml first, falls back to recursive link crawl.
     *
     * @param string $start_url
     * @param string $site_key
     * @param int    $max_pages
     * @return int   Number of pages saved.
     */
    public function crawl_site( $start_url, $site_key, $max_pages = 20 ) {
        $this->visited   = array();
        $this->max_pages = max( 1, (int) $max_pages );

        $base_url = $this->get_base_url( $start_url );
        $pages    = array();

        // Try sitemap first.
        $sitemap_urls = $this->parse_sitemap( $base_url . '/sitemap.xml' );
        if ( empty( $sitemap_urls ) ) {
            $sitemap_urls = $this->parse_sitemap( $base_url . '/sitemap_index.xml' );
        }

        if ( ! empty( $sitemap_urls ) ) {
            foreach ( array_slice( $sitemap_urls, 0, $this->max_pages ) as $url ) {
                $page = $this->crawl_page( $url );
                if ( $page ) {
                    $pages[] = $page;
                }
            }
        } else {
            // Recursive link crawl.
            $this->crawl_recursive( $start_url, $base_url, $pages );
        }

        $this->save_pages( $pages, $site_key );

        return count( $pages );
    }

    /**
     * Crawl a single page and return extracted data.
     *
     * @param string $url
     * @return array|null
     */
    public function crawl_page( $url ) {
        $response = wp_remote_get( $url, array(
            'timeout'    => 20,
            'user-agent' => 'LeadNest-Crawler/1.0 (WordPress plugin; contact admin)',
            'sslverify'  => false,
        ) );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( 200 !== $code ) {
            return null;
        }

        $content_type = wp_remote_retrieve_header( $response, 'content-type' );
        if ( ! empty( $content_type ) && strpos( $content_type, 'text/html' ) === false ) {
            return null;
        }

        $html = wp_remote_retrieve_body( $response );
        if ( empty( $html ) ) {
            return null;
        }

        return $this->extract_content( $html, $url );
    }

    /**
     * Parse a sitemap.xml and return an array of URLs.
     *
     * @param string $sitemap_url
     * @return array
     */
    public function parse_sitemap( $sitemap_url ) {
        $response = wp_remote_get( $sitemap_url, array(
            'timeout'    => 10,
            'user-agent' => 'LeadNest-Crawler/1.0',
            'sslverify'  => false,
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            return array();
        }

        $xml_string = wp_remote_retrieve_body( $response );
        if ( empty( $xml_string ) ) {
            return array();
        }

        libxml_use_internal_errors( true );
        $dom = simplexml_load_string( $xml_string );
        libxml_clear_errors();

        if ( ! $dom ) {
            return array();
        }

        $urls = array();

        // Sitemap index — contains child sitemaps.
        if ( isset( $dom->sitemap ) ) {
            foreach ( $dom->sitemap as $sitemap ) {
                $sub_urls = $this->parse_sitemap( (string) $sitemap->loc );
                $urls     = array_merge( $urls, $sub_urls );
                if ( count( $urls ) >= $this->max_pages ) {
                    break;
                }
            }
        }

        // Regular sitemap — contains page URLs.
        if ( isset( $dom->url ) ) {
            foreach ( $dom->url as $url_node ) {
                $urls[] = (string) $url_node->loc;
                if ( count( $urls ) >= $this->max_pages ) {
                    break;
                }
            }
        }

        return array_unique( $urls );
    }

    /**
     * Extract title, body text, and internal links from HTML.
     *
     * @param string $html
     * @param string $url
     * @return array
     */
    public function extract_content( $html, $url ) {
        $prev = libxml_use_internal_errors( true );
        $doc  = new DOMDocument();
        $doc->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev );

        // Strip noisy tags.
        foreach ( array( 'script', 'style', 'nav', 'footer', 'header', 'aside', 'iframe', 'noscript', 'form' ) as $tag ) {
            $nodes = $doc->getElementsByTagName( $tag );
            while ( $nodes->length > 0 ) {
                $nodes->item( 0 )->parentNode->removeChild( $nodes->item( 0 ) );
            }
        }

        // Get page title.
        $title      = '';
        $title_tags = $doc->getElementsByTagName( 'title' );
        if ( $title_tags->length > 0 ) {
            $title = trim( $title_tags->item( 0 )->textContent );
        }
        if ( empty( $title ) ) {
            $h1s = $doc->getElementsByTagName( 'h1' );
            if ( $h1s->length > 0 ) {
                $title = trim( $h1s->item( 0 )->textContent );
            }
        }

        // Extract main content via common structural selectors.
        $xpath    = new DOMXPath( $doc );
        $content  = '';
        $queries  = array(
            '//main',
            '//article',
            '//*[@role="main"]',
            '//*[contains(concat(" ",normalize-space(@class)," ")," content ")]',
            '//*[@id="content"]',
            '//*[@id="main"]',
            '//body',
        );

        foreach ( $queries as $query ) {
            $nodes = $xpath->query( $query );
            if ( $nodes && $nodes->length > 0 ) {
                $content = trim( $nodes->item( 0 )->textContent );
                if ( strlen( $content ) > 100 ) {
                    break;
                }
            }
        }

        if ( empty( $content ) ) {
            $content = trim( $doc->textContent );
        }

        // Normalise whitespace.
        $content = preg_replace( '/[ \t]+/', ' ', $content );
        $content = preg_replace( '/\n{3,}/', "\n\n", $content );
        $content = trim( $content );

        // Cap at ~1,500 words to stay within token limits.
        $word_count = str_word_count( $content );
        if ( $word_count > 1500 ) {
            $words   = explode( ' ', $content );
            $content = implode( ' ', array_slice( $words, 0, 1500 ) ) . '…';
        }

        // Collect internal links for recursive crawling.
        $base  = $this->get_base_url( $url );
        $links = array();
        foreach ( $doc->getElementsByTagName( 'a' ) as $anchor ) {
            $href = trim( $anchor->getAttribute( 'href' ) );
            if ( empty( $href ) || '#' === $href[0] || 'javascript' === substr( $href, 0, 10 ) ) {
                continue;
            }
            if ( strpos( $href, 'http' ) !== 0 ) {
                if ( '/' === $href[0] ) {
                    $href = rtrim( $base, '/' ) . $href;
                } else {
                    continue;
                }
            }
            // Strip fragments and query strings for deduplication.
            $href = strtok( $href, '#' );
            $href = strtok( $href, '?' );
            $href = rtrim( $href, '/' );
            if ( ! empty( $href ) && strpos( $href, $base ) === 0 ) {
                $links[] = $href;
            }
        }

        return array(
            'url'        => $url,
            'title'      => $title ?: wp_parse_url( $url, PHP_URL_PATH ),
            'content'    => $content,
            'word_count' => str_word_count( $content ),
            'links'      => array_unique( $links ),
        );
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function crawl_recursive( $url, $base_url, &$pages ) {
        if ( count( $pages ) >= $this->max_pages ) {
            return;
        }

        $key = $this->normalize_url( $url );
        if ( isset( $this->visited[ $key ] ) ) {
            return;
        }
        $this->visited[ $key ] = true;

        $page = $this->crawl_page( $url );
        if ( ! $page ) {
            return;
        }

        $pages[] = $page;

        if ( ! empty( $page['links'] ) ) {
            foreach ( $page['links'] as $link ) {
                if ( count( $pages ) >= $this->max_pages ) {
                    break;
                }
                if ( strpos( $link, $base_url ) === 0 ) {
                    $this->crawl_recursive( $link, $base_url, $pages );
                }
            }
        }
    }

    private function save_pages( array $pages, $site_key ) {
        global $wpdb;

        foreach ( $pages as $page ) {
            if ( empty( $page['content'] ) || strlen( $page['content'] ) < 50 ) {
                continue;
            }

            $existing_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}leadnest_knowledge WHERE site_key = %s AND url = %s LIMIT 1",
                    $site_key,
                    $page['url']
                )
            );

            if ( $existing_id ) {
                $wpdb->update(
                    $wpdb->prefix . 'leadnest_knowledge',
                    array(
                        'page_title'   => $page['title'],
                        'content'      => $page['content'],
                        'word_count'   => $page['word_count'],
                        'last_crawled' => current_time( 'mysql' ),
                    ),
                    array( 'id' => $existing_id ),
                    array( '%s', '%s', '%d', '%s' ),
                    array( '%d' )
                );
            } else {
                $wpdb->insert(
                    $wpdb->prefix . 'leadnest_knowledge',
                    array(
                        'site_key'     => $site_key,
                        'url'          => $page['url'],
                        'page_title'   => $page['title'],
                        'content'      => $page['content'],
                        'word_count'   => $page['word_count'],
                        'active'       => 1,
                        'last_crawled' => current_time( 'mysql' ),
                        'created_at'   => current_time( 'mysql' ),
                    ),
                    array( '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
                );
            }
        }
    }

    private function get_base_url( $url ) {
        $parsed = wp_parse_url( $url );
        if ( ! $parsed || empty( $parsed['host'] ) ) {
            return $url;
        }
        $scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] : 'https';
        return $scheme . '://' . $parsed['host'];
    }

    private function normalize_url( $url ) {
        return rtrim( strtolower( $url ), '/' );
    }
}
