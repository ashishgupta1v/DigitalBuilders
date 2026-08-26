# DigitalBuilders Operations Runbook (Ops, Backups & Incident Response)

## 1. System Overview & Architecture

- **Primary Domain**: `https://www.digitalbuilders.in`
- **Application Framework**: Laravel 12 / 13 (Modular Monolith) with Inertia.js & Vue 3 + TypeScript
- **Deployment Platform**: Vercel (Serverless PHP Runtime)
- **Database**: PostgreSQL (Managed / Serverless via Neon / Supabase) with SQLite local development fallback
- **Static Assets & PWA**: Vite build artifacts, Service Worker (`/sw.js`), Web App Manifest (`/manifest.webmanifest`)

---

## 2. Health Checks & Uptime Monitoring

### Production Endpoint
- **URL**: `https://www.digitalbuilders.in/health`
- **Expected Status**: `HTTP 200 OK`
- **Response Format**:
  ```json
  {
    "status": "healthy",
    "app": "DigitalBuilders",
    "environment": "production",
    "timestamp": "2026-08-26T03:45:00Z"
  }
  ```

### Provisioning Uptime Checks (BetterStack / UptimeRobot)
1. Set up an HTTP(S) monitor pointing to `https://www.digitalbuilders.in/health`.
2. Configure **Check Interval**: `1 minute` / `3 minutes`.
3. Set alert escalation to Telegram / WhatsApp / SMS and Email (`ashishgupta1v@gmail.com`).

---

## 3. Lead Capture & Resilience Pipeline

- **Lead Repository**: Persists instantly into the `leads` table before any mailer dispatch.
- **Fail-Safe Mechanism**: Even if outbound SMTP or transactional mail provider fails, the lead is permanently stored with `status = 'new'`.
- **Honeypot Trap**: Spambots filling `_hp_company` receive `HTTP 200` without recording rows or dispatching emails.
- **Auto-Reply & Notification**:
  - `NewLeadMail` dispatches to `ashishgupta1v@gmail.com` with `Reply-To: {lead_email}`.
  - `LeadAutoReplyMail` delivers a branded confirmation to the prospect with an instant WhatsApp deep link.

---

## 4. Database Backup & Staging Restore Drill

### Manual Backup Command (PostgreSQL)
```bash
# Export production database dump
pg_dump -h $DB_HOST -U $DB_USERNAME -d $DB_DATABASE -F c -b -v -f digitalbuilders_backup_$(date +%Y%m%d_%H%M%S).dump
```

### Staging Restore Procedure
```bash
# Verify and restore backup to staging environment
pg_restore -h $STAGING_DB_HOST -U $STAGING_DB_USERNAME -d $STAGING_DB_DATABASE -v digitalbuilders_backup_YYYYMMDD_HHMMSS.dump
```

---

## 5. Security & Deliverability Checklist (SPF / DKIM / DMARC)

| Record Type | Host | Target / Value | Purpose |
|---|---|---|---|
| **TXT (SPF)** | `@` | `v=spf1 include:_spf.google.com include:resend.com ~all` | Authorizes outbound mail servers |
| **CNAME (DKIM)** | `resend._domainkey` | `dkim.resend.com` | Cryptographic signature of emails |
| **TXT (DMARC)** | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:dmarc-reports@digitalbuilders.in` | Protects domain from spoofing |

---

## 6. Incident Response & Escalation Protocol

1. **Severity 1 (Lead Pipeline / Site Down)**:
   - Check Vercel Deployment status.
   - Test `/health` endpoint response.
   - Check database connection quota / connection pooling.
2. **Severity 2 (Client JS Errors)**:
   - Check Sentry dashboard for stacktraces.
   - Trigger fallback `npm run build` and redeploy.
3. **Emergency Contact**: Ashish Gupta (+91 90870 21592 / `hello@digitalbuilders.in`).
