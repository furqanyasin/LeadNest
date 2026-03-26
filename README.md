# LeadNest - AI Lead Capture Chatbot

**LeadNest** is an AI-powered chatbot that works on any website -- WordPress or custom-built. It engages visitors in natural conversation, captures leads automatically, and stores everything in your own database.

## Features

### AI Chat
- **Dual AI providers** -- Anthropic Claude (with prompt caching) + OpenAI GPT
- **BYOK model** -- bring your own API key, pay only your actual usage costs
- Configurable system prompt with industry templates
- Rolling conversation summarization for long chats

### Lead Capture
- Automatic name, email, and phone extraction from conversations
- Configurable trigger (after X messages)
- Email notification on every new lead
- Leads dashboard with status tracking (New / Contacted / Qualified / Closed)
- CSV export

### Knowledge Base & Training
- Website crawler (PHP cURL + DOMDocument) with auto-recrawl scheduling
- Manual content entries for AI context
- Q&A trainer -- exact-answer pairs with CSV import/export
- Missed questions log -- auto-detects when bot can't answer

### Appointment Booking
- Chatbot-driven scheduling with weekly availability settings
- Configurable slot duration, buffer time, and daily limits
- Google Calendar sync via OAuth 2.0
- SMS reminders via Twilio
- Booking confirmation emails

### Multi-Channel Messaging
- **WhatsApp Business API** -- full two-way AI conversations
- **Facebook Messenger** -- webhook integration with Page Access Token
- **Telegram Bot** -- BotFather integration with one-click webhook setup
- All channels share the same AI brain, knowledge base, and lead capture

### Live Agent Handoff
- Keyword and uncertainty-based escalation triggers
- Real-time admin takeover with live chat modal
- "Return to AI" button to release sessions back to the bot
- Email notification on escalation

### Widget
- **Shadow DOM isolation** -- zero CSS conflicts with any theme
- Color presets + custom colors
- Configurable bot name, icon, footer text
- Human-like typing animation
- Mobile optimized

### Embed on Any Website
- One-line `<script>` tag for external sites
- Site key system for multi-site deployments
- CORS-ready REST API
- Works with React, Next.js, Laravel, plain HTML, Webflow, etc.

### License & Tier System
- Feature gating by tier: Free / Personal / Pro / Agency
- Remote license server validation with offline fallback
- Admin UI for license management

### WooCommerce Integration
- Optional conversion tracking (order ID + revenue per session)

## Requirements

- WordPress 6.0+
- PHP 8.0+
- MySQL 5.7+
- An API key from Anthropic (Claude) or OpenAI (GPT)

## Installation

### WordPress Plugin

1. Upload the `leadnest/` folder to `/wp-content/plugins/`
2. Activate the plugin in **Plugins > Installed Plugins**
3. Go to **LeadNest > AI Settings** and enter your API key
4. Customize appearance in **LeadNest > Appearance**
5. The chat widget is now live on your site

### Embed on External Websites

1. Go to **LeadNest > Setup Guide** in the WordPress admin
2. Copy the embed script
3. Paste it before `</body>` on any website

```html
<script src="https://yoursite.com/wp-json/leadnest/v1/widget.js?key=YOUR_SITE_KEY"></script>
```

## Architecture

```
Custom Website (React / HTML / Laravel / etc.)
        | 1-line embed script
LeadNest Widget JS (served from WP backend)
        | CORS API calls
WordPress REST Endpoints (/wp-json/leadnest/v1/)
        |
MySQL Database (via $wpdb)
        |
Claude API / OpenAI API (AI responses)
```

## Tech Stack

| Technology | Purpose |
|---|---|
| PHP | Plugin backend, REST endpoints, crawler |
| MySQL | All data via `$wpdb` |
| Vanilla JS + Shadow DOM | Chat widget (no React/Vue) |
| wp_mail() | Email notifications |
| WP Cron | Auto-recrawl, SMS reminders |
| Twilio REST API | SMS booking reminders |
| Google Calendar OAuth 2.0 | Calendar sync |
| WhatsApp Business API | Multi-channel messaging |
| Facebook Messenger API | Multi-channel messaging |
| Telegram Bot API | Multi-channel messaging |

## Admin Pages

| Menu Item | Purpose |
|---|---|
| Dashboard | Sessions, leads count, conversion stats |
| Chat Logs | Full transcripts, search, bulk delete |
| Leads | Lead cards, status tracking, notes, CSV export |
| Knowledge Base | URL crawler + manual content entries |
| Train Bot | Q&A pairs, CSV import/export, Missed Questions |
| Bookings | Appointments, availability, Google Calendar, Twilio |
| Channels | WhatsApp, Messenger, Telegram config |
| Live Agent | Real-time handoff inbox, agent takeover |
| Appearance | Colors, branding, widget customization |
| AI Settings | Provider, API key, model, prompt config |
| Behavior | Session mode, greeting, WooCommerce toggle |
| Setup Guide | Embed script and quick start |

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).
