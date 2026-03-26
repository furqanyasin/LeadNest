( function () {
    'use strict';

    // ── Determine base URL and site key from this script's src ───────────────
    var scriptEl   = document.currentScript || ( function () {
        var scripts = document.getElementsByTagName( 'script' );
        return scripts[ scripts.length - 1 ];
    } )();

    var scriptSrc  = scriptEl ? scriptEl.src : '';
    var apiBase    = '';
    var siteKey    = '';

    if ( scriptSrc ) {
        var url = new URL( scriptSrc );
        siteKey = url.searchParams.get( 'key' ) || '';
        // Base is everything before /wp-json/...
        apiBase = scriptSrc.replace( /\/wp-json\/leadnest\/v1\/widget\.js.*$/, '' );
    }

    var restBase = apiBase + '/wp-json/leadnest/v1';

    // ── Styles ────────────────────────────────────────────────────────────────
    var CSS = '\n\
:host { all: initial; display: block; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }\n\
* { box-sizing: border-box; margin: 0; padding: 0; }\n\
\n\
#ln-btn {\n\
    position: fixed;\n\
    bottom: 24px;\n\
    right: 24px;\n\
    width: 56px;\n\
    height: 56px;\n\
    border-radius: 50%;\n\
    background: var(--ln-primary, #2563eb);\n\
    color: var(--ln-text, #fff);\n\
    border: none;\n\
    cursor: pointer;\n\
    display: flex;\n\
    align-items: center;\n\
    justify-content: center;\n\
    box-shadow: 0 4px 16px rgba(0,0,0,.25);\n\
    z-index: 2147483647;\n\
    transition: transform .2s, box-shadow .2s;\n\
    outline: none;\n\
}\n\
#ln-btn:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(0,0,0,.3); }\n\
#ln-btn svg { width: 24px; height: 24px; fill: currentColor; }\n\
#ln-btn .ln-close-icon { display: none; }\n\
#ln-btn.open .ln-chat-icon { display: none; }\n\
#ln-btn.open .ln-close-icon { display: block; }\n\
\n\
#ln-window {\n\
    position: fixed;\n\
    bottom: 92px;\n\
    right: 24px;\n\
    width: 360px;\n\
    max-width: calc(100vw - 32px);\n\
    height: 520px;\n\
    max-height: calc(100vh - 110px);\n\
    display: flex;\n\
    flex-direction: column;\n\
    border-radius: 16px;\n\
    overflow: hidden;\n\
    background: #fff;\n\
    box-shadow: 0 12px 40px rgba(0,0,0,.18);\n\
    z-index: 2147483646;\n\
    transform: scale(.92) translateY(12px);\n\
    opacity: 0;\n\
    pointer-events: none;\n\
    transition: transform .22s cubic-bezier(.4,0,.2,1), opacity .22s;\n\
}\n\
#ln-window.open {\n\
    transform: scale(1) translateY(0);\n\
    opacity: 1;\n\
    pointer-events: auto;\n\
}\n\
\n\
#ln-header {\n\
    background: var(--ln-primary, #2563eb);\n\
    color: var(--ln-text, #fff);\n\
    padding: 14px 16px;\n\
    display: flex;\n\
    align-items: center;\n\
    gap: 10px;\n\
    flex-shrink: 0;\n\
}\n\
#ln-header-icon {\n\
    width: 36px; height: 36px;\n\
    border-radius: 50%;\n\
    background: rgba(255,255,255,.25);\n\
    display: flex; align-items: center; justify-content: center;\n\
    flex-shrink: 0;\n\
    overflow: hidden;\n\
}\n\
#ln-header-icon img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }\n\
#ln-header-icon svg { width: 20px; height: 20px; fill: currentColor; }\n\
#ln-header-info { flex: 1; }\n\
#ln-bot-name { font-size: 15px; font-weight: 600; }\n\
#ln-status { font-size: 12px; opacity: .8; }\n\
\n\
#ln-messages {\n\
    flex: 1;\n\
    overflow-y: auto;\n\
    padding: 16px;\n\
    display: flex;\n\
    flex-direction: column;\n\
    gap: 10px;\n\
    background: #f8fafc;\n\
}\n\
#ln-messages::-webkit-scrollbar { width: 4px; }\n\
#ln-messages::-webkit-scrollbar-track { background: transparent; }\n\
#ln-messages::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }\n\
\n\
.ln-msg-row { display: flex; gap: 8px; align-items: flex-end; }\n\
.ln-msg-row.user { flex-direction: row-reverse; }\n\
\n\
.ln-avatar {\n\
    width: 28px; height: 28px;\n\
    border-radius: 50%;\n\
    background: var(--ln-primary, #2563eb);\n\
    display: flex; align-items: center; justify-content: center;\n\
    flex-shrink: 0;\n\
    overflow: hidden;\n\
}\n\
.ln-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }\n\
.ln-avatar svg { width: 14px; height: 14px; fill: #fff; }\n\
\n\
.ln-bubble {\n\
    max-width: 78%;\n\
    padding: 9px 13px;\n\
    border-radius: 16px;\n\
    font-size: 14px;\n\
    line-height: 1.5;\n\
    word-break: break-word;\n\
    white-space: pre-wrap;\n\
}\n\
.ln-msg-row.bot .ln-bubble {\n\
    background: #fff;\n\
    color: #1e293b;\n\
    border-bottom-left-radius: 4px;\n\
    box-shadow: 0 1px 3px rgba(0,0,0,.08);\n\
}\n\
.ln-msg-row.user .ln-bubble {\n\
    background: var(--ln-primary, #2563eb);\n\
    color: var(--ln-text, #fff);\n\
    border-bottom-right-radius: 4px;\n\
}\n\
\n\
/* Typing indicator */\n\
.ln-typing .ln-bubble {\n\
    display: flex; align-items: center; gap: 4px;\n\
    padding: 11px 16px;\n\
}\n\
.ln-dot {\n\
    width: 7px; height: 7px;\n\
    border-radius: 50%;\n\
    background: #94a3b8;\n\
    animation: ln-bounce .9s infinite;\n\
}\n\
.ln-dot:nth-child(2) { animation-delay: .15s; }\n\
.ln-dot:nth-child(3) { animation-delay: .3s; }\n\
@keyframes ln-bounce {\n\
    0%, 60%, 100% { transform: translateY(0); }\n\
    30% { transform: translateY(-5px); }\n\
}\n\
\n\
#ln-input-area {\n\
    padding: 12px;\n\
    background: #fff;\n\
    border-top: 1px solid #e2e8f0;\n\
    display: flex;\n\
    gap: 8px;\n\
    align-items: flex-end;\n\
    flex-shrink: 0;\n\
}\n\
#ln-input {\n\
    flex: 1;\n\
    border: 1px solid #e2e8f0;\n\
    border-radius: 20px;\n\
    padding: 9px 14px;\n\
    font-size: 14px;\n\
    outline: none;\n\
    resize: none;\n\
    max-height: 120px;\n\
    line-height: 1.4;\n\
    font-family: inherit;\n\
    background: #f8fafc;\n\
    color: #1e293b;\n\
    transition: border-color .15s;\n\
}\n\
#ln-input:focus { border-color: var(--ln-primary, #2563eb); background: #fff; }\n\
#ln-input::placeholder { color: #94a3b8; }\n\
\n\
#ln-send {\n\
    width: 38px; height: 38px;\n\
    border-radius: 50%;\n\
    background: var(--ln-primary, #2563eb);\n\
    color: var(--ln-text, #fff);\n\
    border: none;\n\
    cursor: pointer;\n\
    display: flex; align-items: center; justify-content: center;\n\
    flex-shrink: 0;\n\
    transition: opacity .15s;\n\
}\n\
#ln-send:disabled { opacity: .45; cursor: default; }\n\
#ln-send svg { width: 18px; height: 18px; fill: currentColor; }\n\
\n\
#ln-footer {\n\
    text-align: center;\n\
    padding: 6px;\n\
    font-size: 11px;\n\
    color: #94a3b8;\n\
    background: #fff;\n\
    border-top: 1px solid #f1f5f9;\n\
    flex-shrink: 0;\n\
}\n\
\n\
@media (max-width: 420px) {\n\
    #ln-window { right: 0; bottom: 0; width: 100%; max-width: 100%; border-radius: 16px 16px 0 0; height: 100%; max-height: calc(100% - 70px); }\n\
    #ln-btn { bottom: 16px; right: 16px; }\n\
}\n\
';

    // ── SVG icons ─────────────────────────────────────────────────────────────
    var ICON_CHAT  = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/></svg>';
    var ICON_CLOSE = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';
    var ICON_SEND  = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>';
    var ICON_BOT   = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>';

    // ── State ─────────────────────────────────────────────────────────────────
    var config        = {};
    var sessionToken  = null;
    var isOpen        = false;
    var isWaiting     = false;
    var isLiveAgent   = false;
    var livePollTimer = null;
    var lastMsgCount  = 0;

    // ── Session persistence ───────────────────────────────────────────────────
    function getStorageKey() { return 'leadnest_session_' + siteKey; }

    function loadSession() {
        try {
            var key  = getStorageKey();
            var val  = sessionStorage.getItem( key ) || localStorage.getItem( key );
            if ( val ) { sessionToken = val; }
        } catch ( e ) {}
    }

    function saveSession( token ) {
        try {
            sessionToken = token;
            sessionStorage.setItem( getStorageKey(), token );
            localStorage.setItem( getStorageKey(), token );
        } catch ( e ) {}
    }

    // ── DOM refs ──────────────────────────────────────────────────────────────
    var shadow, elBtn, elWindow, elMessages, elInput, elSend, elFooter, elStatus;

    // ── Bootstrap ─────────────────────────────────────────────────────────────
    function init() {
        loadSession();
        fetchConfig( function () {
            buildDOM();
            applyColors();
            showGreeting();
        } );
    }

    function fetchConfig( cb ) {
        var url = restBase + '/widget-config?key=' + encodeURIComponent( siteKey );
        var xhr = new XMLHttpRequest();
        xhr.open( 'GET', url, true );
        xhr.onload = function () {
            if ( xhr.status === 200 ) {
                try { config = JSON.parse( xhr.responseText ); } catch ( e ) {}
            }
            cb();
        };
        xhr.onerror = cb;
        xhr.send();
    }

    // ── Build DOM inside Shadow Root ──────────────────────────────────────────
    function buildDOM() {
        var host = document.createElement( 'div' );
        host.id  = 'leadnest-host';
        document.body.appendChild( host );

        shadow = host.attachShadow( { mode: 'open' } );

        // Inject styles
        var styleEl = document.createElement( 'style' );
        styleEl.textContent = CSS;
        shadow.appendChild( styleEl );

        // Build button
        elBtn = document.createElement( 'button' );
        elBtn.id = 'ln-btn';
        elBtn.setAttribute( 'aria-label', 'Open chat' );
        elBtn.innerHTML = '<span class="ln-chat-icon">' + ICON_CHAT + '</span>'
                        + '<span class="ln-close-icon">' + ICON_CLOSE + '</span>';
        elBtn.addEventListener( 'click', toggleChat );
        shadow.appendChild( elBtn );

        // Build window
        elWindow = document.createElement( 'div' );
        elWindow.id = 'ln-window';
        elWindow.setAttribute( 'role', 'dialog' );
        elWindow.setAttribute( 'aria-label', 'Chat' );

        // Header
        var headerIconContent = config.header_icon_url
            ? '<img src="' + escAttr( config.header_icon_url ) + '" alt="">'
            : ICON_BOT;

        var header = document.createElement( 'div' );
        header.id  = 'ln-header';
        header.innerHTML = '<div id="ln-header-icon">' + headerIconContent + '</div>'
            + '<div id="ln-header-info">'
            + '<div id="ln-bot-name">' + escHtml( config.bot_name || 'LeadNest' ) + '</div>'
            + '<div id="ln-status">Online</div>'
            + '</div>';
        elStatus = header.querySelector( '#ln-status' );
        elWindow.appendChild( header );

        // Messages
        elMessages = document.createElement( 'div' );
        elMessages.id = 'ln-messages';
        elWindow.appendChild( elMessages );

        // Input area
        var inputArea = document.createElement( 'div' );
        inputArea.id  = 'ln-input-area';

        elInput = document.createElement( 'textarea' );
        elInput.id   = 'ln-input';
        elInput.rows = 1;
        elInput.placeholder = config.placeholder || 'Type your message…';
        elInput.setAttribute( 'aria-label', 'Message' );
        elInput.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Enter' && ! e.shiftKey ) {
                e.preventDefault();
                sendMessage();
            }
        } );
        elInput.addEventListener( 'input', autoResize );

        elSend = document.createElement( 'button' );
        elSend.id = 'ln-send';
        elSend.setAttribute( 'aria-label', 'Send' );
        elSend.innerHTML = ICON_SEND;
        elSend.addEventListener( 'click', sendMessage );

        inputArea.appendChild( elInput );
        inputArea.appendChild( elSend );
        elWindow.appendChild( inputArea );

        // Footer
        if ( config.show_footer && config.footer_text ) {
            elFooter = document.createElement( 'div' );
            elFooter.id = 'ln-footer';
            elFooter.textContent = config.footer_text;
            elWindow.appendChild( elFooter );
        }

        shadow.appendChild( elWindow );
    }

    function applyColors() {
        var primary = config.primary_color || '#2563eb';
        var text    = config.text_color    || '#ffffff';
        var root    = shadow.host || shadow;
        // Apply via host style
        var styleEl = shadow.querySelector( 'style' );
        if ( styleEl ) {
            styleEl.textContent = ':host { --ln-primary: ' + primary + '; --ln-text: ' + text + '; }\n' + CSS;
        }
    }

    function showGreeting() {
        var greeting = config.greeting || 'Hello! How can I help you today?';
        appendBotMessage( greeting );
    }

    // ── Toggle open/close ─────────────────────────────────────────────────────
    function toggleChat() {
        isOpen = ! isOpen;
        if ( isOpen ) {
            elWindow.classList.add( 'open' );
            elBtn.classList.add( 'open' );
            elBtn.setAttribute( 'aria-label', 'Close chat' );
            setTimeout( function () { elInput.focus(); }, 250 );
        } else {
            elWindow.classList.remove( 'open' );
            elBtn.classList.remove( 'open' );
            elBtn.setAttribute( 'aria-label', 'Open chat' );
        }
    }

    // ── Send message ──────────────────────────────────────────────────────────
    function sendMessage() {
        var text = elInput.value.trim();
        if ( ! text || isWaiting ) { return; }

        elInput.value = '';
        autoResize();
        appendUserMessage( text );
        lastMsgCount++; // Track that we added a user message
        setWaiting( true );

        var body = JSON.stringify( {
            message:       text,
            session_token: sessionToken || '',
            site_key:      siteKey,
            page_url:      window.location.href,
        } );

        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', restBase + '/chat', true );
        xhr.setRequestHeader( 'Content-Type', 'application/json' );

        xhr.onload = function () {
            setWaiting( false );
            try {
                var data = JSON.parse( xhr.responseText );
                if ( data.session_token ) { saveSession( data.session_token ); }
                if ( data.live_agent ) {
                    isLiveAgent = true;
                    if ( elStatus ) { elStatus.textContent = 'Connected to agent'; }
                    if ( data.reply ) {
                        appendBotMessage( data.reply );
                        lastMsgCount++; // Track the reply
                    }
                    startLivePolling();
                } else if ( data.reply ) {
                    appendBotMessage( data.reply );
                    lastMsgCount++; // Track the reply
                }
            } catch ( e ) {
                appendBotMessage( 'Sorry, something went wrong. Please try again.' );
            }
        };

        xhr.onerror = function () {
            setWaiting( false );
            appendBotMessage( 'Connection error. Please check your internet and try again.' );
        };

        xhr.send( body );
    }

    // ── Message rendering ─────────────────────────────────────────────────────
    function appendBotMessage( text ) {
        var row    = document.createElement( 'div' );
        row.className = 'ln-msg-row bot';

        var av = document.createElement( 'div' );
        av.className = 'ln-avatar';
        av.innerHTML = config.header_icon_url
            ? '<img src="' + escAttr( config.header_icon_url ) + '" alt="">'
            : ICON_BOT;

        var bubble = document.createElement( 'div' );
        bubble.className = 'ln-bubble';
        bubble.textContent = text;

        row.appendChild( av );
        row.appendChild( bubble );
        elMessages.appendChild( row );
        scrollBottom();
    }

    function appendUserMessage( text ) {
        var row    = document.createElement( 'div' );
        row.className = 'ln-msg-row user';

        var bubble = document.createElement( 'div' );
        bubble.className = 'ln-bubble';
        bubble.textContent = text;

        row.appendChild( bubble );
        elMessages.appendChild( row );
        scrollBottom();
    }

    function showTypingIndicator() {
        var row    = document.createElement( 'div' );
        row.className = 'ln-msg-row bot ln-typing';
        row.id = 'ln-typing-indicator';

        var av = document.createElement( 'div' );
        av.className = 'ln-avatar';
        av.innerHTML = ICON_BOT;

        var bubble = document.createElement( 'div' );
        bubble.className = 'ln-bubble';
        bubble.innerHTML = '<div class="ln-dot"></div><div class="ln-dot"></div><div class="ln-dot"></div>';

        row.appendChild( av );
        row.appendChild( bubble );
        elMessages.appendChild( row );
        scrollBottom();
    }

    function removeTypingIndicator() {
        var el = shadow.getElementById( 'ln-typing-indicator' );
        if ( el ) { el.parentNode.removeChild( el ); }
    }

    function setWaiting( waiting ) {
        isWaiting = waiting;
        elSend.disabled = waiting;
        elInput.disabled = waiting;
        if ( waiting ) {
            elStatus.textContent = 'Typing…';
            showTypingIndicator();
        } else {
            elStatus.textContent = 'Online';
            removeTypingIndicator();
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    function scrollBottom() {
        elMessages.scrollTop = elMessages.scrollHeight;
    }

    function autoResize() {
        elInput.style.height = 'auto';
        elInput.style.height = Math.min( elInput.scrollHeight, 120 ) + 'px';
    }

    function escHtml( s ) {
        return String( s )
            .replace( /&/g, '&amp;' ).replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
    }

    function escAttr( s ) { return escHtml( s ); }

    // ── Live agent polling ──────────────────────────────────────────────────
    function startLivePolling() {
        stopLivePolling();
        livePollTimer = setInterval( pollLiveMessages, 3000 );
    }

    function stopLivePolling() {
        if ( livePollTimer ) {
            clearInterval( livePollTimer );
            livePollTimer = null;
        }
    }

    function pollLiveMessages() {
        if ( ! sessionToken || ! isLiveAgent ) { return; }

        var url = restBase + '/chat-poll?session_token=' + encodeURIComponent( sessionToken )
                + '&site_key=' + encodeURIComponent( siteKey )
                + '&last_count=' + lastMsgCount;

        var xhr = new XMLHttpRequest();
        xhr.open( 'GET', url, true );
        xhr.onload = function () {
            if ( xhr.status === 200 ) {
                try {
                    var data = JSON.parse( xhr.responseText );
                    if ( data.new_messages && data.new_messages.length > 0 ) {
                        data.new_messages.forEach( function ( msg ) {
                            if ( msg.role === 'assistant' ) {
                                appendBotMessage( msg.content );
                            }
                        } );
                        lastMsgCount = data.total_count || ( lastMsgCount + data.new_messages.length );
                    }
                    if ( ! data.live_agent ) {
                        isLiveAgent = false;
                        if ( elStatus ) { elStatus.textContent = 'Online'; }
                        stopLivePolling();
                    }
                } catch ( e ) {}
            }
        };
        xhr.send();
    }

    // ── Boot when DOM ready ───────────────────────────────────────────────────
    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
