=== LeadNest — AI Lead Capture Chatbot ===
Contributors: furqan
Tags: chatbot, lead capture, ai, artificial intelligence, contact form
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 2.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

AI-powered lead capture chatbot for WordPress. Engage visitors, capture leads, and grow your business automatically.

== Description ==

**LeadNest** is an AI-powered chatbot that lives on your WordPress site (or any website) and automatically:

* Chats with visitors using Claude or GPT
* Captures leads — name, email, phone — naturally in conversation
* Stores everything in your own database (no third-party SaaS)
* Works on WordPress AND custom websites (React, Next.js, Laravel, plain HTML)

**Why LeadNest?**

* **BYOK (Bring Your Own API Key)** — you pay only your actual API costs (~$0.12–$1.70 per 500 chats), not a SaaS subscription
* **Your data stays on your server** — no data sent to external SaaS platforms
* **Shadow DOM widget** — zero CSS conflicts with any theme or framework
* **Prompt caching** — 90% cost reduction on repeated Anthropic conversations
* **Dual deployment** — one installation serves your WordPress site AND external websites via embed script

**Features**

= AI & Chat =
* Multi-provider: Anthropic Claude + OpenAI GPT
* Configurable system prompt with industry templates (home inspection, real estate, healthcare, law, e-commerce)
* Rolling conversation history with configurable depth
* Prompt caching for Anthropic (cache_control ephemeral)

= Lead Capture =
* Automatic contact extraction from conversations (name, email, phone)
* Configurable trigger (after X messages)
* Email notification on every new lead
* Leads dashboard with status tracking (New / Contacted / Qualified / Closed)
* CSV export

= Knowledge Base =
* Manually add website content (paste text from any page)
* Content injected into AI system prompt as context
* Per-entry toggle (active/inactive)

= Train Bot =
* Add Q&A pairs — bot answers these exactly as written
* Missed Questions log — auto-detects when bot can't answer
* One-click "Answer This" to add Q&A from missed questions

= Chat Logs =
* Full transcript per visitor session
* Geo data: country, city, device, browser
* Search by IP, country, session token
* Bulk delete + pagination

= Widget =
* Shadow DOM isolation — no CSS conflicts
* Color presets + custom color support
* Configurable bot name, icon, footer text
* Human-like typing animation
* Mobile optimized

= Embed on Any Website =
* One-line embed script for external sites
* Site key system for multi-site deployments
* CORS-ready REST API

= Website Crawler =
* PHP cURL + DOMDocument site crawler
* Auto-recrawl via WP Cron (daily/weekly/monthly)
* Max pages limit and schedule control

= Appointment Booking =
* Chatbot-driven appointment scheduling
* Weekly availability configuration
* Slot duration, buffer time, max bookings per day
* Booking confirmation emails to visitor and admin
* Status management (Pending / Confirmed / Cancelled / Completed)
* Google Calendar sync (OAuth 2.0) — auto-create calendar events
* SMS reminders via Twilio — configurable hours-before notification

= Multi-Channel Messaging =
* WhatsApp Business API — full two-way AI conversations
* Facebook Messenger — webhook integration with Page Access Token
* Telegram Bot — BotFather integration with one-click webhook setup
* All channels share the same AI brain, knowledge base, and lead capture

= Live Agent Handoff =
* Keyword and uncertainty-based escalation triggers
* Admin real-time takeover with live chat modal
* "Return to AI" button to release sessions back to the bot
* Email notification when escalation is triggered

= License & Tier System =
* License key activation/deactivation
* Tier-based feature gating (Free / Personal / Pro / Agency)
* Remote license server validation with offline fallback
* Admin UI for license management

= WooCommerce =
* Optional conversion tracking (order ID + revenue per session)

== Installation ==

1. Upload the plugin to `/wp-content/plugins/leadnest/` or install via Plugins > Add New
2. Activate the plugin
3. Go to **LeadNest → AI Settings** and enter your Anthropic or OpenAI API key
4. Customize appearance in **LeadNest → Appearance**
5. The chat widget is now live on your site

