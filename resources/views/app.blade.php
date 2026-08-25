<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $manifestPath = public_path('build/manifest.json');
        $hasLocalManifest = file_exists($manifestPath);
        $hasViteHot = file_exists(public_path('hot'));

        $assetMode = ($hasViteHot || $hasLocalManifest) ? 'local' : 'none';
        $hasAssets = $assetMode === 'local';
    @endphp
    @php
        $currentMetaTitle = $pageMeta['title'] ?? 'DigitalBuilders — Enterprise Web, Mobile and AI Architecture';
        $currentMetaDesc = $pageMeta['description'] ?? 'DigitalBuilders delivers enterprise-grade web applications, mobile apps, and AI solutions engineered for scale.';
        $currentMetaImage = $pageMeta['image'] ?? 'https://www.digitalbuilders.in/images/db-logo.png';
        $currentMetaType = $pageMeta['type'] ?? 'website';
        $currentMetaUrl = $pageMeta['url'] ?? url()->current();
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $currentMetaDesc }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $currentMetaUrl }}">
        <meta property="og:site_name" content="DigitalBuilders">
        <meta property="og:type" content="{{ $currentMetaType }}">
        <meta property="og:title" content="{{ $currentMetaTitle }}">
        <meta property="og:description" content="{{ $currentMetaDesc }}">
        <meta property="og:url" content="{{ $currentMetaUrl }}">
        <meta property="og:image" content="{{ $currentMetaImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $currentMetaTitle }}">
        <meta name="twitter:description" content="{{ $currentMetaDesc }}">
        <meta name="twitter:image" content="{{ $currentMetaImage }}">
        @verbatim
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "ProfessionalService",
                "name": "DigitalBuilders",
                "url": "https://www.digitalbuilders.in/",
                "logo": "https://www.digitalbuilders.in/images/db-logo.png",
                "image": "https://www.digitalbuilders.in/images/portfolio/habuilt.jpg",
                "description": "DigitalBuilders delivers enterprise-grade web applications, high-throughput SaaS platforms, mobile apps, and autonomous AI agents.",
                "founder": {
                    "@type": "Person",
                    "name": "Ashish Gupta",
                    "url": "https://ashishgupta.dev"
                },
                "telephone": "+919087021592",
                "email": "hello@digitalbuilders.in",
                "areaServed": ["IN", "US", "GB", "AE", "CA", "SG"],
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Ludhiana",
                    "addressRegion": "Punjab",
                    "addressCountry": "IN"
                },
                "priceRange": "$$"
            }
        </script>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "WebSite",
                "name": "DigitalBuilders",
                "url": "https://www.digitalbuilders.in/"
            }
        </script>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "ItemList",
                "itemListElement": [
                    {
                        "@type": "SoftwareApplication",
                        "position": 1,
                        "name": "Habuilt Wellness & Habit Platform",
                        "url": "https://www.digitalbuilders.in/portfolio/habuilt",
                        "applicationCategory": "HealthApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 2,
                        "name": "Dhanda Diary Execution Cockpit SaaS",
                        "url": "https://www.digitalbuilders.in/portfolio/dhandadiary",
                        "applicationCategory": "BusinessApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 3,
                        "name": "ZoetiCoach AI WhatsApp Coaching ERP",
                        "url": "https://www.digitalbuilders.in/portfolio/zoeticoach",
                        "applicationCategory": "BusinessApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 4,
                        "name": "GutTalks Telehealth & Microbiome Portal",
                        "url": "https://www.digitalbuilders.in/portfolio/guttalks",
                        "applicationCategory": "HealthApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 5,
                        "name": "MyAstrova Vedic AstroTech Computation Engine",
                        "url": "https://www.digitalbuilders.in/portfolio/myastrova",
                        "applicationCategory": "LifestyleApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 6,
                        "name": "Krishan Balram Gaushala GauSeva Connect",
                        "url": "https://www.digitalbuilders.in/portfolio/gaushala",
                        "applicationCategory": "BusinessApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 7,
                        "name": "SportsEntertainmentClub Mobile Booking App",
                        "url": "https://www.digitalbuilders.in/portfolio/sports-club",
                        "applicationCategory": "SportsApplication"
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 8,
                        "name": "Garg Enterprises B2B Wholesale Ordering App",
                        "url": "https://www.digitalbuilders.in/portfolio/garg-enterprises",
                        "applicationCategory": "BusinessApplication"
                    }
                ]
            }
        </script>
        @endverbatim

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/db-logo.png') }}">
        <link rel="alternate icon" href="{{ asset('favicon.png') }}">

        <!-- Google Fonts: Libre Baskerville (Headings & Display), Outfit (Body & UI), JetBrains Mono (Prices & Data) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:ital,wght@0,400..800;1,400..800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @routes
        @if ($assetMode === 'local')
            @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @endif
        <script>
            (function() {
                var theme = localStorage.getItem('db-theme');
                if (theme === 'light') {
                    document.documentElement.setAttribute('data-theme', 'light');
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                }
            })();
        </script>
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @if ($hasAssets)
            @inertia
        @else
            <main style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:#0f172a;color:#e2e8f0;font-family:Inter,Segoe UI,Arial,sans-serif;">
                <section style="max-width:640px;border:1px solid rgba(148,163,184,.35);border-radius:16px;padding:24px;background:rgba(15,23,42,.92);">
                    <h1 style="margin:0 0 12px;font-size:24px;line-height:1.2;">DigitalBuilders is updating</h1>
                    <p style="margin:0 0 10px;line-height:1.6;">A deployment finished without frontend assets, so the application switched to safe mode to avoid server errors.</p>
                    <p style="margin:0;line-height:1.6;">Please redeploy with prebuilt assets (npm run build + vercel deploy --prebuilt) or use the configured CI workflow.</p>
                </section>
            </main>
        @endif
    </body>
</html>
