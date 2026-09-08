# DigitalBuilders — Autonomous Sales Funnel Architecture

> Design document. Ground truth as of 2026-09-08, written against the actual repo
> (Laravel 13 + Inertia/Vue 3 + Neon Postgres on Vercel) and the existing CRM schema.

---

## 0. The strategic reframe (read this first)

The brief was: *"fetch all requirements from the market from every platform, pitch the client
within an hour, talk to them like a human, close into the CRM."*

Three parts of that are excellent. One part will get the business banned and fined.

| Instinct | Verdict | What to do instead |
| :-- | :-- | :-- |
| Fetch published requirements from every platform | **Correct, and the core moat** | Build the multi-source ingest layer |
| Respond within an hour | **Correct — aim for 5 minutes** | Real-time webhooks, not a daily batch |
| Close into the CRM | **Correct** | Already 70% built here |
| Cold-blast strangers on WhatsApp/phone, AI posing as a human | **Illegal / policy-fatal** | Intent-only outreach + disclosed AI |

### Why the cold-spray version fails

- **WhatsApp**: Meta mandates documented opt-in before any business-initiated template. Cold
  lists have none. The result is blocks → quality rating collapse → number ban. Marketing-category
  templates are additionally paused for US numbers with no announced end date.
- **India voice/SMS**: TCCCPR 2018 requires DLT registration, 140-series headers for promotional
  calls, DND scrubbing, and a 9 AM–9 PM window. Penalties escalate to service disconnection;
  DPDP Act 2023 adds consent-processing exposure.
- **AI pretending to be human**: EU AI Act Art. 50 (in force Aug 2026) requires disclosure;
  California SB 1001 and Texas SB 140 (30-second disclosure) apply to US targets. TCPA exposure
  on outbound AI calls without prior express written consent runs $500–$1,500 **per call**.
- **LinkedIn scraping / DM bots**: ToS violation, account termination.

### The version that actually works

**Intent arbitrage.** Only touch buyers who have *published a requirement* and thereby invited
contact. A buy-lead on IndiaMART, an Upwork job post, a tender, an RFP, an inbound estimator
submission — every one of these carries a legitimate, auditable basis for the first message.

That is both legally clean **and** converts 5–10× better than cold. Cold B2B first-touch replies
at 2–5%. Intent-based first-touch delivered inside an hour replies at 15–30%.

**The AI is the SDR's hands, not its face.** It sources, filters, enriches, scores, and drafts in
Ashish's voice. A human taps approve. That is ~30 seconds per lead — 50 leads/day is 25 minutes of
founder time, which is *faster* than the fantasy version and carries zero legal risk.

---

## 1. What already exists in this repo

Good news: most of the destination is built.

| Component | Path | State |
| :-- | :-- | :-- |
| Lead / Organization / Deal / Activity / Payment schema | `database/migrations/2026_09_08_000001_*` | Done, well-modelled |
| Dual-currency pipeline (INR/USD), 8 deal stages | `app/Models/Deal.php` | Done |
| 5-touch cadence fields (`touchpoint_count`, `next_action_date`) | `2026_09_08_000002_*` | Done |
| Lead scoring (0–100, deterministic) | `app/Services/LeadScoringService.php` | Done |
| AI next-action + script generation | `app/Http/Controllers/Crm/CrmAiController.php` | Done (playbook + OpenAI enhance) |
| Site AI chat agent w/ full price book | `app/Http/Controllers/Library/AiChatController.php` | Done (gpt-4o-mini) |
| External lead webhook endpoint | `POST /api/crm/leads/webhook` | **Done — this is the ingest door** |
| Razorpay + Stripe webhooks | `routes/crm.php` | Stubbed |
| Proposal generator + public accept portal | `CrmProposalController` | Done |
| Native booking + Google Calendar invites | commit `4ee1fd8` | Done |
| Segment pitch playbook, battlecards, cadence | `docs/SALES_KIT_AND_PITCH_PLAYBOOK.md` | Done — this is the AI's prompt corpus |

### The three real gaps

1. **There is no scheduler.** `routes/console.php` contains only `inspire`. `bootstrap/app.php`
   registers no `withSchedule`. `vercel.json` has no `crons` block. *"Runs daily" currently has
   no mechanism whatsoever.* This is gap #1.
