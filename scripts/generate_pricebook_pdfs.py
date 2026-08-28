"""
Script to generate high-fidelity, professional PDF Price Books for DigitalBuilders.
Generates:
  - public/downloads/digitalbuilders-pricing-india-inr.pdf
  - public/downloads/digitalbuilders-pricing-international-usd.pdf
"""

import os
from reportlab.lib.pagesizes import letter
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super(NumberedCanvas, self).__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super(NumberedCanvas, self).showPage()
        super(NumberedCanvas, self).save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#64748b"))
        
        # Header (pages > 1)
        if self._pageNumber > 1:
            self.drawString(36, 756, "DigitalBuilders · Architectural Price Book & Service Catalogue")
            self.drawRightString(576, 756, "FY 2026–27 · Confidential")
            self.setStrokeColor(colors.HexColor("#e2e8f0"))
            self.setLineWidth(0.5)
            self.line(36, 750, 576, 750)
            
        # Footer
        self.setStrokeColor(colors.HexColor("#e2e8f0"))
        self.setLineWidth(0.5)
        self.line(36, 42, 576, 42)
        self.drawString(36, 30, "DigitalBuilders · https://digitalbuilders.in · hello@digitalbuilders.in · +91 90870 21592")
        self.drawRightString(576, 30, f"Page {self._pageNumber} of {page_count}")
        self.restoreState()