= Embedding on external websites =

Go to **LeadNest → Setup Guide** to get your embed script. Paste it before `</body>` on any website.

== Frequently Asked Questions ==

= Does LeadNest store my visitors' chat data on my server? =

Yes. All sessions, messages, and leads are stored in your WordPress database. No data is sent to external services except your configured AI provider (Anthropic or OpenAI).

= Do I need to pay for LeadNest? =

LeadNest itself is free. You provide your own AI API key (Anthropic or OpenAI) and pay only your actual usage costs, typically $0.12–$1.70 per 500 conversations.

= Will the chat widget conflict with my theme? =

No. The widget uses Shadow DOM, which fully isolates its CSS from your theme. It works with any WordPress theme and any external framework.

= Can I use it on websites that aren't WordPress? =

Yes. Go to LeadNest → Setup Guide and copy the embed script. Paste it into any website — React, Next.js, Laravel, plain HTML, or Webflow. All data flows back to your WordPress backend.

= Which AI models are supported? =

**Anthropic:** Claude Opus 4.6, Claude Sonnet 4.6, Claude Haiku 4.5, Claude 3.5 Sonnet, Claude 3.5 Haiku
**OpenAI:** GPT-4o, GPT-4o Mini, GPT-4 Turbo, GPT-3.5 Turbo

== Screenshots ==

1. Dashboard — sessions, leads, and stats at a glance
2. Leads page — lead cards with status tracking and notes
3. Chat Logs — full transcripts with visitor geo data
4. AI Settings — provider selection, API key, system prompt
5. Appearance — color presets and branding options
6. Chat Widget — live on a website with Shadow DOM isolation

== Changelog ==

= 2.0.0 =
* License & tier system — activate license keys with feature gating (Free/Personal/Pro/Agency)
* Facebook Messenger channel — webhook integration, two-way AI conversations
* Telegram Bot channel — BotFather integration with one-click webhook setup
* Google Calendar sync — OAuth 2.0 integration, auto-create calendar events from bookings
* Twilio SMS reminders — configurable hours-before booking reminders
* Live agent handoff — keyword/uncertainty escalation, real-time admin takeover, return to AI
* Rolling conversation summarization — compress long histories to stay within token limits
* WooCommerce conversion tracking — match orders to chat sessions by email
* Admin UI for Google Calendar, Twilio, Messenger, Telegram, and license settings
* Version bump to 2.0.0

= 1.8.0 =
* WhatsApp Business API — full two-way AI conversations via webhook
* Multi-channel architecture — shared AI brain across all messaging platforms
* Channels admin page with connection status and setup guides

= 1.7.0 =
* Appointment booking system — chatbot-driven scheduling
* Weekly availability settings with day/time configuration
* Booking admin page with status management
* Email confirmations to visitors and admin

= 1.5.0 =
* Website crawler — PHP cURL + DOMDocument
* Auto-recrawl via WP Cron (daily/weekly/monthly)
* Q&A CSV import/export
* Missed questions auto-flagging with confidence detection

= 1.4.0 =
* Custom website embed — site key system, CORS middleware, embed script generator
* Dual deployment model — serve WordPress + external sites from one backend

= 1.3.0 =
* Lead capture system — automatic extraction from conversations
* Leads admin page with status tracking, notes, CSV export
* Email notification on new lead
* Knowledge Base — manual content entries
* Q&A Trainer — exact-answer Q&A pairs
* Missed Questions log — auto-detection and admin review
* Industry system prompt templates
* Setup Guide with embed instructions
* Full admin CSS + JS
* Shadow DOM chat widget

== Upgrade Notice ==

= 2.0.0 =
Major release. Adds Facebook Messenger, Telegram, Google Calendar sync, Twilio SMS reminders, live agent handoff, license system, and WooCommerce conversion tracking.