2. **There is no source layer.** Every lead must be typed in or arrive via the site form. Nothing
   fetches requirements from anywhere.
3. **There is no consent ledger.** Nothing records *why* we are allowed to message a given person.
   Under DPDP this is the difference between a business and a liability.

---

## 2. Runtime: how a Laravel app on Vercel runs a daily job

Vercel PHP is request-scoped. There is no long-lived process, so `queue:work` and `schedule:work`
cannot run. `QUEUE_CONNECTION=database` currently means jobs are queued and **never consumed**.

Three options, in the order we should adopt them:

**A. Vercel Cron → signed route (simplest)**
```json
"crons": [{ "path": "/api/internal/tick?token=...", "schedule": "*/5 * * * *" }]
```
Bounded by function timeout (10s hobby / 300s pro). Fine for dispatch and light polling.

**B. GitHub Actions cron → signed route (recommended now)**
Two workflows already exist in `.github/workflows/`. Actions gives up to 6h runtime, free minutes,
secret storage, and lets heavy scraping run in Node/Python *outside* the PHP function while the
results POST into `/api/crm/leads/webhook`. This is the pragmatic choice for months 1–6.

**C. A £4/mo always-on worker (endgame)**
One Hetzner/Railway box running `php artisan schedule:run` (cron) + `php artisan queue:work`
against the same Neon database. Do this when send volume justifies it. Vercel keeps serving the
web app; the box does the funnel work.

### Cadence — this is not one daily cron, it is four loops

| Loop | Interval | Job |
| :-- | :-- | :-- |
| **Realtime** | webhook, ~0s | IndiaMART push, own-site forms, inbound replies → instant draft + alert |
| **Fast** | every 5 min | Poll Upwork / Freelancer / Reddit / HN; dedupe; relevance-score |
| **Daily 06:00 IST** | 1×/day | Tenders, funding news, enrichment, re-score pipeline, dispatch touches 2–5 due today, **Morning Battle Card** digest |
| **Nightly 21:30 IST** | 1×/day | Outcome roll-up, reply/meeting rates per source & variant, prune dead sources, capacity check |

The daily cron is the *strategy* layer. The hour-response promise is kept by the **realtime** loop,
not the daily one. This distinction is the whole design.

---

## 3. The pipeline

```
 SOURCE ──▶ NORMALIZE ──▶ DEDUPE ──▶ ENRICH ──▶ SCORE ──▶ CONSENT GATE
                                                              │
                                                              ▼
   CRM DEAL ◀── BOOK ◀── CONVERSE ◀── SEND ◀── HUMAN GATE ◀── DRAFT
```

Every stage is a queued job writing to `market_requirements`, so a failure at any stage is
inspectable and replayable rather than a silent drop.

**Consent gate** is the stage everyone skips and it is the one that keeps the business alive. A
requirement cannot proceed to DRAFT unless it carries a `consent_basis` from the allowed set:
`platform_inquiry` · `public_rfp` · `tender` · `web_form` · `referral` · `explicit_optin`.
No basis → the record parks in a "research only" bucket, viewable but unmessageable.

---

## 4. Where to pitch — the source layer, ranked by ROI

### Tier 1 — Buyer published a requirement and expects contact *(clean consent, highest intent)*

| Source | Access | Fit | Notes |
| :-- | :-- | :-- | :-- |
| **IndiaMART Buy Leads** | **Push API (real-time)** + Pull API (5 min) | 🥇 Manufacturers, retail, distributors | Requires paid seller account. DIY integration, no hand-holding. Points straight at existing `/api/crm/leads/webhook`. **Build this first.** |
| TradeIndia / ExportersIndia / Justdial | Lead APIs (paid tiers) | 🥇 Same segments | Same webhook shape; add as adapters |
| **Upwork** | GraphQL `api.upwork.com/graphql`, OAuth2 | 🥇 International USD tickets | **Read-only for jobs.** No mutation exists to submit a proposal or spend Connects — bidding stays manual in their UI. So: auto-draft, human submits. |
| **Freelancer.com** | Public API incl. project feed + bid placement | 🥈 International | One of the few platforms permitting programmatic bidding |
| GeM / CPPP eProcure / state tender portals | Public listings, RSS/scrape | 🥈 Large-ticket, slow cycle | High value, 3–9 month cycles. Daily loop, not fast loop |
| Clutch / GoodFirms RFPs | Email digest → parse | 🥈 International agency RFPs | Ingest by piping the digest into a mailbox the funnel reads |

