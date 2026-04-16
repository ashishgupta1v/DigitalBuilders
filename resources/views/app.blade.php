<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="DigitalBuilders builds enterprise web apps, mobile apps, and AI systems engineered for scale.">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ config('app.name', 'DigitalBuilders') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'ProfessionalService',
                'name' => 'DigitalBuilders',
                'url' => rtrim(config('app.url', 'https://www.digitalbuilders.in'), '/').'/'.ltrim(request()->path() === '/' ? '' : request()->path(), '/'),
                'image' => asset('brand/db-logo.svg'),
                'description' => 'DigitalBuilders delivers enterprise-grade web applications, mobile apps, and AI solutions.',
                'areaServed' => 'IN',
                'address' => [
                    '@type' => 'PostalAddress',
                    'addressLocality' => 'Ludhiana',
                    'addressRegion' => 'Punjab',
                    'addressCountry' => 'IN',
                ],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'WebSite',
                'name' => 'DigitalBuilders',
                'url' => rtrim(config('app.url', 'https://www.digitalbuilders.in'), '/'),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/svg+xml" href="{{ asset('brand/db-logo.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.ts', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
