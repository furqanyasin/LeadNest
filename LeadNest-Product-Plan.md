# LeadNest — AI Lead Capture Chatbot
### Complete Product Plan & Feature Roadmap
**Author:** Furqan | **Version:** 2.0 Plan | **Updated:** March 2026

---

## 🏷️ Brand Name Decision

### Why "LeadNest"

After researching the competitor landscape (WPBot, MxChat, Robofy, Tidio, FastBots, AI Engine, Chatbase, WotNot, BotPenguin), here is the chosen name:

```
LeadNest
```

**Why it works:**
- "Lead" — instantly communicates the core purpose (lead generation)
- "Nest" — implies a safe home for your leads, warmth, organization
- Two syllables — short, memorable, passes the "radio test"
- No existing WordPress plugin with this name
- Works across all industries (not niche-specific like "HomeBot" or "InspectBot")
- Sounds like a real SaaS brand (like Mailchimp, HubSpot, Drift)
- Easy to trademark
- Domain options: `leadnest.io`, `leadnest.ai`, `leadnest.co`, `getleadnest.com`

### Name Alternatives Considered

| Name | Verdict |
|------|---------|
| LeadNest | ✅ **CHOSEN** |
| ChatLeads | ❌ Too generic |
| NestChat | ❌ Chat-first, not lead-first |
| LeadBot | ❌ Taken, too generic |
| LeadPulse | ❌ Sounds like analytics tool |
| CatchBot | ❌ Negative connotation |
| LeadFlow | ❌ Taken by CRM tools |
| VisitorIQ | ❌ Too corporate |
| LeadSpark | ❌ Common in marketing tools |
| NestAI | ❌ Too broad |

---

## Table of Contents

