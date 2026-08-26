<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <title>@yield('title', 'DigitalBuilders')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/db-logo.png') }}">
    <link rel="alternate icon" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            background: radial-gradient(1100px 600px at -10% -10%, rgba(56, 189, 248, 0.12), transparent 60%),
                        radial-gradient(900px 540px at 110% 10%, rgba(192, 132, 252, 0.12), transparent 58%),
                        linear-gradient(180deg, #0b0f19 0%, #0f172a 52%, #111827 100%);
            color: #f8fafc;
            font-family: 'Outfit', system-ui, sans-serif;
            font-weight: 300;
            line-height: 1.7;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        header .inner {
            max-width: 72rem;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.9rem 1.25rem;
        }

        header a.logo {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Outfit', sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-decoration: none;
            background: linear-gradient(135deg, #7dd3fc 0%, #a5b4fc 45%, #c084fc 80%, #f472b6 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        header a.logo img {
            height: 2.2rem;
            width: auto;
            filter: none;
        }

        header nav a {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        header nav a:hover { color: #ffffff; }

        main {
            max-width: 52rem;
            margin: 0 auto;
            padding: 3rem 1.25rem 5rem;
        }

        h1 {
            font-family: 'Libre Baskerville', Georgia, serif;
            font-size: clamp(2rem, 4.5vw, 3.5rem);
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        h2 {
            font-family: 'Libre Baskerville', Georgia, serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #7dd3fc;
            margin-top: 2.5rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        h3 {
            font-family: 'Libre Baskerville', Georgia, serif;
            font-size: 1.25rem;
            font-weight: 700;
            color: #c084fc;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
        }

        p { line-height: 1.75; margin-bottom: 1rem; color: #cbd5e1; }

        .last-updated {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 2rem;
        }

        ul, ol { padding-left: 1.5rem; margin-bottom: 1rem; }
        li { line-height: 1.7; color: #cbd5e1; margin-bottom: 0.35rem; }
        li strong { color: #f8fafc; }

        a { color: #7dd3fc; text-decoration: underline; text-underline-offset: 3px; }
        a:hover { color: #c084fc; }

        footer {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(11, 15, 25, 0.9);
            padding: 2rem 1.25rem;
            text-align: center;
        }

        footer .inner {
            max-width: 72rem;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        footer a { color: #7dd3fc; }

        @media (min-width: 640px) {
            footer .inner { flex-direction: row; justify-content: space-between; align-items: center; }
        }
    </style>
</head>
<body>
    <header>
        <div class="inner">
            <a href="/" class="logo">
                <img src="{{ asset('images/db-logo.png') }}" alt="DigitalBuilders Logo">
                <span>Digital Builders</span>
            </a>
            <nav style="display: flex; gap: 1.25rem;">
                <a href="/">Home</a>
                <a href="{{ route('library.privacy') }}">Privacy</a>
                <a href="{{ route('library.terms') }}">Terms</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="inner">
            <p>&copy; {{ date('Y') }} Balaji Enterprises (DigitalBuilders). All rights reserved.</p>
            <p>
                <a href="{{ route('library.privacy') }}">Privacy Policy</a> &middot;
                <a href="{{ route('library.terms') }}">Terms of Service</a>
            </p>
        </div>
    </footer>
</body>
</html>
