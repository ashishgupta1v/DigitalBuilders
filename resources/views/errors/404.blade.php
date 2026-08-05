<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — Page Not Found | DigitalBuilders</title>
    <link rel="icon" type="image/svg+xml" href="/brand/db-logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(1100px 600px at -10% -10%, rgba(122, 196, 255, 0.2), transparent 60%),
                radial-gradient(900px 540px at 110% 10%, rgba(197, 147, 255, 0.22), transparent 58%),
                linear-gradient(180deg, #1f2b3b 0%, #1b2736 52%, #1c2a3a 100%);
            color: #e7efff;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
            padding: 2rem;
            text-align: center;
        }

        .error-code {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(5rem, 15vw, 10rem);
            font-weight: 800;
            background: linear-gradient(92deg, #7ac4ff 0%, #9ba7ff 48%, #c593ff 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            line-height: 1;
        }

        h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 1rem;
            color: #ffffff;
        }

        p {
            margin-top: 1rem;
            color: #b4c3de;
            max-width: 28rem;
            line-height: 1.6;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 2rem;
            padding: 0.75rem 1.75rem;
            border-radius: 999px;
            border: 1px solid rgba(184, 201, 230, 0.25);
            background: linear-gradient(95deg, #7ac4ff 0%, #9ba7ff 48%, #c593ff 100%);
            color: #1a2231;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            transition: filter 0.2s, transform 0.2s;
        }

        .back-link:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .footer-text {
            position: fixed;
            bottom: 1.5rem;
            color: #5a7494;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="error-code">404</div>
    <h1>Page Not Found</h1>
    <p>The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
    <a href="/" class="back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Home
    </a>
    <p class="footer-text">&copy; {{ date('Y') }} DigitalBuilders</p>
</body>
</html>