1. [Product Overview](#1-product-overview)
2. [WordPress vs Custom Website — Dual Deployment](#2-dual-deployment)
3. [What v1.0 (HumanChatty) Already Has](#3-existing-features)
4. [Competitor Research & Market Gaps](#4-competitor-research)
5. [Complete Feature Roadmap](#5-feature-roadmap)
6. [Industry Templates](#6-industry-templates)
7. [Monetization Strategy](#7-monetization)
8. [Database Schema](#8-database-schema)
9. [Tech Stack](#9-tech-stack)
10. [Home Inspection Use Case](#10-home-inspection)
11. [Action Plan](#11-action-plan)

---

## 1. Product Overview

**LeadNest** is an AI-powered chatbot that works on any website — WordPress or custom-built — that:

1. **Reads your website** automatically (crawler)
2. **Learns from your Q&A** training
3. **Chats with visitors** naturally using Claude or GPT
4. **Captures leads** (name, email, phone) at the right moment
5. **Books appointments** directly into your calendar
6. **Stores everything** in your own database (no third-party SaaS)

### Tagline Options
- *"Every visitor is a lead. LeadNest captures them all."*
- *"Your AI receptionist. Works 24/7. Never misses a lead."*
- *"Train it once. It captures leads forever."*

---

## 2. Dual Deployment — WordPress & Custom Website

This is a major competitive advantage. Most plugins only work on WordPress.
**LeadNest works everywhere.**

### How It Works on WordPress
- Install plugin → activate → configure in WP admin
- Widget appears automatically on all pages
- Admin panel lives inside WordPress dashboard
- Uses WordPress REST API for chat, leads, sessions
- Database stored in WordPress MySQL

### How It Works on Custom Websites (HTML, React, Next.js, Laravel, etc.)
- Admin signs up / installs LeadNest on a WordPress instance (their own or a hosted control panel)
- Gets a **unique embed script** (1 line of JavaScript):

```html
<!-- LeadNest Embed — paste before </body> -->
<script src="https://yourdomain.com/wp-json/leadnest/v1/widget.js?key=YOUR_SITE_KEY" async></script>
```

- That script loads the chat widget on ANY website
- All data (leads, chats, sessions) flows back to the WordPress/LeadNest backend
- Widget is fully isolated via Shadow DOM — zero conflicts with any framework

### Architecture for Custom Website Embedding

```
Custom Website (React / HTML / Laravel / etc.)
        ↓ embed script (1 line)
LeadNest Widget JS (loaded from WP backend)
        ↓ API calls
WordPress REST Endpoints (/wp-json/leadnest/v1/)
        ↓
MySQL Database (sessions, leads, chats, knowledge)
        ↑
Claude API / OpenAI API (AI responses)
```

### Site Key System
Each website gets a unique `site_key`. This allows:
- One LeadNest installation to serve multiple websites
- Per-site settings (different branding, system prompt, knowledge base)
- Per-site lead tracking
- Agency use case: manage all client sites from one WP backend

### New Database Table for Multi-Site
```sql
CREATE TABLE leadnest_sites (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key    VARCHAR(64) UNIQUE NOT NULL,
    site_name   VARCHAR(255),
    site_url    TEXT,
    settings    LONGTEXT,  -- JSON: branding, system prompt, provider
    active      TINYINT(1) DEFAULT 1,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

---

## 3. Existing Features (Inherited from HumanChatty v1.2.0)

### AI & Models
- Multi-provider: Anthropic Claude + OpenAI GPT
- User brings their own API key (no markup, user pays directly)
- Prompt caching — 90% cost savings on Anthropic repeated calls
- Rolling summarization — compresses long chats, maintains context
- Configurable max history per API call
- Cost: ~$0.12–$1.70 per 500 visitors depending on model

### Branding & UI
- 8 color presets + full custom color support
- Custom brand name, header icon, CTA button, footer text
- Shadow DOM isolation — never conflicts with any theme or framework CSS
- Full-screen mobile optimization, no keyboard flicker bugs
- Human-like typing: flicker animation, configurable delays
- 6 industry system prompt templates

### Data & Analytics
- Full chat transcript per session
- Visitor geo-data: country, city, device, IP, page URL, user agent
- Session tracking: tab / browser / none
- Configurable session timeout + cookie expiry
- Search chat logs by message, email, IP, country
- Bulk delete + CSV export

### WooCommerce
- Automatic conversion tracking
- Order ID + revenue per session
- Dashboard: Conversions + Revenue cards

### Admin Pages
- Dashboard, Chat Logs, Appearance, Behavior, AI Settings, Setup Guide

---

## 4. Competitor Research

### Market Overview (2025–2026)

| Competitor | Strengths | Weakness | Price |
|-----------|-----------|----------|-------|
| **WPBot** | Feature-rich, WP native, NLP + button modes | Complex, expensive Pro, WordPress-only | Free + $49+/yr |
| **AI Engine** | Full AI toolkit, voice, MCP support | Not lead-focused, overkill | Free + Pro |
| **MxChat** | RAG, 100+ models, WooCommerce | Complex setup | Free + addons |
| **Robofy** | Auto-trains from URL, dead simple | Only reads public pages | Free trial + paid |
| **FastBots.ai** | Fast setup, multi-channel | External SaaS, data leaves site | $15+/month |
| **Tidio** | Live chat + AI hybrid, eCommerce | Expensive at scale | $24–$49/month |
| **Chatbase** | Simple ChatGPT-like bots | No WP native, costly | $40–$500/month |
| **WotNot** | No-code builder, multilingual | External SaaS | $29–$299/month |
| **Boei** | GPT + Claude, auto website training | External SaaS, $14/month | $14+/month |

### Key Market Insights

- **Lead capture is the #1 most requested feature** — most plugins treat it as an afterthought or a paid addon
- **Auto-training from URL** is a major selling point — Robofy built their entire business on it
- **Calendar/booking integration** is the highest-value feature for service businesses; businesses report **20–30% growth in bookings** after adding AI appointment bots
- **Chatbots push form completion rates above 90%** vs 60% abandonment on static contact forms
- **WordPress-only limits market** — websites on React, Next.js, Laravel, plain HTML are left out; LeadNest's embed script solves this
- **BYOK (Bring Your Own API Key)** is a major differentiator — competitors charge $40–$500/month, LeadNest users pay only their actual API costs (~$0.12–$1.70 per 500 chats)
- **Data privacy** is a growing concern — businesses prefer their data on their own server, not a third-party SaaS

### Pricing Benchmarks (Agency Market)
- Basic chatbot setup: **$395–$995 one-time** (TiltStack, 2025)
- Monthly SaaS subscriptions: **$19–$199/month**
- White-label agency plans: **$99–$299/month**
- Done-for-you install: **$200–$500 setup + $49/month**

---

## 5. Complete Feature Roadmap

---

### ✅ v1.3 — Lead Capture System
**Priority: CRITICAL — Build First**

#### What It Does
Bot naturally collects visitor contact info during conversation. Every captured lead appears in a dedicated Leads dashboard.

#### Conversation Flow
```
Bot:  "To help you further, could I get your first name?"
User: "Ahmed"
Bot:  "Thanks Ahmed! Best email to reach you?"
User: "ahmed@gmail.com"
Bot:  "And a phone number in case we need to call you?"
User: "0300-1234567"
Bot:  ✅ "Perfect! Someone from our team will reach out within 24 hours."
```

#### Admin Features
- New page: **LeadNest → Leads**
- Lead cards: name, email, phone, what they asked, source page, date
- Status tracking: New / Contacted / Qualified / Closed
- One-click email to lead
- Admin notes per lead
- CSV export
- Email notification on every new lead (configurable)
- Lead count card on main dashboard
- Filter by status, date, source page

#### Lead Capture Settings
- Enable / disable lead capture
- Trigger: after X messages (default: 3)
- Fields to collect: name / email / phone (toggle each)
- Custom question text for each field
- "Skip for now" option for visitors

#### System Prompt Auto-Injection
```
When you understand the visitor's need (after 2-3 exchanges), naturally ask 
for their contact info. Ask name first, then email, then phone — one at a time.
Never ask before understanding what they need. Be friendly, not pushy.
```

#### New Database Table
```sql
CREATE TABLE leadnest_leads (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id  BIGINT UNSIGNED NOT NULL,
    site_key    VARCHAR(64) DEFAULT '',
    name        VARCHAR(255) DEFAULT '',
    email       VARCHAR(255) DEFAULT '',
    phone       VARCHAR(50)  DEFAULT '',
    need        TEXT,
    source_page TEXT,
    status      ENUM('new','contacted','qualified','closed') DEFAULT 'new',
    notes       TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_email  (email),
    KEY idx_status (status),
    KEY idx_site   (site_key)
);
```

---

### ✅ v1.4 — Website Crawler & Knowledge Base
**Priority: HIGH**

#### What It Does
Admin pastes a URL → LeadNest crawls all pages → bot uses the content to answer questions accurately, without manual prompt writing.

#### Process
1. Admin enters URL → clicks "Crawl Now"
2. Plugin fetches sitemap.xml, falls back to recursive link crawl
3. HTML cleaned, text extracted, stored per page
4. Content injected into system prompt as context
5. For large sites: keyword-matched chunks injected (not full content)
6. Auto-recrawl: daily / weekly / manual (WP cron)

#### Custom Website Support
- Works on any public URL — not limited to WordPress sites
- Admin can crawl their client's website even if it's built in React or Webflow
- Manual content paste (for private pages not publicly accessible)

#### Admin Features
- New page: **LeadNest → Knowledge Base**
- URL input + "Crawl Now" button
- Page list with include/exclude toggles
- Last crawled timestamp per page
- Word count per page
- Manual content editor (override scraped content)
- "Add custom content" block (paste any text)
- Crawl status log

#### New Database Table
```sql
CREATE TABLE leadnest_knowledge (
    id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key     VARCHAR(64) DEFAULT '',
    url          TEXT,
    page_title   VARCHAR(500) DEFAULT '',
    content      LONGTEXT,
    word_count   INT DEFAULT 0,
    active       TINYINT(1) DEFAULT 1,
    last_crawled DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_site (site_key)
);
```

#### Cost Control
- Content cached as Anthropic system prompt → 90% cost reduction on repeated calls
- Per-page token limit: 2,000 tokens max
- Total knowledge cap: configurable (default 10,000 tokens)
- Only re-inject if content changed since last crawl

---

### ✅ v1.5 — Admin Q&A Trainer
**Priority: HIGH**

#### What It Does
Admin adds specific Q&A pairs. Bot answers these exactly as written — priority knowledge before AI generation.

#### Admin Features
- New page: **LeadNest → Train Bot**
- Table: Question | Answer | Active | Use Count | Actions
- Add / Edit / Delete Q&A pairs
- Import from CSV
- Export all Q&A pairs
- Sort by: most used, recently added

#### System Prompt Injection
```
Answer the following questions EXACTLY as written:

Q: What are your business hours?
A: We are open Monday to Friday, 9AM to 6PM EST.

Q: How much does an inspection cost?
A: Our standard home inspection starts at $299 for homes under 2,000 sq ft.
```

#### New Database Table
```sql
CREATE TABLE leadnest_qa (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key   VARCHAR(64) DEFAULT '',
    question   TEXT NOT NULL,
    answer     TEXT NOT NULL,
    active     TINYINT(1) DEFAULT 1,
    use_count  INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_site (site_key)
);
```

---

### ✅ v1.6 — Unanswered Questions Log
**Priority: MEDIUM**

#### What It Does
Logs questions the bot couldn't answer confidently. Admin reviews and adds answers → bot gets smarter over time.

#### Detection
- Bot uses phrases like "I'm not sure", "I don't have that info", "please contact us directly" → auto-flagged
- Admin manually marks any chat message as "unanswered"
- Optional: confidence threshold if AI returns score

#### Admin Features
- Section in Train Bot page: **Missed Questions**
- Shows: question, date, times asked, bot's response
- One-click "Answer This" → opens Q&A form prefilled
- "Mark Resolved" once answered
- Sort: most asked / most recent

#### New Database Table
```sql
CREATE TABLE leadnest_missed_questions (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key    VARCHAR(64) DEFAULT '',
    question    TEXT NOT NULL,
    bot_reply   TEXT,
    session_id  BIGINT UNSIGNED,
    ask_count   INT DEFAULT 1,
    resolved    TINYINT(1) DEFAULT 0,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_site (site_key)
);
```

---

### ✅ v1.7 — Appointment Booking System
**Priority: VERY HIGH — Biggest Revenue Driver**

#### What It Does
Bot collects lead info → offers available time slots → user picks → appointment confirmed → both get confirmation email.

#### Booking Flow
```
Bot:  "Would you like to schedule an inspection?"
User: "Yes please"
Bot:  "Great! Here are our available slots:
       → Mon Mar 24 at 10:00 AM
       → Tue Mar 25 at 2:00 PM
       → Wed Mar 26 at 9:00 AM
       Which works best for you?"
User: "Tuesday at 2"
Bot:  ✅ "Booked! Confirmation sent to ahmed@gmail.com.
       Your appointment is Tuesday March 25 at 2:00 PM."
```

#### Calendar Integrations
- **Manual availability** — admin sets weekly schedule inside plugin (no external tool needed)
- **Google Calendar** — OAuth 2.0, reads/writes real availability
- **Calendly API** — bot offers link or books via API
- **Cal.com** — open-source alternative, self-hosted
- **WordPress plugin hooks** — Amelia, BookingPress, Bookly (via action hooks)

#### Works on Custom Websites Too
- Booking widget is part of the embed script
- All bookings flow back to the WordPress/LeadNest backend
- Confirmation emails sent via WordPress wp_mail()

#### Admin Features
- New page: **LeadNest → Bookings**
- Calendar view of all appointments
- Manual availability (days + hours per day)
- Buffer time between appointments
- Appointment duration setting
- Email confirmation templates (editable)
- SMS reminder via Twilio (optional)
- Max bookings per day cap
- Booking status: Pending / Confirmed / Cancelled / Completed

#### New Database Tables
```sql
CREATE TABLE leadnest_bookings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key        VARCHAR(64) DEFAULT '',
    session_id      BIGINT UNSIGNED,
    lead_id         BIGINT UNSIGNED,
    name            VARCHAR(255),
    email           VARCHAR(255),
    phone           VARCHAR(50),
    service_type    VARCHAR(255),
    booking_date    DATE,
    booking_time    TIME,
    duration_mins   INT DEFAULT 60,
    status          ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    notes           TEXT,
    google_event_id VARCHAR(255),
    reminder_sent   TINYINT(1) DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_site    (site_key),
    KEY idx_date    (booking_date)
);

CREATE TABLE leadnest_availability (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    site_key    VARCHAR(64) DEFAULT '',
    day_of_week TINYINT,   -- 0=Sunday ... 6=Saturday
    start_time  TIME,
    end_time    TIME,
    active      TINYINT(1) DEFAULT 1
);
```

---

### ✅ v1.8 — Multi-Channel Deployment
**Priority: HIGH — Major Price Justification**

#### Channels
- **WhatsApp Business API** — highest engagement in Pakistan, UAE, and service businesses
- **Facebook Messenger** — large existing user base
- **Instagram DM** — younger audiences, high for local businesses
- **SMS** — Twilio, for US clients
- **Telegram** — tech audiences

#### Unified Inbox
All conversations from all channels in one admin inbox. Same AI brain, same knowledge base, same lead capture — regardless of channel.

#### Admin Features
- New page: **LeadNest → Channels**
- Connect each channel with API keys
- Channel-specific prompt overrides (optional)
- Unified conversation view
- Per-channel lead stats

---

### ✅ v1.9 — Live Agent Handoff
**Priority: MEDIUM**

#### Triggers
- User says "talk to human", "real person", "agent", "help"
- Bot says "I'm not sure" 2+ times in a row
- Admin-defined escalation keywords
- Admin manually takes over from dashboard

#### Admin Experience
- Real-time notification when handoff triggered
- Admin types in dashboard → goes to widget as "agent" message
- Operator name updates to real agent name
- Full chat history visible before takeover
- "Return to AI" button

---

### ✅ v2.0 — SaaS / Agency Platform
**Priority: HIGH — Biggest Long-Term Revenue**

#### License Key System
- Each purchase gets a unique license key
- Activates plugin on specified domains
- Tiers: 1 site / 5 sites / Unlimited sites
- Expired licenses show upgrade notice
- Keys tracked on your central server

#### White-Label
- Remove "LeadNest by Furqan" branding
- Add agency name + logo
- Custom admin panel colors
- Custom plugin name in WP dashboard
- Available as agency license add-on

#### Central Dashboard
- View all client chatbots from one login
- See leads across all client sites
- Push knowledge base updates to all clients
- Monitor API usage per client

#### Reseller Program
- Agencies buy wholesale, resell to clients
- You earn monthly, agencies earn monthly

---

## 6. Industry Templates

### Pre-Built System Prompt Templates

#### 🏠 Home Inspection *(your office's niche)*
```
You are a friendly assistant for [Company Name], a professional home inspection 
service. Help visitors and capture their contact info.

Ask in order:
1. Buying, selling, or already own the property?
2. City / zip code of the property?
3. Property type: house, condo, multi-unit, commercial?
4. When needed: this week, next week, or flexible?
5. Collect: name, phone, email

Key facts:
- Standard inspection starts at $299 (under 2,000 sq ft)
- Report delivered within 24 hours
- We cover [service area]
- Available Mon–Sat, 8AM–6PM
- Book online or we'll call to confirm

Always offer to schedule directly at the end.
```

#### 🏥 Healthcare / Clinic
#### ⚖️ Law Firm
#### 🚗 Auto Repair / Dealership
#### 🏗️ Contractor / Construction
#### 🏋️ Gym / Fitness Studio
#### 🏨 Hotel / Hospitality
#### 🛒 E-Commerce / Retail
#### 🎓 Education / Tutoring
#### 💼 Agency / Consulting
#### 🏡 Real Estate
#### 🦷 Dental / Medical Practice

---

## 7. Monetization Strategy

### Pricing Tiers

#### Option A — WordPress.org Freemium *(Best for Growth)*

| Plan | Price | Features |
|------|-------|----------|
| Free | $0 | Chatbot, branding, chat logs, 1 AI provider |
| Pro | $49/year | Lead capture, crawler, Q&A trainer, booking, all templates |
| Agency | $149/year | Unlimited sites, white-label, embed on any website, priority support |

#### Option B — CodeCanyon One-Time *(Best for Quick Cash)*

| License | Price |
|---------|-------|
| Regular (1 site) | $49 |
| Extended (client sites) | $149 |
| Agency bundle (10 sites) | $299 |

#### Option C — Done-For-You Service *(Your Office — Best Margins)*

| Service | Price |
|---------|-------|
| Basic install + setup | $200–$300 |
| Custom prompt + branding | $350–$500 |
| With booking + calendar | $600–$800 |
| Monthly maintenance + leads dashboard | $49–$99/month |

**Target:** Home inspectors, contractors, dentists, law firms, real estate agents across USA — use LeadPro to find and pitch them.

#### Option D — SaaS Subscriptions *(v2.0 — Long Term)*

| Plan | Price | Includes |
|------|-------|----------|
| Starter | $19/month | 1 site, 500 chats/month |
| Pro | $49/month | 3 sites, unlimited chats, booking |
| Agency | $149/month | 10 sites, white-label, multi-channel |
| Enterprise | $299/month | Unlimited sites, central dashboard |

### Revenue Projections

| Scenario | Monthly Clients | Revenue |
|----------|----------------|---------|
| Conservative | 10 DFY clients | $3,000–$5,000 |
| Medium | 50 SaaS subscribers | $2,450–$7,450 |
| Aggressive | 200 SaaS + 20 DFY | $15,000–$25,000 |

**Home Inspection niche target:**
20 clients × $79/month = **$1,580/month recurring** from one niche alone.

---

## 8. Database Schema

### Complete Schema

```sql
-- INHERITED (from HumanChatty v1.2.0, renamed with leadnest_ prefix)
leadnest_sessions          -- visitor sessions, geo, conversion tracking
leadnest_chats             -- individual messages per session

-- v1.3
leadnest_leads             -- captured leads (name, email, phone, need, status)

-- v1.4
leadnest_knowledge         -- crawled website content per page

-- v1.5
leadnest_qa                -- admin-added Q&A training pairs

-- v1.6
leadnest_missed_questions  -- questions bot couldn't answer

-- v1.7
leadnest_bookings          -- scheduled appointments
leadnest_availability      -- weekly available time slots

-- Multi-site / custom website
leadnest_sites             -- site keys, per-site settings

-- v2.0
leadnest_licenses          -- license keys, domains, tiers, expiry
```

---

## 9. Tech Stack

### Core (WordPress Plugin)
- **PHP** — plugin backend, REST endpoints
- **MySQL** — all data via `$wpdb`
- **Vanilla JS + Shadow DOM** — chat widget
- **WordPress REST API** — `/wp-json/leadnest/v1/` endpoints
- **wp_mail()** — lead + booking email notifications
- **WP Cron** — auto-recrawl, reminder scheduling

### Custom Website Embed
- **JavaScript embed snippet** — 1-line script tag
- **CORS headers** — allow cross-origin requests from external domains
- **Site key authentication** — each external site has a unique key
- **Shadow DOM widget** — isolated, zero CSS conflicts on any framework

### AI Providers
- **Anthropic Claude API** — primary (prompt caching = 90% cost savings)
- **OpenAI GPT API** — secondary option

### Additions for Future Versions

| Feature | Technology |
|---------|-----------|
| Website crawler | PHP cURL + DOMDocument |
| Knowledge chunking | PHP + token counting |
| Google Calendar | OAuth 2.0 API |
| Booking emails | wp_mail() + HTML templates |
| SMS reminders | Twilio REST API |
| WhatsApp | WhatsApp Business API (Meta) |
| License server | Separate PHP API + MySQL |
| Webhook outbound | PHP cURL, configurable endpoints |

---

## 10. Home Inspection Use Case

### The Problem
- Miss calls after business hours
- Slow email replies = lost leads to competitors
- No 24/7 presence on website
- Manual qualification wastes staff time

### LeadNest Solution
- Answers visitors at 2AM when buyers are researching
- Qualifies: location, property type, urgency, budget
- Captures: name + phone + email
- Books inspection slot directly
- Admin notified instantly by email

### Proven Results (Industry Data)
- 20–30% increase in booked appointments after adding AI chat
- 90%+ form completion via chatbot vs 60% abandonment on static forms
- 50% of buyer conversations happen outside business hours

### Pricing for Home Inspection Clients

| Package | Price |
|---------|-------|
| Starter (chat + lead capture) | $250 setup + $49/month |
| Standard (+ booking) | $450 setup + $79/month |
| Premium (+ WhatsApp + follow-up) | $800 setup + $99/month |

**Target 20 clients → $1,580–$1,980/month recurring.**
**Use LeadPro to find companies → pitch LeadNest as the solution.**

---

## 11. Action Plan

### Week 1 — Rename & Rebrand
- [ ] Rename all PHP functions: `wpc_` → `leadnest_`
- [ ] Rename DB tables: `wpc_sessions` → `leadnest_sessions` (migration script)
- [ ] Update Plugin Name in header: `HumanChatty` → `LeadNest`
- [ ] Update Author: `Bytebex` → `Furqan`
- [ ] Update footer: "Powered by Bytebex" → "Powered by LeadNest"
- [ ] Register domain: `leadnest.io` or `getleadnest.com`
- [ ] Create GitHub repo: `furqan-dev/leadnest`

### Week 2 — Build v1.3 (Lead Capture)
- [ ] Create `leadnest_leads` table
- [ ] Add lead extraction logic to chat endpoint
- [ ] Build Leads admin page with filter + status
- [ ] Email notification on new lead
- [ ] Lead Capture settings panel
- [ ] Auto-inject lead capture prompt
- [ ] Test with home inspection template

### Week 3 — Build v1.4 (Crawler)
- [ ] PHP crawler: sitemap → cURL → DOMDocument
- [ ] Knowledge Base admin page (include/exclude per page)
- [ ] Store in `leadnest_knowledge`
- [ ] Inject into system prompt
- [ ] WP Cron auto-recrawl
- [ ] Test on client website URL

### Week 4 — Custom Website Embed
- [ ] Add site key system (`leadnest_sites` table)
- [ ] CORS middleware for cross-origin API calls
- [ ] Widget embed script generator in admin
- [ ] Test on plain HTML site, React site, Next.js site
- [ ] Documentation for non-WordPress users

### Week 5 — Launch
- [ ] Demo site: home inspection chatbot live
- [ ] 2-min demo video
- [ ] List on CodeCanyon ($49 regular, $149 extended)
- [ ] WordPress.org submission (free version)
- [ ] Landing page: leadnest.io or getleadnest.com
- [ ] Use LeadPro: outreach to 50 home inspection companies

### Month 2
- [ ] v1.5 Q&A Trainer
- [ ] v1.6 Missed Questions Log
- [ ] v1.7 Appointment Booking (manual availability first, Google Calendar next)

### Month 3+
- [ ] v1.8 Multi-channel (WhatsApp first)
- [ ] v1.9 Live agent handoff
- [ ] v2.0 License + white-label system

---

## Competitive Advantages

| Advantage | LeadNest | Competitors |
|-----------|----------|-------------|
| Works on WordPress AND custom websites | ✅ | ❌ Most are WP-only |
| Bring your own API key | ✅ | ❌ Most charge SaaS fees |
| Data stays on your server | ✅ | ❌ Most send to external SaaS |
| Prompt caching (90% cost reduction) | ✅ | ❌ |
| Rolling summarization | ✅ | ❌ |
| Shadow DOM (zero conflicts) | ✅ | ❌ |
| WooCommerce conversion tracking | ✅ | Few have this |
| Lead capture built-in (not an addon) | ✅ | ❌ WPBot charges extra |
| Website auto-crawler | ✅ (v1.4) | Only some |
| Appointment booking | ✅ (v1.7) | Only some |
| White-label agency option | ✅ (v2.0) | Few |
| Free forever (BYOK model) | ✅ | ❌ |

---

*LeadNest — AI Lead Capture Chatbot*
*Author: Furqan | WordPress Plugin + Custom Website Embed*
*Plan Version: 2.0 | March 2026*
