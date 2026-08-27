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
        <meta name="theme-color" content="#0b0f19">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $currentMetaDesc }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $currentMetaUrl }}">
        <link rel="alternate" type="application/rss+xml" title="DigitalBuilders Engineering & Architecture Insights" href="{{ url('/feed.xml') }}">
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
        <meta name="geo.region" content="IN-PB">
        <meta name="geo.placename" content="Ludhiana">
        <meta name="geo.position" content="30.9010;75.8573">
        <meta name="ICBM" content="30.9010, 75.8573">

        @php
            $gaMeasurementId = config('services.ga_measurement_id', env('GA_MEASUREMENT_ID', ''));
        @endphp
        <script>
            window.GA_MEASUREMENT_ID = @json($gaMeasurementId);
        </script>
        @verbatim
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "ProfessionalService",
                "name": "DigitalBuilders",
                "alternateName": "Digital Builders Enterprise Software Architecture",
                "url": "https://www.digitalbuilders.in/",
                "logo": "https://www.digitalbuilders.in/images/db-logo.png",
                "image": "https://www.digitalbuilders.in/images/portfolio/habuilt.jpg",
                "description": "DigitalBuilders delivers enterprise-grade web applications, high-throughput SaaS platforms, mobile apps, and autonomous AI agents with Silicon Valley engineering discipline.",
                "founder": {
                    "@type": "Person",
                    "name": "Ashish Gupta",
                    "jobTitle": "Lead Digital Architect & Founder",
                    "url": "https://ashishgupta.dev",
                    "sameAs": [
                        "https://www.linkedin.com/in/ashishgupta1v/",
                        "https://github.com/ashishgupta1v",
                        "https://ashishgupta.dev"
                    ]
                },
                "sameAs": [
                    "https://www.linkedin.com/in/ashishgupta1v/",
                    "https://github.com/ashishgupta1v"
                ],
                "telephone": "+919087021592",
                "email": "hello@digitalbuilders.in",
                "areaServed": ["IN", "US", "GB", "AE", "CA", "SG", "AU"],
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "Ludhiana",
                    "addressRegion": "Punjab",
                    "addressCountry": "IN"
                },
                "priceRange": "$$",
                "hasOfferCatalog": {
                    "@type": "OfferCatalog",
                    "name": "Software & AI Architecture Services 2026",
                    "itemListElement": [
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "Digital Presence & Funnel Architecture",
                                "description": "High-converting web presence with integrated WhatsApp capture and local SEO."
                            },
                            "price": "19999",
                            "priceCurrency": "INR"
                        },
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "Custom Web Application Development",
                                "description": "Modular Laravel + Vue 3 applications with domain-driven design and automated testing."
                            },
                            "price": "79000",
                            "priceCurrency": "INR"
                        },
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "Autonomous AI Voice & Chat Agents",
                                "description": "WhatsApp bots, WebRTC voice agents, and private RAG document pipelines."
                            },
                            "price": "99000",
                            "priceCurrency": "INR"
                        },
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "Cross-Platform Mobile App Development",
                                "description": "Flutter and React Native iOS & Android apps with offline sync and Razorpay/Stripe."
                            },
                            "price": "119000",
                            "priceCurrency": "INR"
                        },
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "High-Scale SaaS Platform Engineering",
                                "description": "Multi-tenant platforms with subscription engines, RBAC, and automated CI/CD."
                            },
                            "price": "199000",
                            "priceCurrency": "INR"
                        },
                        {
                            "@type": "Offer",
                            "itemOffered": {
                                "@type": "Service",
                                "name": "Enterprise ERP & CRM Architecture",
                                "description": "GST-compliant invoicing, multi-godown stock, Tally sync, and dealer portals."
                            },
                            "price": "249000",
                            "priceCurrency": "INR"
                        }
                    ]
                }
            }
        </script>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "HowTo",
                "name": "How DigitalBuilders Delivers Enterprise Software Architecture",
                "description": "A 4-step engineering lifecycle with zero boilerplate and weekly sprint demos.",
                "step": [
                    {
                        "@type": "HowToStep",
                        "position": 1,
                        "name": "Understand Your Needs",
                        "text": "Map your actual business workflows, identify pain points, and define a written fixed scope and quote."
                    },
                    {
                        "@type": "HowToStep",
                        "position": 2,
                        "name": "Plan the Right Solution",
                        "text": "Design domain models, module boundaries, database schemas, and integration points before writing code."
                    },
                    {
                        "@type": "HowToStep",
                        "position": 3,
                        "name": "Build and Deliver with Weekly Demos",
                        "text": "Execute in weekly sprints with working live software demos every 7 days."
                    },
                    {
                        "@type": "HowToStep",
                        "position": 4,
                        "name": "Launch, Warranty & Support",
                        "text": "Zero-downtime deployment, team training, full handover, and 30 days of post-launch SLA warranty."
                    }
                ]
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
                    },
                    {
                        "@type": "SoftwareApplication",
                        "position": 9,
                        "name": "Ashish Gupta Architecture Hub & VILT Showcase",
                        "url": "https://www.digitalbuilders.in/portfolio/ashishgupta",
                        "applicationCategory": "DeveloperApplication"
                    }
                ]
            }
        </script>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "FAQPage",
                "mainEntity": [
                    {
                        "@type": "Question",
                        "name": "How long does a typical web application project take?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Timelines vary by architectural scope. A standard web application typically takes 3–6 weeks. Enterprise-grade platforms with custom ERP/CRM integrations take 4–8 weeks. We break every project into weekly sprint milestones with live review demos from day one."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Do you work with startups, or only established companies?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "We work with both. For startups, we focus on building a scalable MVP that won't need to be rebuilt as you grow. For established companies, we focus on performance, security, and integrating with existing systems."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What makes DigitalBuilders different from other agencies?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "We operate with a Staff Engineer mindset — we design systems before writing a single line of code. Unlike most agencies, we don't use fragile templates or boilerplate. Every system we build is custom-engineered for your specific domain and scale requirements."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "Can you integrate AI into my existing application?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Yes. We specialize in practical AI integrations — not just chatbots. This includes AI voice agents, automated lead qualification, intelligent document processing, workflow automation, and custom LLM-powered features fitted into your existing stack."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "What happens after the project is launched?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "Every project includes a 30-day post-launch warranty where we fix any bugs at no charge. After that, we offer flexible retainer plans for ongoing feature development, monitoring, and performance optimization."
                        }
                    },
                    {
                        "@type": "Question",
                        "name": "How do you handle pricing and payment milestones?",
                        "acceptedAnswer": {
                            "@type": "Answer",
                            "text": "We use decoupled, fixed regional price books with transparent milestone disbursements (₹21k booking -> 40/30/30 milestones in India; 50/50 via Stripe/Wire internationally). No surprise invoices, no currency exchange markups. India builds start at ₹99,000 + GST; International builds start at $3,500 ($2,500 for Gulf)."
                        }
                    }
                ]
            }
        </script>
        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "AggregateRating",
                "itemReviewed": {
                    "@type": "ProfessionalService",
                    "name": "DigitalBuilders",
                    "url": "https://www.digitalbuilders.in/"
                },
                "ratingValue": "5.0",
                "bestRating": "5",
                "ratingCount": "8",
                "reviewCount": "8"
            }
        </script>
        @endverbatim

        <title inertia>{{ config('app.name', 'DigitalBuilders') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('images/db-logo.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('images/db-logo.png') }}">
        <link rel="alternate icon" href="{{ asset('favicon.png') }}">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

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
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.setAttribute('data-theme', 'light');
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }

                // Register service worker if supported
                if ('serviceWorker' in navigator && window.location.protocol === 'https:') {
                    window.addEventListener('load', function() {
                        navigator.serviceWorker.register('/sw.js').catch(function() {});
                    });
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
