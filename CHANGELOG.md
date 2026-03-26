# Changelog

All notable changes to LeadNest will be documented in this file.

## [2.0.0] - 2026-03-26

### Added
- **License & Tier System** -- activate license keys with feature gating (Free/Personal/Pro/Agency)
- **Facebook Messenger channel** -- webhook integration, two-way AI conversations via Meta Send API
- **Telegram Bot channel** -- BotFather integration with one-click webhook setup via admin UI
- **Google Calendar sync** -- OAuth 2.0 integration, auto-create calendar events from confirmed bookings
- **Twilio SMS reminders** -- configurable hours-before booking reminders via WP Cron
- **Live Agent Handoff** -- keyword/uncertainty-based escalation, real-time admin takeover, return to AI
- **Rolling conversation summarization** -- compress long chat histories to stay within token limits
- **WooCommerce conversion tracking** -- match orders to chat sessions by billing email
- Admin UI for Google Calendar, Twilio, Messenger, Telegram, and license settings
- Admin AJAX handlers for all new settings (GCal, Twilio, Messenger, Telegram, License)
- JS form handlers for all new admin settings pages
- REST endpoints: `/messenger/webhook`, `/telegram/webhook`, `/license/activate`, `/license/deactivate`, `/license/status`
- REST endpoints: `/chat-poll`, `/live-agent/takeover`, `/live-agent/release`, `/live-agent/reply`, `/live-agent/messages`
- `class-leadnest-gcal.php` -- Google Calendar OAuth 2.0 class
- `class-leadnest-sms.php` -- Twilio SMS class
- `class-leadnest-license.php` -- License validation and tier gating class

## [1.8.0] - 2026-03-25

### Added
- **WhatsApp Business API** -- full two-way AI conversations via webhook
- Multi-channel architecture -- shared AI brain across all messaging platforms
- Channels admin page with connection status and setup guides
- REST endpoints: `/whatsapp/webhook` (GET verify + POST receive)

## [1.7.0] - 2026-03-24

### Added
- **Appointment booking system** -- chatbot-driven scheduling
- Weekly availability settings with day/time configuration
- Slot generation with duration, buffer, and daily max limits
- Booking admin page with status management (Pending/Confirmed/Cancelled/Completed)
- Email confirmations to visitors and admin on new bookings
- REST endpoints: `/bookings`, `/bookings/{id}/status`, `/available-slots`
- `leadnest_bookings` and `leadnest_availability` database tables

## [1.5.0] - 2026-03-22

### Added
- **Website crawler** -- PHP cURL + DOMDocument for automated content ingestion
- Auto-recrawl via WP Cron (daily/weekly/monthly schedule)
- **Q&A CSV import/export** -- bulk manage training pairs
- **Missed questions auto-flagging** -- confidence detection with admin review
- Crawl settings UI with schedule selector
- `leadnest_missed_questions` database table

## [1.4.0] - 2026-03-20

### Added
- **Custom website embed** -- site key system for multi-site deployments
- CORS middleware for cross-origin REST API requests
- Embed script generator in Setup Guide admin page
- `leadnest_sites` database table

## [1.3.0] - 2026-03-18

### Added
- **Lead capture system** -- automatic name/email/phone extraction from AI conversations
- Leads admin page with status tracking (New/Contacted/Qualified/Closed), notes, CSV export
- Email notification on every new lead via wp_mail()
- **Knowledge Base** -- manual content entries injected into AI system prompt
- **Q&A Trainer** -- exact-answer Q&A pairs for precise bot responses
- **Missed Questions log** -- auto-detection with admin review and one-click answer
- Industry system prompt templates (home inspection, real estate, healthcare, law, e-commerce)
- Setup Guide page with embed instructions
- Full admin CSS and JS
- Shadow DOM chat widget with color presets
- `leadnest_leads`, `leadnest_knowledge`, `leadnest_qa` database tables