### Tier 2 — Public signal, reply on their own published channel

| Source | Access | Fit |
| :-- | :-- | :-- |
| Hacker News "Seeking Freelancer" monthly thread | Algolia HN API, **free** | 🥇 High-quality intl. leads, zero cost |
| Reddit (r/forhire, r/startups, r/Entrepreneur) | Official Reddit API | 🥈 Intl. SMB |
| X/Twitter search | API paid tier | 🥉 Noisy |
| **Job boards as a build-backlog proxy** | LinkedIn Jobs API, Naukri, Wellfound | 🥇 **Underrated** |

> **The job-board play**: a company posting *"urgently hiring 3 Laravel developers"* is a company
> with a build backlog and no capacity. That is a qualified agency lead hiding in plain sight, it
> is public, and the pitch writes itself: *"I saw you're hiring three Laravel devs — that's a
> 4-month hiring cycle. We can ship the same scope in 6 weeks, fixed price."*

### Tier 3 — Trigger events *(best for USD premium tickets)*

| Signal | Source | Pitch it unlocks |
| :-- | :-- | :-- |
| Funding round closed | Crunchbase API / news RSS | "You just raised — here's the platform build" |
| Legacy/failing stack | BuiltWith or Wappalyzer-style detection | "You're on WooCommerce at 30% checkout drop-off" |
| **Terrible site performance** | **Google PageSpeed Insights API, free** | *"Your site scores 34 on mobile. Here's the 3 fixes."* — devastating opener, costs nothing |
| No website / dead site | Google Places API by pin code | Local Ludhiana/NCR SMB sweep |

### Tier 0 — The source already owned and under-exploited

The site's own estimator, pricing page, `/book`, blog CTAs and newsletter. 100% consent-clean,
zero acquisition cost, and currently answered on human time. **Cutting first-response on these
from hours to 5 minutes is the single highest-ROI change in this document** — and it needs no new
vendor, no new API key, and no new spend.

---

## 5. Schema additions

Additive to the existing CRM tables. Nothing here breaks what is built.

```php
// market_requirements — every raw requirement ever seen
$t->id();
$t->string('source', 50)->index();          // indiamart, upwork, hn, tender, places, own_site
$t->string('source_ref', 191);              // platform's own id
$t->string('fingerprint', 64)->index();     // sha256(normalized title+body+contact) for dedupe
$t->unique(['source', 'source_ref']);       // idempotent re-polling
$t->string('title'); $t->text('body');
$t->string('budget_raw')->nullable(); $t->decimal('budget_normalized',12,2)->nullable();
$t->string('currency',10)->default('INR'); $t->string('country',10)->default('IN');
$t->string('segment',50)->nullable();       // matches playbook segments
$t->unsignedTinyInteger('relevance_score')->nullable();  // 0-100, LLM-assigned
$t->string('status',30)->default('new')->index();
    // new → scored → rejected | parked_no_consent | approved → contacted → converted
$t->foreignId('lead_id')->nullable()->constrained();
$t->string('url',500)->nullable(); $t->json('raw_payload');
$t->timestamp('posted_at')->nullable(); $t->timestamps();

// consent_records — the DPDP audit trail. Non-negotiable.
lead_id, channel(email|whatsapp|phone|linkedin),
basis(platform_inquiry|public_rfp|tender|web_form|referral|explicit_optin),
evidence_url, evidence_payload json, captured_at, revoked_at

// suppression_list — checked before EVERY send
value_hash, type(email|phone|domain), reason(dnd|unsubscribed|bounced|complaint|competitor), created_at

// outreach_messages — one row per attempted touch
lead_id, requirement_id, channel, direction, variant_id, subject, body,
status(drafted|approved|sent|delivered|opened|replied|bounced|failed),
consent_record_id, external_id, sent_at, replied_at

// sources — runtime config, so sources are toggled from the CRM not a deploy
key, label, enabled, poll_interval_minutes, last_polled_at, last_error, health_score, config json

// experiments — the funnel must learn
variant_id, segment, channel, hypothesis, sends, replies, meetings, wins, revenue
```

