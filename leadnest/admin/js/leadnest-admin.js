/* global leadnestAdmin, jQuery */
( function ( $ ) {
    'use strict';

    var ajax = leadnestAdmin.ajaxUrl;
    var nonce = leadnestAdmin.nonce;
    var restUrl = leadnestAdmin.restUrl;
    var restNonce = leadnestAdmin.restNonce;

    // ── Helpers ──────────────────────────────────────────────────────────────

    function showNotice( msg, type, $container ) {
        type = type || 'success';
        var $n = $( '<div class="ln-notice ln-notice-' + type + '">' + msg + '</div>' );
        $container.prepend( $n );
        setTimeout( function () { $n.fadeOut( 300, function () { $n.remove(); } ); }, 3500 );
    }

    function setStatus( $el, msg, isError ) {
        $el.text( msg ).removeClass( 'error' );
        if ( isError ) { $el.addClass( 'error' ); }
        setTimeout( function () { $el.text( '' ).removeClass( 'error' ); }, 3000 );
    }

    // ── Copy to clipboard ────────────────────────────────────────────────────

    $( document ).on( 'click', '.ln-copy-btn', function () {
        var targetId = $( this ).data( 'copy' );
        var text = $( '#' + targetId ).text();
        if ( navigator.clipboard ) {
            navigator.clipboard.writeText( text );
        } else {
            var $t = $( '<textarea>' ).val( text ).appendTo( 'body' );
            $t[0].select();
            document.execCommand( 'copy' );
            $t.remove();
        }
        var $btn = $( this );
        var orig = $btn.text();
        $btn.text( 'Copied!' );
        setTimeout( function () { $btn.text( orig ); }, 1500 );
    } );

    // ── Appearance form ──────────────────────────────────────────────────────

    $( '#ln-appearance-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ln-appearance-save-btn' );
        var $status = $( '#ln-appearance-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_appearance&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Appearance' ); } );
    } );

    // Color preset selection
    $( document ).on( 'click', '.ln-color-preset', function () {
        $( '.ln-color-preset' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        var preset = $( this ).data( 'preset' );
        $( '#color_preset' ).val( preset );
        var primary = $( this ).data( 'primary' );
        var text    = $( this ).data( 'text' );
        if ( preset === 'custom' ) {
            $( '.ln-custom-colors' ).slideDown( 200 );
        } else {
            $( '.ln-custom-colors' ).slideUp( 200 );
            updateColorPreview( primary, text );
        }
    } );

    $( '#custom_primary_color, #custom_text_color' ).on( 'change', function () {
        updateColorPreview( $( '#custom_primary_color' ).val(), $( '#custom_text_color' ).val() );
    } );

    function updateColorPreview( primary, text ) {
        $( '#ln-color-preview' ).css( { background: primary, color: text } );
    }

    // ── AI Settings form ──────────────────────────────────────────────────────

    $( '#ln-ai-settings-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ln-ai-save-btn' );
        var $status = $( '#ln-ai-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_ai_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save AI Settings' ); } );
    } );

    // Provider tabs
    $( document ).on( 'click', '.ln-provider-tab', function () {
        var provider = $( this ).data( 'provider' );
        $( '.ln-provider-tab' ).removeClass( 'active' );
        $( this ).addClass( 'active' );
        $( '.ln-provider-section' ).removeClass( 'active' );
        $( '#ln-provider-' + provider ).addClass( 'active' );
        $( '#ai_provider' ).val( provider );
    } );

    // Test connection
    $( '#ln-test-connection-btn' ).on( 'click', function () {
        var $btn = $( this );
        var $result = $( '#ln-test-result' );
        $btn.prop( 'disabled', true ).html( '<span class="ln-spinner"></span> Testing…' );
        $result.removeClass( 'ln-notice-success ln-notice-error' ).hide();

        $.ajax( {
            url: restUrl + 'test-connection',
            method: 'POST',
            headers: { 'X-WP-Nonce': restNonce },
            contentType: 'application/json',
            data: JSON.stringify( { provider: $( '#ai_provider' ).val() } )
        } ).done( function ( r ) {
            if ( r.success ) {
                $result.addClass( 'ln-notice ln-notice-success' ).text( 'Connected: ' + r.message ).show();
            } else {
                $result.addClass( 'ln-notice ln-notice-error' ).text( 'Error: ' + r.message ).show();
            }
        } ).fail( function () {
            $result.addClass( 'ln-notice ln-notice-error' ).text( 'Connection failed.' ).show();
        } ).always( function () {
            $btn.prop( 'disabled', false ).text( 'Test Connection' );
        } );
    } );

    // ── Behavior form ─────────────────────────────────────────────────────────

    $( '#ln-behavior-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ln-behavior-save-btn' );
        var $status = $( '#ln-behavior-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_behavior&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Behavior' ); } );
    } );

    // ── Leads page ────────────────────────────────────────────────────────────

    // Status change
    $( document ).on( 'change', '.ln-status-select', function () {
        var leadId = $( this ).data( 'lead-id' );
        var status = $( this ).val();
        $.post( ajax, { action: 'leadnest_update_lead', nonce: nonce, lead_id: leadId, status: status } )
            .done( function ( r ) {
                if ( ! r.success ) { alert( r.data.message || 'Error updating status.' ); }
            } );
    } );

    // Save notes
    $( document ).on( 'click', '.ln-save-notes-btn', function () {
        var leadId = $( this ).data( 'lead-id' );
        var notes  = $( '.ln-notes-textarea[data-lead-id="' + leadId + '"]' ).val();
        var $btn   = $( this );
        $btn.prop( 'disabled', true ).text( 'Saving…' );
        $.post( ajax, { action: 'leadnest_update_lead', nonce: nonce, lead_id: leadId, notes: notes } )
            .done( function ( r ) {
                if ( r.success ) {
                    $btn.text( 'Saved!' );
                    setTimeout( function () { $btn.prop( 'disabled', false ).text( 'Save Notes' ); }, 1500 );
                } else {
                    $btn.prop( 'disabled', false ).text( 'Save Notes' );
                    alert( r.data.message || 'Error.' );
                }
            } )
            .fail( function () { $btn.prop( 'disabled', false ).text( 'Save Notes' ); } );
    } );

    // Delete lead
    $( document ).on( 'click', '.ln-delete-lead-btn', function () {
        if ( ! confirm( 'Delete this lead? This cannot be undone.' ) ) { return; }
        var leadId = $( this ).data( 'lead-id' );
        $.post( ajax, { action: 'leadnest_delete_lead', nonce: nonce, lead_id: leadId } )
            .done( function ( r ) {
                if ( r.success ) {
                    $( '#ln-lead-' + leadId ).fadeOut( 300, function () { $( this ).remove(); } );
                } else {
                    alert( r.data.message || 'Error.' );
                }
            } );
    } );

    // Export leads
    $( '#ln-export-leads-btn' ).on( 'click', function () {
        var form = $( '<form method="post" action="' + ajax + '">' )
            .append( $( '<input>' ).attr( { type: 'hidden', name: 'action',  value: 'leadnest_export_leads' } ) )
            .append( $( '<input>' ).attr( { type: 'hidden', name: 'nonce',   value: nonce } ) )
            .appendTo( 'body' );
        form.submit();
        form.remove();
    } );

    // ── Chat Logs ─────────────────────────────────────────────────────────────

    // View chat modal
    $( document ).on( 'click', '.ln-view-chat-btn', function () {
        var sessionId = $( this ).data( 'session-id' );
        openChatModal( sessionId );
    } );

    function openChatModal( sessionId ) {
        $( '#ln-chat-modal' ).show();
        $( '#ln-modal-body' ).html( '<div class="ln-chat-loading">Loading…</div>' );

        $.post( ajax, { action: 'leadnest_get_session_chats', nonce: nonce, session_id: sessionId } )
            .done( function ( r ) {
                if ( r.success && r.data.chats ) {
                    renderChatModal( r.data.chats );
                } else {
                    $( '#ln-modal-body' ).html( '<p>No messages found.</p>' );
                }
            } )
            .fail( function () {
                $( '#ln-modal-body' ).html( '<p>Failed to load chat.</p>' );
            } );
    }

    function renderChatModal( chats ) {
        if ( ! chats.length ) {
            $( '#ln-modal-body' ).html( '<p>No messages found.</p>' );
            return;
        }
        var html = '<div class="ln-chat-messages">';
        chats.forEach( function ( msg ) {
            var cls  = 'ln-chat-msg-' + msg.role;
            var time = msg.created_at ? msg.created_at.slice( 0, 16 ) : '';
            html += '<div class="ln-chat-msg ' + cls + '">';
            html += '<div class="ln-chat-bubble">' + escHtml( msg.content ) + '</div>';
            if ( time ) { html += '<span class="ln-chat-msg-time">' + escHtml( time ) + '</span>'; }
            html += '</div>';
        } );
        html += '</div>';
        $( '#ln-modal-body' ).html( html );
    }

    $( document ).on( 'click', '.ln-modal-close, .ln-modal-backdrop', function () {
        $( '.ln-modal' ).hide();
    } );

    // Delete single session
    $( document ).on( 'click', '.ln-delete-session-btn', function () {
        if ( ! confirm( 'Delete this session and its messages?' ) ) { return; }
        var sessionId = $( this ).data( 'session-id' );
        $.post( ajax, { action: 'leadnest_bulk_delete_chats', nonce: nonce, session_ids: [ sessionId ] } )
            .done( function ( r ) {
                if ( r.success ) {
                    $( '#ln-session-row-' + sessionId ).fadeOut( 200, function () { $( this ).remove(); } );
                    $( '#ln-expand-' + sessionId ).remove();
                } else {
                    alert( r.data.message || 'Error.' );
                }
            } );
    } );

    // Select all checkbox
    $( '#ln-check-all' ).on( 'change', function () {
        $( '.ln-session-check' ).prop( 'checked', $( this ).is( ':checked' ) );
        updateBulkBtn();
    } );

    $( document ).on( 'change', '.ln-session-check', function () {
        updateBulkBtn();
    } );

    function updateBulkBtn() {
        var checked = $( '.ln-session-check:checked' ).length;
        $( '#ln-bulk-delete-btn' ).prop( 'disabled', checked === 0 );
    }

    $( '#ln-bulk-delete-btn' ).on( 'click', function () {
        var ids = $( '.ln-session-check:checked' ).map( function () { return $( this ).val(); } ).get();
        if ( ! ids.length ) { return; }
        if ( ! confirm( 'Delete ' + ids.length + ' session(s)?' ) ) { return; }

        $.post( ajax, { action: 'leadnest_bulk_delete_chats', nonce: nonce, session_ids: ids } )
            .done( function ( r ) {
                if ( r.success ) {
                    ids.forEach( function ( id ) {
                        $( '#ln-session-row-' + id ).remove();
                        $( '#ln-expand-' + id ).remove();
                    } );
                    $( '#ln-bulk-delete-btn' ).prop( 'disabled', true );
                } else {
                    alert( r.data.message || 'Error.' );
                }
            } );
    } );

    // ── Train Bot — Q&A ───────────────────────────────────────────────────────

    $( '#ln-qa-add-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $form = $( this );
        var $btn  = $( '#ln-qa-add-btn' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $form.serialize() + '&action=leadnest_save_qa&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    location.reload();
                } else {
                    alert( r.data.message || 'Error saving Q&A.' );
                    $btn.prop( 'disabled', false ).text( 'Add Q&A' );
                }
            } )
            .fail( function () { $btn.prop( 'disabled', false ).text( 'Add Q&A' ); } );
    } );

    $( document ).on( 'click', '.ln-delete-qa-btn', function () {
        if ( ! confirm( 'Delete this Q&A pair?' ) ) { return; }
        var id   = $( this ).data( 'qa-id' );
        var $row = $( this ).closest( 'tr' );
        $.post( ajax, { action: 'leadnest_delete_qa', nonce: nonce, qa_id: id } )
            .done( function ( r ) {
                if ( r.success ) {
                    $row.fadeOut( 200, function () { $row.remove(); } );
                } else {
                    alert( r.data.message || 'Error.' );
                }
            } );
    } );

    // Resolve missed question
    $( document ).on( 'click', '.ln-resolve-missed-btn', function () {
        var id   = $( this ).data( 'missed-id' );
        var $row = $( this ).closest( 'tr' );
        $.post( ajax, { action: 'leadnest_resolve_missed', nonce: nonce, missed_id: id } )
            .done( function ( r ) {
                if ( r.success ) {
                    $row.addClass( 'ln-resolved' );
                    $( '.ln-missed-count' ).text( function ( i, v ) { return Math.max( 0, parseInt( v ) - 1 ); } );
                }
            } );
    } );

    // ── Knowledge Base ────────────────────────────────────────────────────────

    $( '#ln-kb-add-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn = $( '#ln-kb-add-btn' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_knowledge&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    location.reload();
                } else {
                    alert( r.data.message || 'Error.' );
                    $btn.prop( 'disabled', false ).text( 'Add Entry' );
                }
            } )
            .fail( function () { $btn.prop( 'disabled', false ).text( 'Add Entry' ); } );
    } );

    $( document ).on( 'click', '.ln-delete-kb-btn', function () {
        if ( ! confirm( 'Delete this knowledge base entry?' ) ) { return; }
        var id   = $( this ).data( 'kb-id' );
        var $row = $( this ).closest( 'tr' );
        $.post( ajax, { action: 'leadnest_delete_knowledge', nonce: nonce, kb_id: id } )
            .done( function ( r ) {
                if ( r.success ) {
                    $row.fadeOut( 200, function () { $row.remove(); } );
                }
            } );
    } );

    // ── Crawler ────────────────────────────────────────────────────────────────

    $( '#ln-crawl-btn' ).on( 'click', function () {
        var $btn    = $( this );
        var $status = $( '#ln-crawl-status' );
        var url     = $( '#ln-crawl-url' ).val();
        var maxPages = $( '#ln-crawl-max-pages' ).val();

        if ( ! url ) { alert( 'Enter a URL to crawl.' ); return; }

        $btn.prop( 'disabled', true ).html( '<span class="ln-spinner"></span> Crawling…' );
        $status.show().html( '<span class="ln-spinner"></span> Crawling your website… This may take a minute.' );

        $.post( ajax, { action: 'leadnest_crawl_url', nonce: nonce, crawl_url: url, max_pages: maxPages } )
            .done( function ( r ) {
                if ( r.success ) {
                    $status.html( '<span class="dashicons dashicons-yes" style="color:#16a34a;"></span> ' + r.data.message );
                    setTimeout( function () { location.reload(); }, 1500 );
                } else {
                    $status.html( '<span class="dashicons dashicons-warning" style="color:#dc2626;"></span> ' + ( r.data.message || 'Crawl failed.' ) );
                }
            } )
            .fail( function () {
                $status.html( '<span class="dashicons dashicons-warning" style="color:#dc2626;"></span> Request failed. The crawl may have timed out.' );
            } )
            .always( function () {
                $btn.prop( 'disabled', false ).html( '<span class="dashicons dashicons-search" style="vertical-align:middle;margin-top:-2px;"></span> Crawl Now' );
            } );
    } );

    $( '#ln-save-crawl-settings-btn' ).on( 'click', function () {
        var $btn = $( this );
        var schedule = $( '#ln-crawl-schedule' ).val();
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, { action: 'leadnest_save_crawl_settings', nonce: nonce, crawl_schedule: schedule } )
            .done( function ( r ) {
                $btn.text( r.success ? 'Saved!' : 'Error' );
                setTimeout( function () { $btn.prop( 'disabled', false ).text( 'Save' ); }, 1500 );
            } )
            .fail( function () { $btn.prop( 'disabled', false ).text( 'Save' ); } );
    } );

    // ── Bookings ──────────────────────────────────────────────────────────────

    // Booking status change
    $( document ).on( 'change', '.ln-booking-status-select', function () {
        var bookingId = $( this ).data( 'booking-id' );
        var status    = $( this ).val();
        $.post( ajax, { action: 'leadnest_update_booking', nonce: nonce, booking_id: bookingId, status: status } )
            .done( function ( r ) {
                if ( ! r.success ) { alert( r.data.message || 'Error updating booking.' ); }
            } );
    } );

    // Delete booking
    $( document ).on( 'click', '.ln-delete-booking-btn', function () {
        if ( ! confirm( 'Delete this booking?' ) ) { return; }
        var bookingId = $( this ).data( 'booking-id' );
        $.post( ajax, { action: 'leadnest_delete_booking', nonce: nonce, booking_id: bookingId } )
            .done( function ( r ) {
                if ( r.success ) {
                    $( '#ln-booking-row-' + bookingId ).fadeOut( 200, function () { $( this ).remove(); } );
                } else {
                    alert( r.data.message || 'Error.' );
                }
            } );
    } );

    // Save availability
    $( '#ln-availability-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-availability-save-btn' );
        var $status = $( '#ln-availability-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_availability&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Availability' ); } );
    } );

    // ── Channels ──────────────────────────────────────────────────────────────

    $( '#ln-whatsapp-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-whatsapp-save-btn' );
        var $status = $( '#ln-whatsapp-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_channel_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save WhatsApp Settings' ); } );
    } );

    // ── Live Agent ────────────────────────────────────────────────────────────

    $( '#ln-live-agent-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-live-agent-save-btn' );
        var $status = $( '#ln-live-agent-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_live_agent&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Settings' ); } );
    } );

    // ── Q&A CSV Import/Export ─────────────────────────────────────────────────

    $( '#ln-qa-import-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn  = $( '#ln-qa-import-btn' );
        var file  = $( '#ln-qa-csv-file' )[0].files[0];

        if ( ! file ) { alert( 'Select a CSV file first.' ); return; }

        $btn.prop( 'disabled', true ).text( 'Importing…' );

        var formData = new FormData();
        formData.append( 'action', 'leadnest_import_qa_csv' );
        formData.append( 'nonce', nonce );
        formData.append( 'qa_csv_file', file );

        $.ajax( {
            url: ajax,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        } ).done( function ( r ) {
            if ( r.success ) {
                alert( r.data.message );
                location.reload();
            } else {
                alert( r.data.message || 'Import failed.' );
                $btn.prop( 'disabled', false ).text( 'Import CSV' );
            }
        } ).fail( function () {
            alert( 'Upload failed.' );
            $btn.prop( 'disabled', false ).text( 'Import CSV' );
        } );
    } );

    $( '#ln-qa-export-btn' ).on( 'click', function () {
        var form = $( '<form method="post" action="' + ajax + '">' )
            .append( $( '<input>' ).attr( { type: 'hidden', name: 'action', value: 'leadnest_export_qa_csv' } ) )
            .append( $( '<input>' ).attr( { type: 'hidden', name: 'nonce',  value: nonce } ) )
            .appendTo( 'body' );
        form.submit();
        form.remove();
    } );

    // ── Google Calendar form ─────────────────────────────────────────────────

    $( '#ln-gcal-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-gcal-save-btn' );
        var $status = $( '#ln-gcal-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_gcal_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Google Calendar Settings' ); } );
    } );

    // ── Twilio form ──────────────────────────────────────────────────────────

    $( '#ln-twilio-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-twilio-save-btn' );
        var $status = $( '#ln-twilio-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_twilio_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Twilio Settings' ); } );
    } );

    // ── Messenger form ───────────────────────────────────────────────────────

    $( '#ln-messenger-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-messenger-save-btn' );
        var $status = $( '#ln-messenger-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_messenger_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Messenger Settings' ); } );
    } );

    // ── Telegram form ────────────────────────────────────────────────────────

    $( '#ln-telegram-form' ).on( 'submit', function ( e ) {
        e.preventDefault();
        var $btn    = $( '#ln-telegram-save-btn' );
        var $status = $( '#ln-telegram-save-status' );
        $btn.prop( 'disabled', true ).text( 'Saving…' );

        $.post( ajax, $( this ).serialize() + '&action=leadnest_save_telegram_settings&nonce=' + nonce )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, 'Saved!' );
                } else {
                    setStatus( $status, r.data.message || 'Error', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Save Telegram Settings' ); } );
    } );

    $( '#ln-telegram-set-webhook-btn' ).on( 'click', function () {
        var $btn    = $( this );
        var $status = $( '#ln-telegram-webhook-status' );
        $btn.prop( 'disabled', true ).text( 'Setting webhook…' );

        $.post( ajax, { action: 'leadnest_set_telegram_webhook', nonce: nonce } )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, r.data.message || 'Webhook set!' );
                } else {
                    setStatus( $status, r.data.message || 'Failed', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Set Webhook with Telegram' ); } );
    } );

    // ── License ──────────────────────────────────────────────────────────────

    $( '#ln-license-activate-btn' ).on( 'click', function () {
        var $btn    = $( this );
        var $status = $( '#ln-license-status' );
        var key     = $( '#ln-license-key' ).val();

        if ( ! key ) { alert( 'Enter a license key.' ); return; }

        $btn.prop( 'disabled', true ).text( 'Activating…' );

        $.post( ajax, { action: 'leadnest_activate_license', nonce: nonce, license_key: key } )
            .done( function ( r ) {
                if ( r.success ) {
                    setStatus( $status, r.data.message || 'Activated!' );
                    location.reload();
                } else {
                    setStatus( $status, r.data.message || 'Activation failed.', true );
                }
            } )
            .fail( function () { setStatus( $status, 'Request failed.', true ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Activate License' ); } );
    } );

    $( '#ln-license-deactivate-btn' ).on( 'click', function () {
        if ( ! confirm( 'Deactivate your license? You will lose access to premium features.' ) ) { return; }
        var $btn = $( this );
        $btn.prop( 'disabled', true ).text( 'Deactivating…' );

        $.post( ajax, { action: 'leadnest_deactivate_license', nonce: nonce } )
            .done( function ( r ) {
                if ( r.success ) { location.reload(); }
                else { alert( r.data.message || 'Error.' ); }
            } )
            .fail( function () { alert( 'Request failed.' ); } )
            .always( function () { $btn.prop( 'disabled', false ).text( 'Deactivate' ); } );
    } );

    // ── Escape HTML helper ────────────────────────────────────────────────────

    function escHtml( str ) {
        return String( str )
            .replace( /&/g,  '&amp;' )
            .replace( /</g,  '&lt;' )
            .replace( />/g,  '&gt;' )
            .replace( /"/g,  '&quot;' )
            .replace( /'/g,  '&#039;' );
    }

} )( jQuery );
