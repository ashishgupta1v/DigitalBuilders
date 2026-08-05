<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @php
        $manifestPath = public_path('build/manifest.json');
        $hasLocalManifest = file_exists($manifestPath);
        $hasViteHot = file_exists(public_path('hot'));

        $assetMode = ($hasViteHot || $hasLocalManifest) ? 'local' : 'none';
        $hasAssets = $assetMode === 'local';
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="DigitalBuilders delivers enterprise-grade web applications, mobile apps, and AI solutions engineered for scale.">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:site_name" content="DigitalBuilders">
        <meta property="og:type" content="website">
        <meta property="og:title" content="DigitalBuilders — Enterprise Web, Mobile and AI Architecture">
        <meta property="og:description" content="Custom software engineered with a Staff Engineer mindset to scale your business.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="https://www.digitalbuilders.in/images/db-logo.png">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="DigitalBuilders">
        <meta name="twitter:description" content="Enterprise-grade web, mobile, and AI architecture for ambitious businesses.">
        <meta name="twitter:image" content="https://www.digitalbuilders.in/images/db-logo.png">
        @verbatim
        <script type="application/ld+json">
            {"@context":"https://schema.org","@type":"ProfessionalService","name":"DigitalBuilders","url":"https://www.digitalbuilders.in/","image":"https://www.digitalbuilders.in/images/db-logo.png","description":"DigitalBuilders delivers enterprise-grade web applications, mobile apps, and AI solutions.","areaServed":"IN","address":{"@type":"PostalAddress","addressLocality":"Ludhiana","addressRegion":"Punjab","addressCountry":"IN"}}
        </script>
        <script type="application/ld+json">
            {"@context":"https://schema.org","@type":"WebSite","name":"DigitalBuilders","url":"https://www.digitalbuilders.in/"}
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