def build_pdf(filename: str, region: str = "INR"):
    is_inr = (region == "INR")
    currency = "INR (₹)" if is_inr else "USD ($)"
    
    doc = SimpleDocTemplate(
        filename,
        pagesize=letter,
        leftMargin=36,
        rightMargin=36,
        topMargin=46,
        bottomMargin=50
    )
    
    styles = getSampleStyleSheet()
    
    # Custom styles
    primary_color = colors.HexColor("#0f172a") # Slate 900
    brand_blue = colors.HexColor("#0284c7")    # Sky 600
    text_dark = colors.HexColor("#1e293b")     # Slate 800
    muted_text = colors.HexColor("#64748b")    # Slate 500
    border_color = colors.HexColor("#cbd5e1")  # Slate 300
    table_header_bg = colors.HexColor("#0f172a")
    alt_row_bg = colors.HexColor("#f8fafc")
    
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=primary_color
    )
    
    subtitle_style = ParagraphStyle(
        'DocSubTitle',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=10,
        leading=14,
        textColor=brand_blue
    )
    
    h1_style = ParagraphStyle(
        'SectionH1',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=primary_color,
        spaceBefore=12,
        spaceAfter=6
    )
    
    body_style = ParagraphStyle(
        'BodyDark',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=text_dark
    )
    
    cell_bold = ParagraphStyle(
        'CellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=11,
        textColor=text_dark
    )
    
    cell_normal = ParagraphStyle(
        'CellNormal',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=7.5,
        leading=10.5,
        textColor=text_dark
    )
    
    cell_header = ParagraphStyle(
        'CellHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8,
        leading=11,
        textColor=colors.white
    )
    
    story = []
    
    # Title Block
    story.append(Paragraph("DIGITALBUILDERS", subtitle_style))
    story.append(Paragraph(f"Architectural Price Book & Service Catalogue ({currency})", title_style))
    story.append(Paragraph(f"<b>Fiscal Year:</b> 2026–27 &nbsp;|&nbsp; <b>Lead Architect:</b> Ashish Gupta (ashishgupta.dev) &nbsp;|&nbsp; <b>Govt Reg:</b> UDYAM-PB-12-0217716", ParagraphStyle('Meta', parent=body_style, textColor=muted_text)))
    story.append(Spacer(1, 10))
    story.append(HRFlowable(width="100%", thickness=1.5, color=brand_blue, spaceBefore=2, spaceAfter=10))
    
    # Executive Summary
    summary_text = (
        "<b>About the Studio:</b> DigitalBuilders is a high-performance software architecture studio based in Ludhiana, Punjab, India. "
        "We engineer enterprise web applications, high-throughput SaaS platforms, mobile applications (iOS/Android), and autonomous AI agents for clients across India, North America, Europe, and the Middle East. "
        "Our core architecture focuses on sub-100ms API response times, domain-driven design (DDD), and zero-tech-debt engineering."
    )
    story.append(Paragraph(summary_text, body_style))
    story.append(Spacer(1, 10))
    
    # Core Services Table
    story.append(Paragraph("1. Core Engineering Price Book (Fixed Scope)", h1_style))
    
    headers = ["Service Category", "Tier", f"Price ({'INR' if is_inr else 'USD'})", "Timeline", "Key Deliverables"]
    table_data = [[Paragraph(f"<b>{h}</b>", cell_header) for h in headers]]
    
    services = [
        ("Digital Presence & Landing", "Launch (Starter)", "₹19,999" if is_inr else "$600", "5–7 Days", "Single landing page, interactive hero, direct WhatsApp/lead form, Local SEO, sub-0.5s PageSpeed."),
        ("Digital Presence & Landing", "Growth (Business)", "₹44,999" if is_inr else "$1,300", "2 Weeks", "Up to 5 custom pages, Admin CMS (Filament), catalog/matrix, email triggers, 30-day support."),
        ("Custom Web Application", "Launch", "₹79,000" if is_inr else "$3,500", "2–3 Weeks", "Core Reactive UI (Vue 3), robust REST API, Postgres/SQLite, standard Auth/RBAC, CI/CD deploy."),
        ("Custom Web Application", "Growth (Popular)", "₹1,49,000" if is_inr else "$6,500", "4–6 Weeks", "Domain-Driven modular monolith, Redis caching/queues, multi-role RBAC, WhatsApp/Email alerts, 30d warranty."),
        ("Custom Web Application", "Enterprise", "₹2,49,000" if is_inr else "$11,000", "8–10 Weeks", "High-throughput microservices, audit logging, HA Postgres, full automated test suite, load testing, SLA."),
        ("AI Voice & Chat Agents", "Launch", "₹99,000" if is_inr else "$4,500", "2–3 Weeks", "Custom LLM prompt engineering, web chat widget with streaming, lead CRM sync, human fallback."),
        ("AI Voice & Chat Agents", "Growth", "₹1,79,000" if is_inr else "$7,900", "4–5 Weeks", "Grounded RAG pipeline with pgvector embeddings, private knowledge base ingestion, token cost analytics."),
        ("Mobile Apps (iOS & Android)", "Launch", "₹1,19,000" if is_inr else "$6,000", "3–4 Weeks", "Flutter / React Native cross-platform app, core screens, push notifications, Play Store / App Store setup."),
        ("Mobile Apps (iOS & Android)", "Growth", "₹1,99,000" if is_inr else "$10,000", "6–8 Weeks", "Offline-first SQLite sync, biometric auth, in-app payments (Razorpay/Stripe), deep links, 30d warranty."),
        ("SaaS Platform Architecture", "Launch", "₹1,99,000" if is_inr else "$9,000", "4–6 Weeks", "Multi-tenant tenant isolation, recurring subscription billing, user invite workflow, tenant admin dashboard."),
        ("SaaS Platform Architecture", "Growth", "₹3,19,000" if is_inr else "$14,000", "8–10 Weeks", "Metered usage billing, organization teams/invites, webhook dispatchers, audit trails, priority queue workers."),
        ("Enterprise ERP & CRM", "Launch", "₹2,49,000" if is_inr else "$11,000", "6–8 Weeks", "Inventory ledger, customer & vendor master, order entry, GST invoice PDF generation, role permissions."),
        ("Enterprise ERP & CRM", "Growth", "₹3,79,000" if is_inr else "$16,500", "10–12 Weeks", "Multi-warehouse dispatch, credit limits, automated reconciliation, barcode scanning, custom executive dashboards."),
    ]
    
    for row in services:
        table_data.append([
            Paragraph(row[0], cell_bold),
            Paragraph(row[1], cell_normal),
            Paragraph(f"<b>{row[2]}</b>", cell_bold),
            Paragraph(row[3], cell_normal),
            Paragraph(row[4], cell_normal)
        ])
        
    col_widths = [110, 85, 65, 55, 225]
    t = Table(table_data, colWidths=col_widths, repeatRows=1)
    t.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), table_header_bg),
        ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 4),
        ('TOPPADDING', (0, 0), (-1, -1), 4),
        ('GRID', (0, 0), (-1, -1), 0.5, border_color),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, alt_row_bg]),
    ]))
    story.append(t)
    story.append(Spacer(1, 12))
    
    # Modular Add-ons & Maintenance (Page 2)
    story.append(Paragraph("2. Modular Add-on Capabilities & AMC Care Plans", h1_style))
    
    addons_headers = ["Add-on Capability", f"Investment ({'INR' if is_inr else 'USD'})", "Turnaround", "Description"]
    addons_data = [[Paragraph(f"<b>{h}</b>", cell_header) for h in addons_headers]]
    
    addons = [
        ("Payment & Subscription Engine", "₹19,000" if is_inr else "$350", "+3 Days", "Razorpay / Stripe recurring card subscriptions, auto-invoicing, refund webhooks."),
        ("Real-time WebSockets & Live Sync", "₹25,000" if is_inr else "$450", "+4 Days", "Pusher / Soketi live notifications, sub-50ms chat / collaborative state sync."),
        ("AI Copilot / LLM Workflow", "₹35,000" if is_inr else "$650", "+5 Days", "OpenAI / Claude streaming integration, token budget guardrails, fallback routing."),
        ("Multi-language Localization (i18n)", "₹15,000" if is_inr else "$300", "+3 Days", "Full client/server translation dictionary, RTL support, URL prefix routing."),
        ("Meta WhatsApp Cloud API Catalog", "₹12,000" if is_inr else "$250", "+2 Days", "Direct interactive WhatsApp product catalogue and automated order booking."),
        ("Legacy Database & User Migration", "₹15,000" if is_inr else "$300", "+3 Days", "Sanitization scripts, zero-loss password hash migration, automated data validation."),
    ]
    for row in addons:
        addons_data.append([
            Paragraph(row[0], cell_bold),
            Paragraph(f"<b>{row[1]}</b>", cell_bold),
            Paragraph(row[2], cell_normal),
            Paragraph(row[3], cell_normal)
        ])
    
    t_addons = Table(addons_data, colWidths=[130, 80, 60, 270], repeatRows=1)
    t_addons.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), table_header_bg),
        ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3.5),
        ('TOPPADDING', (0, 0), (-1, -1), 3.5),
        ('GRID', (0, 0), (-1, -1), 0.5, border_color),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, alt_row_bg]),
    ]))
    story.append(t_addons)
    story.append(Spacer(1, 10))
    
    # AMC Care Plans
    care_headers = ["Care / Maintenance Tier", f"Annual Fee ({'INR' if is_inr else 'USD'})", "SLA Response", "Scope & Coverage"]
    care_data = [[Paragraph(f"<b>{h}</b>", cell_header) for h in care_headers]]
    
    care_tiers = [
        ("Basic Care", "₹19,000 / yr" if is_inr else "$1,200 / yr", "48 Hours", "Monthly server & security patches, automated daily DB backups, SSL/DNS renewal."),
        ("Business Care (Recommended)", "₹49,000 / yr" if is_inr else "$3,500 / yr", "12 Hours", "Everything in Basic + 8-10h monthly feature updates, 99.9% uptime monitor. (Prepay 10mo = 12mo)."),
        ("Enterprise SLA", "From ₹1,49,000 / yr" if is_inr else "From $8,500 / yr", "1 Hour (24/7)", "Dedicated named Staff Engineer, 24/7 incident on-call, custom DR drills, architecture audits."),
    ]
    for row in care_tiers:
        care_data.append([
            Paragraph(row[0], cell_bold),
            Paragraph(f"<b>{row[1]}</b>", cell_bold),
            Paragraph(row[2], cell_normal),
            Paragraph(row[3], cell_normal)
        ])
    
    t_care = Table(care_data, colWidths=[130, 95, 65, 250], repeatRows=1)
    t_care.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, 0), table_header_bg),
        ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
        ('VALIGN', (0, 0), (-1, -1), 'TOP'),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 3.5),
        ('TOPPADDING', (0, 0), (-1, -1), 3.5),
        ('GRID', (0, 0), (-1, -1), 0.5, border_color),
        ('ROWBACKGROUNDS', (0, 1), (-1, -1), [colors.white, alt_row_bg]),
    ]))
    story.append(t_care)
    story.append(Spacer(1, 10))
    
    # Commercial Terms & Guarantees
    story.append(Paragraph("3. Architectural Discovery, Commercial Terms & Code Ownership", h1_style))
    
    terms_text = (
        "• <b>Architecture Discovery Sprint:</b> " + ("₹19,000 INR" if is_inr else "$750 USD") + " (1-week deep dive delivering DB schema, API design, and cloud architecture blueprint; <b>100% credited</b> toward your build contract).<br/>"
        "• <b>Commercial Milestone Schedule:</b> 40% Advance Kickoff, 40% Staging Demo Review & Signoff, 20% Production Handover.<br/>"
        "• <b>Free Mutual NDA:</b> We execute a bilateral NDA before reviewing any proprietary business logic or codebases.<br/>"
        "• <b>100% IP & Code Ownership:</b> All intellectual property, source code, designs, and database schemas are transferred to you upon milestone settlement.<br/>"
        "• <b>30-Day Post-Launch Bug Warranty:</b> Complimentary bug fixes and stabilization included with every production deployment.<br/>"
        "• <b>Invoicing & Tax:</b> GST Invoicing (18% ITC compliant for India) & International Wire / Stripe (zero withholding tax)."
    )
    story.append(Paragraph(terms_text, body_style))
    story.append(Spacer(1, 12))
    
    # Contact CTA Box
    cta_data = [[
        Paragraph(
            "<b>Ready to Kick Off? Schedule a 30-Minute Discovery Session</b><br/>"
            "• Direct WhatsApp / Hotline: <b>+91 90870 21592</b> &nbsp;|&nbsp; • Email: <b>hello@digitalbuilders.in</b><br/>"
            "• Instant Calendar Booking: <b>https://digitalbuilders.in/book</b> &nbsp;|&nbsp; • Scope Estimator: <b>https://digitalbuilders.in/estimator</b>",
            ParagraphStyle('CTABox', parent=body_style, textColor=colors.HexColor("#0369a1"))
        )
    ]]
    t_cta = Table(cta_data, colWidths=[540])
    t_cta.setStyle(TableStyle([
        ('BACKGROUND', (0, 0), (-1, -1), colors.HexColor("#f0f9ff")),
        ('BOX', (0, 0), (-1, -1), 1, colors.HexColor("#38bdf8")),
        ('TOPPADDING', (0, 0), (-1, -1), 8),
        ('BOTTOMPADDING', (0, 0), (-1, -1), 8),
        ('LEFTPADDING', (0, 0), (-1, -1), 12),
        ('RIGHTPADDING', (0, 0), (-1, -1), 12),
    ]))
    story.append(t_cta)
    
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"[SUCCESS] Built PDF: {filename} ({os.path.getsize(filename)} bytes)")

if __name__ == '__main__':
    downloads_dir = os.path.join(os.path.dirname(__file__), "..", "public", "downloads")
    os.makedirs(downloads_dir, exist_ok=True)
    
    inr_pdf = os.path.join(downloads_dir, "digitalbuilders-pricing-india-inr.pdf")
    usd_pdf = os.path.join(downloads_dir, "digitalbuilders-pricing-international-usd.pdf")
    
    build_pdf(inr_pdf, region="INR")
    build_pdf(usd_pdf, region="USD")