`activities` already has `metadata json` + `touchpoint_number`, so the cadence engine writes into
the existing timeline with no change.

---

## 6. The AI layer

The site chat already runs `gpt-4o-mini`; `CrmAiController` runs the playbook deterministically
with an optional LLM enhance and a graceful fallback. That fallback pattern is correct — keep it
everywhere, so an API outage degrades the funnel instead of stopping it.

### Three-tier model routing (cost is the whole game at this volume)

| Tier | Job | Volume/day | Model |
| :-- | :-- | :-- | :-- |
| **Filter** | "Is this requirement relevant? Segment? Spam? Budget band?" | 300–800 | **Haiku 4.5** (`claude-haiku-4-5-20251001`) |
| **Draft & converse** | Personalized pitch in Ashish's voice; handle replies & objections | 20–60 | **Sonnet 5** (`claude-sonnet-5`) |
| **Strategy** | Proposal generation, hard objections, nightly digest & funnel diagnosis | 5–15 | **Opus 5** (`claude-opus-5`) |

The `SALES_KIT_AND_PITCH_PLAYBOOK.md` + the price book become one shared system prompt (~4k tokens,
identical on every call). **Enable prompt caching on it** — it is the difference between a viable
and an absurd monthly bill at 800 classifications/day.

### The filter prompt is the highest-leverage prompt in the system

Precision matters far more than recall. Missing a lead costs one opportunity; a false positive
costs founder attention, which is the actual scarce resource. Tune it to be harsh, and log every
rejection with its reason so the threshold is tunable against real outcomes.

### The human gate

One keyboard-driven CRM screen — the **Morning Battle Card**:

```
┌─ 12 new requirements · 4 high-intent ────────────────────────┐
│ ▸ Sharma Textiles, Ludhiana        IndiaMART   score 87      │
│   "need dealer ordering app, 200 dealers, offline"           │
│   consent: platform_inquiry ✓   suppression: clear ✓          │
│   ── draft (Sonnet, Ashish voice, Segment 1 hook) ──          │
│   "Hi Mr Sharma, saw your requirement for a dealer ordering  │
│    app. We built exactly this for Garg Enterprises in        │
│    Ludhiana — dispatch errors 14% → zero, works offline..."  │
│   [A]pprove & send   [E]dit   [S]kip   [R]egenerate          │
└──────────────────────────────────────────────────────────────┘
```

Auto-send is permitted **only** for: (a) replies within a thread the buyer started, and (b) the
first acknowledgement to an own-site form. Everything else passes the gate.

---

## 7. Integration shortlist

| Layer | Pick | Cost | Why |
| :-- | :-- | :-- | :-- |
| Ingest — India | IndiaMART Push API | paid seller plan | Real-time, highest-intent India source |
| Ingest — Intl | Upwork GraphQL + Freelancer API | free w/ account | Read jobs; bid manually on Upwork |
| Ingest — free | HN Algolia, Reddit API, PageSpeed API | ₹0 | Zero-cost intl. + a killer opener |
| Ingest — long tail | Apify actors | ~$49/mo | For sources with no API |
| Enrich | Hunter.io or Apollo.io | $49–99/mo | Verified email + firmographics |
| Email send | **Resend or Postmark** | $20/mo | **Config stubs already in `config/services.php`** |
| Deliverability | SPF + DKIM + DMARC, separate outreach subdomain, 4-week warm-up | ₹0 | Never send outreach from the primary domain |
| WhatsApp | Meta Cloud API — **opted-in contacts only** | usage | Already used on the Gaushala project |
| Voice | Vapi/Retell + Exotel/Plivo, DLT-registered, disclosed | usage | Phase 4 only |
| Book | Existing native scheduler + Google Calendar | ₹0 | Already built |
| Pay | Razorpay + Stripe | % | Webhooks already routed |
| Alerts | Slack | ₹0 | **`config/services.php` already has the bot token stub** |
| Observability | Sentry + Plausible (both live) + funnel metrics table | ₹0 | Already wired |

**Realistic run cost at steady state: ₹15,000–₹30,000/month**, dominated by data/enrichment
subscriptions rather than LLM tokens (which land around ₹3–6k/mo with caching and Haiku filtering).

