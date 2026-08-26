@extends('layouts.legal')

@section('title', 'Privacy Policy — DigitalBuilders')

@section('content')
<h1>Privacy Policy</h1>
<p class="last-updated">Last updated: {{ date('F j, Y') }}</p>

<p>DigitalBuilders ("we", "us", or "our") operates the website <strong>www.digitalbuilders.in</strong>. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.</p>

<h2>1. Information We Collect</h2>

<h3>Personal Information</h3>
<p>We may collect the following personal information when you interact with our website:</p>
<ul>
    <li><strong>Contact Information:</strong> Name, email address, phone number</li>
    <li><strong>Project Details:</strong> Project type, description, and business requirements</li>
    <li><strong>Account Information:</strong> Name, email, and password (if you create an account)</li>
    <li><strong>Google OAuth Data:</strong> Name, email, and profile picture (if you sign in with Google)</li>
</ul>

<h3>Automatically Collected Information</h3>
<ul>
    <li>IP address and browser type</li>
    <li>Pages visited and time spent on pages</li>
    <li>Referring URL and device information</li>
</ul>

<h2>2. How We Use Your Information</h2>
<p>We use the information we collect to:</p>
<ul>
    <li>Respond to your project inquiries within 24 business hours</li>
    <li>Provide, maintain, and improve our services</li>
    <li>Communicate with you about projects and services</li>
    <li>Authenticate your account and maintain security</li>
    <li>Comply with legal obligations</li>
</ul>

<h2>3. Information Sharing</h2>
<p>We do <strong>not</strong> sell, rent, or trade your personal information. We may share information only in the following circumstances:</p>
<ul>
    <li><strong>Service providers:</strong> Email delivery, hosting, and analytics services that assist our operations</li>
    <li><strong>Legal requirements:</strong> When required by law or to protect our rights</li>
    <li><strong>Business transfers:</strong> In connection with a merger or acquisition</li>
</ul>

<h2>4. Data Security & Encryption</h2>
<p>We implement defense-in-depth security measures to safeguard all client and inquiry data:</p>
<ul>
    <li><strong>Data in Transit:</strong> End-to-end encrypted transmission via TLS 1.3 / HTTPS with strict HSTS (Strict-Transport-Security: max-age=63072000).</li>
    <li><strong>Data at Rest:</strong> Database records and backups are encrypted at rest using AES-256 standards.</li>
    <li><strong>Password Security:</strong> High-entropy cryptographic hashing using modern bcrypt with strict work factors.</li>
    <li><strong>Strict Access Control:</strong> Principle of least privilege with mandatory role-based authentication and token revocation.</li>
</ul>

<h2>5. Data Retention & Deletion Schedule</h2>
<p>We adhere to strict data minimization principles:</p>
<ul>
    <li><strong>Discovery Inquiries & Estimates:</strong> Retained for 24 months to support ongoing engineering scoping, after which they are securely purged or anonymized.</li>
    <li><strong>Active Client Contracts & Invoices:</strong> Retained for statutory compliance and accounting periods as required by Indian taxation and corporate regulations.</li>
</ul>

<h2>6. Your Rights & Data Subject Access Requests (DSAR)</h2>
<p>Under <strong>India's Digital Personal Data Protection (DPDP) Act 2023</strong> and the <strong>EU General Data Protection Regulation (GDPR)</strong>, you have the right to:</p>
<ul>
    <li><strong>Access & Portability:</strong> Request a complete machine-readable copy (JSON/CSV) of your personal data.</li>
    <li><strong>Correction & Rectification:</strong> Request correction of inaccurate or incomplete records.</li>
    <li><strong>Erasure & Deletion:</strong> Request immediate deletion of your contact records (Right to be Forgotten).</li>
    <li><strong>Withdrawal of Consent:</strong> Revoke consent for non-essential cookies and analytics at any time.</li>
</ul>
<p>To submit a DSAR request, email us directly at <a href="mailto:hello@digitalbuilders.in">hello@digitalbuilders.in</a> with the subject line <code>DSAR Request - [Your Name]</code>. We process all verified requests within 30 calendar days at no charge.</p>

<h2>7. Cookies & Consent Management</h2>
<p>We use essential cookies strictly required for security, routing, and CSRF token protection. Non-essential performance and analytics telemetry are strictly consent-gated and can be adjusted anytime via our on-site cookie manager.</p>

<h2>8. Third-Party Services</h2>
<p>Our website may use the following third-party services:</p>
<ul>
    <li><strong>Google OAuth:</strong> For authentication (governed by <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Google's Privacy Policy</a>)</li>
    <li><strong>Vercel:</strong> For hosting (governed by <a href="https://vercel.com/legal/privacy-policy" target="_blank" rel="noopener">Vercel's Privacy Policy</a>)</li>
</ul>

<h2>9. Children's Privacy</h2>
<p>Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from children.</p>

<h2>10. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. Changes will be posted on this page with an updated "Last updated" date.</p>

<h2>11. Contact Us</h2>
<p>If you have questions about this Privacy Policy, contact us at:</p>
<ul>
    <li><strong>Email:</strong> <a href="mailto:hello@digitalbuilders.in">hello@digitalbuilders.in</a></li>
    <li><strong>Phone:</strong> +91 90870 21592</li>
    <li><strong>Location:</strong> Ludhiana, Punjab, India</li>
</ul>
@endsection
