<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiry Confirmation — DigitalBuilders</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b0f19;
            color: #e2e8f0;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #111827;
            border: 1px solid #1e293b;
            border-radius: 12px;
            padding: 32px;
        }
        .header {
            border-bottom: 1px solid #1e293b;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .logo {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #38bdf8;
        }
        h1 {
            font-size: 22px;
            color: #f8fafc;
            margin: 0 0 16px 0;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #94a3b8;
            margin: 0 0 16px 0;
        }
        .details-box {
            background-color: #0f172a;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
        }
        .details-row {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .details-row:last-child {
            margin-bottom: 0;
        }
        .label {
            color: #64748b;
            font-weight: 600;
        }
        .val {
            color: #f1f5f9;
        }
        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #4f46e5 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 9999px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 12px;
        }
        .footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #1e293b;
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">DigitalBuilders</div>
        </div>

        <h1>Hi {{ $name }}, we received your inquiry</h1>
        <p>Thank you for reaching out to DigitalBuilders. Our lead architect, <strong>Ashish Gupta</strong>, will personally review your project requirements and get back to you within <strong>24 business hours</strong>.</p>

        <div class="details-box">
            <div class="details-row">
                <span class="label">Scope / Project Type:</span>
                <span class="val">{{ $projectType }}</span>
            </div>
            @if(!empty($description))
            <div class="details-row">
                <span class="label">Requirements Note:</span>
                <span class="val">{{ $description }}</span>
            </div>
            @endif
            <div class="details-row">
                <span class="label">Guaranteed SLA:</span>
                <span class="val">Initial response within 24 business hours</span>
            </div>
        </div>

        <p>If your project is urgent or you prefer direct communication, you can reach Ashish directly on WhatsApp or review our rate card:</p>

        <a href="https://wa.me/919087021592?text=Hi%20Ashish,%20I%20just%20submitted%20a%20project%20inquiry%20via%20DigitalBuilders." class="cta-btn">
            Chat Directly on WhatsApp &rarr;
        </a>

        <div class="footer">
            <p>DigitalBuilders · Enterprise Web, Mobile & AI Architecture<br>
            Website: <a href="https://www.digitalbuilders.in" style="color: #38bdf8; text-decoration: none;">www.digitalbuilders.in</a> · Email: hello@digitalbuilders.in</p>
        </div>
    </div>
</body>
</html>
