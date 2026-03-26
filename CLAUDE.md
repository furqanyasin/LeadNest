# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**LeadNest** is an AI-powered lead capture chatbot WordPress plugin (v2.0.0) with a secondary custom website embed capability. All features through v2.0 are fully implemented.

Full product specification: `LeadNest-Product-Plan.md`

## Architecture

### Dual Deployment Model

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

- **WordPress Plugin** -- PHP backend + REST API + WP admin dashboard + Vanilla JS chat widget
- **Embed mode** -- single `<script>` tag that loads the widget from a WordPress backend on any external site (React, Next.js, Laravel, plain HTML)
- **Site key system** -- each external site gets a unique key enabling one LeadNest backend to serve multiple websites with per-site settings

### PHP Conventions

- All functions prefixed with `leadnest_`
- All DB table names prefixed with `leadnest_` (e.g., `leadnest_sessions`, `leadnest_leads`)
- REST namespace: `/wp-json/leadnest/v1/`
- Database access exclusively via `$wpdb` (never raw PDO or MySQLi)
- Emails via `wp_mail()`
- Scheduled tasks via WP Cron

### Widget Isolation

The chat widget uses **Shadow DOM** to prevent CSS conflicts with any host site's theme or framework. This is non-negotiable -- all widget styles must be scoped inside the shadow root.

## Database Schema

```
leadnest_sessions          -- visitor sessions, geo, device, conversion, live_agent flag
leadnest_chats             -- individual messages per session
leadnest_leads             -- captured leads (name, email, phone, status)
leadnest_knowledge         -- crawled website content per page
leadnest_qa                -- admin Q&A training pairs
leadnest_missed_questions  -- questions bot couldn't confidently answer
leadnest_bookings          -- scheduled appointments + google_event_id + reminder_sent
leadnest_availability      -- weekly available time slots per site
leadnest_sites             -- site keys & per-site JSON settings
leadnest_licenses          -- license keys, domains, tiers, status, expiry
```

## Implemented Features (v2.0.0)

All features are complete:

1. **v1.3 -- Lead Capture** -- `leadnest_leads` table, automatic extraction from AI conversations, Leads admin page, email notifications, CSV export
2. **v1.4 -- Website Crawler** -- PHP cURL + DOMDocument, `leadnest_knowledge` table, WP Cron auto-recrawl (daily/weekly/monthly)
3. **v1.4 -- Custom Website Embed** -- site key system, CORS middleware, embed script generator, Setup Guide page
4. **v1.5 -- Q&A Trainer** -- `leadnest_qa` table, CSV import/export, system prompt injection, missed questions auto-detection
5. **v1.7 -- Appointment Booking** -- `leadnest_bookings`, weekly availability, slot generation, Google Calendar OAuth sync, Twilio SMS reminders
6. **v1.8 -- Multi-Channel** -- WhatsApp Business API, Facebook Messenger, Telegram Bot (all fully implemented with webhooks)
7. **v1.9 -- Live Agent Handoff** -- keyword/uncertainty escalation, admin real-time takeover via AJAX polling, "Return to AI" button
8. **v2.0 -- License System** -- tier-based feature gating (Free/Personal/Pro/Agency), remote validation with offline fallback, admin UI

## AI Integration

- **Primary:** Anthropic Claude API with prompt caching (90% cost reduction on repeated system prompts)
- **Secondary:** OpenAI GPT API
- **BYOK model** -- users provide their own API keys; LeadNest never proxies or marks up API costs
- Knowledge base + Q&A pairs are injected into the system prompt and cached as Anthropic cache blocks
- Rolling summarization compresses long conversations to stay within context limits

## Key Admin Pages

| WP Menu Item | Purpose |
|---|---|
| LeadNest -> Dashboard | Sessions, leads count, conversion stats |
| LeadNest -> Chat Logs | Full transcripts, search, bulk delete |
| LeadNest -> Leads | Lead cards, status tracking, notes, CSV export |
| LeadNest -> Knowledge Base | URL crawler + manual content entries |
| LeadNest -> Train Bot | Q&A pairs, CSV import/export, Missed Questions |
| LeadNest -> Bookings | Bookings list, availability settings, Google Calendar, Twilio SMS |
| LeadNest -> Channels | WhatsApp, Messenger, Telegram config |
| LeadNest -> Live Agent | Real-time handoff inbox, agent takeover, return to AI |
| LeadNest -> Appearance | Colors, branding, widget customization |
| LeadNest -> AI Settings | Provider, API key, model, prompt, lead capture config |
| LeadNest -> Behavior | Session mode, greeting, WooCommerce toggle |
| LeadNest -> Setup Guide | Embed script, site key, quick start |

## Key Classes

| File | Class | Purpose |
|---|---|---|
| `includes/class-leadnest-db.php` | `LeadNest_DB` | DB install, options, defaults |
| `includes/class-leadnest-api.php` | `LeadNest_API` | All REST endpoints, AI calls, channel webhooks |
| `includes/class-leadnest-admin.php` | `LeadNest_Admin` | Menus, AJAX handlers, settings |
| `includes/class-leadnest-crawler.php` | `LeadNest_Crawler` | Website crawler (cURL + DOMDocument) |
| `includes/class-leadnest-gcal.php` | `LeadNest_GCal` | Google Calendar OAuth 2.0 integration |
| `includes/class-leadnest-sms.php` | `LeadNest_SMS` | Twilio SMS sending |
| `includes/class-leadnest-license.php` | `LeadNest_License` | License validation, tier gating |

## Tech Stack

- **PHP** -- plugin backend, REST endpoints, crawler (cURL + DOMDocument)
- **MySQL** -- all data via `$wpdb`
- **Vanilla JavaScript + Shadow DOM** -- chat widget (no React/Vue)
- **wp_mail()** -- all email notifications
- **WP Cron** -- auto-recrawl, SMS reminders scheduling
- **Twilio REST API** -- SMS reminders
- **Google Calendar OAuth 2.0** -- calendar integration
- **WhatsApp Business API (Meta)** -- multi-channel messaging
- **Facebook Messenger API (Meta)** -- multi-channel messaging
- **Telegram Bot API** -- multi-channel messaging