### On Claude connectors specifically

The connected account has **Gmail, Google Calendar, Google Drive, Gamma, Canva, Figma, Vercel,
Windsor.ai**. Genuinely useful here: **Gmail** (reply triage and drafting), **Calendar** (booking),
**Drive** (proposal archive), **Gamma** (auto-build a per-segment pitch deck), **Vercel** (deploy
health). Worth adding: **Slack**.

⚠️ **Important distinction most people get wrong**: claude.ai connectors operate inside *your chat
sessions*. They are an **operator's cockpit**, not a production runtime. The funnel itself must
call vendor APIs directly from Laravel — do not architect the money pipeline around chat-session
connectors, because nothing schedules or supervises them at 3 AM.

---

## 8. Realistic volume math

Using the playbook's own benchmark (100 conversations → 20 discovery → 5 proposals → 2 closed):

| Stage | Daily | Monthly |
| :-- | --: | --: |
| Raw requirements ingested | 300–800 | ~15,000 |
| Pass relevance filter (~5–8%) | 20–50 | ~900 |
| Pass dedupe + suppression + consent | 10–25 | ~450 |
| Outreach sent | 10–25 | ~450 |
| Replies (15–30% at <1h response) | 2–6 | ~90 |
| Discovery calls booked | 1–2 | ~30 |
| Proposals sent | — | 8–12 |
| **Closed won** | — | **3–5** |

At a blended ₹2L (India) / $8k (international) ticket: **₹8L–₹20L per month**, ramping over ~90 days
as deliverability warms and the filter is tuned. Month 1 will look like nothing; that is normal and
expected — email warm-up alone is 4 weeks.

### The constraint that will actually bite: delivery capacity, not lead flow

A founder-led studio ships 2–3 projects concurrently. At 3–5 closes/month the funnel **out-runs
delivery inside one quarter**, and an over-full pipeline with slipping delivery destroys the
referral engine that is worth more than the funnel.

So build a **capacity throttle** into scoring: when weighted pipeline exceeds delivery capacity,
the minimum-ticket floor auto-raises and low-band segments stop being contacted. The funnel's job
past that point is not more leads — it is *better-priced* leads. This is the single most important
design decision in this document and the one almost everyone omits.

---

## 9. Phased build plan

| Phase | Weeks | Deliverable | New spend |
| :-- | :-- | :-- | --: |
| **0 — Foundations** | 1 | Scheduler (GH Actions → signed `/api/internal/tick`), `consent_records`, `suppression_list`, own-site speed-to-lead < 5 min, Slack alerts | **₹0** |
| **1 — Sight** | 2–3 | `market_requirements` + `sources`; IndiaMART Push + Upwork + HN adapters; Haiku relevance filter; Morning Battle Card digest | IndiaMART plan |
| **2 — Voice** | 4–5 | Draft-and-approve console; Sonnet drafting on playbook corpus; Resend + domain warm-up; `outreach_messages` | ~$20/mo |
| **3 — Conversation** | 6–8 | Reply-handling agent, objection battlecards, auto-booking into existing scheduler, auto-draft into existing proposal generator | enrichment |
| **4 — Scale** | 9–12 | Freelancer/tenders/job-board/PageSpeed sources, `experiments` variant testing, disclosed AI voice on opt-ins, capacity throttle | voice usage |

**Phase 0 is the one to start today.** It requires no vendor, no budget, and no approval — and
cutting first-response time on leads already arriving is likely worth more this month than every
other phase combined.

---

## 10. Non-negotiables

1. Never message anyone without a logged `consent_record`.
2. Check `suppression_list` before every single send. No exceptions, no bypass flag.
3. Every AI-initiated call or chat identifies itself as AI within the first 10 seconds.
4. Outreach email leaves a dedicated subdomain, never the primary domain.
5. WhatsApp is for opted-in contacts only — the number is infrastructure, not a channel to burn.
6. Every message carries a working one-tap opt-out, and it is honoured within seconds.
7. Indian voice outreach only 9 AM–9 PM, DLT-registered, DND-scrubbed.
8. Log every LLM decision with its reasoning — an unauditable funnel cannot be tuned or defended.
