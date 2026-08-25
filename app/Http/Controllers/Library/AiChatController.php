<?php

declare(strict_types=1);

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatController extends Controller
{
    private const SYSTEM_PROMPT = <<<EOT
You are DigitalBuilders AI, a Senior Software Architect and Technical Consultant for DigitalBuilders, founded and led by Lead Digital Architect Ashish Gupta (https://ashishgupta.dev).
DigitalBuilders is a high-performance software architecture studio based in Ludhiana, Punjab, India, engineering enterprise web applications, high-throughput SaaS platforms, mobile apps, and autonomous AI agents for clients globally.

Studio Core Values & Architecture Philosophy:
- We build custom, domain-driven modular monoliths and resilient cloud microservices that eliminate tech debt and deliver sub-100ms API response times.
- Technology Stack: Laravel 13, Vue 3, Inertia.js, TypeScript, PostgreSQL (Neon), Redis, Python (FastAPI/LangChain), Flutter, React Native, Tailwind CSS, Docker.

Comprehensive Live Portfolio Knowledge:
1. Habuilt (https://www.habuilt.com/): High-scale wellness & habit-tracking platform. 50 habits, 4 progression tiers, streak mechanics, mobile deep link auth. Result: 70% latency drop, 99.99% uptime.
2. Dhanda Diary (https://dhandadiary.cloud/): Execution Cockpit SaaS. Automated Daily Compliance Reports (DCR), streak multipliers, ApexCharts KPI telemetry, Kanban boards, and Web Push notifications (<50ms sync).
3. ZoetiCoach AI (https://zoeticoach.com/): WhatsApp-first B2B2C coaching accountability SaaS. OpenAI RAG agent & pgvector embeddings for automated habit verification, reducing client churn by 65%.
4. GutTalks (https://guttalks.in/): Telehealth portal & clinic. Root Rx doctor consultations (₹499), GutMap Complete™ at-home microbiome test kit tracking for 10,000+ patients with 4.8★ rating.
5. MyAstrova (https://myastrova.com/): Vedic AstroTech computational engine. Sub-200ms mathematical Kundli & horoscope generation, live 1-on-1 astrologer call/chat routing, and energized crystal e-commerce.
6. Krishan Balram Gaushala (https://krishanbalramgaushala.com/): GauSeva Connect platform. Automated devotee birthday/anniversary blessings via Meta WhatsApp Cloud API and instant 80G tax exemption PDF generation.
7. Ashish Gupta Architecture Hub (https://ashishgupta.dev/): Lead Architect portfolio demonstrating Domain-Driven Design (DDD), legacy modernization, live telemetry, and $1M+ cloud savings.
8. SportsEntertainmentClub: Cross-platform iOS/Android mobile app. Atomic slot locking eliminating court reservation collisions, digital QR membership passes, and live tournament leaderboards.
9. Garg Enterprises: Rugged B2B Android ordering app. Offline SQLite draft purchase orders with background sync, dealer credit ledger, and 1-tap GST invoice downloads for 10,000+ SKUs.

Services, Ballpark Timelines & Pricing:
- Custom Web Applications: 3-6 weeks, starting at ₹1,25,000 INR ($1,500 USD).
- B2B SaaS Platforms & Custom ERP/CRM: 4-8 weeks, starting at ₹2,50,000 - ₹5,00,000 INR ($3,000 - $6,000 USD).
- Mobile Apps (iOS + Android): 4-7 weeks, starting at ₹2,00,000 INR ($2,500 USD).
- Autonomous AI Agents & Voice RAG: 2-4 weeks, starting at ₹1,50,000 INR ($1,800 USD).
- Interactive Scope Estimator: Available on /estimator.

Contact & Booking:
- Email: hello@digitalbuilders.in
- Direct WhatsApp / Phone: +91 90870 21592
- Location: Ludhiana, Punjab, India (serving global clients across US, UK, India, and Middle East).

Interaction Guidelines:
- Act as an authoritative, helpful, and technically sharp software architect.
- Offer actionable architectural advice. Keep responses engaging, concise (under 140 words), and encourage booking a Discovery Call or trying the /estimator.
EOT;

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $message = trim((string) $request->input('message'));
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        // If OpenAI key is available, call OpenAI API
        if ($apiKey) {
            try {
                $messages = [
                    ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
                ];

                if ($request->filled('history')) {
                    foreach ($request->input('history') as $item) {
                        if (isset($item['sender'], $item['text'])) {
                            $messages[] = [
                                'role' => $item['sender'] === 'user' ? 'user' : 'assistant',
                                'content' => $item['text'],
                            ];
                        }
                    }
                }

                $messages[] = [
                    'role' => 'user',
                    'content' => $message,
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(12)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 350,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $reply = $data['choices'][0]['message']['content'] ?? null;
                    if (!empty($reply)) {
                        return response()->json(['response' => trim($reply)]);
                    }
                }

                Log::warning('OpenAI API request returned non-200 or empty choices: ' . $response->body());
            } catch (\Throwable $e) {
                Log::warning('OpenAI API call exception: ' . $e->getMessage());
            }
        }

        // Context-aware fallback response generator
        $smartReply = $this->generateContextualFallback($message);

        return response()->json([
            'response' => $smartReply,
        ]);
    }

    /**
     * Generate an intelligent domain-specific architectural response when OpenAI is offline or without key.
     */
    private function generateContextualFallback(string $query): string
    {
        $q = strtolower($query);

        // Blockchain, Rust, Go, microservices, specialized tech
        if (preg_match('/\b(blockchain|rust|go|golang|solidity|web3|crypto|smart contract|microservice|microservices)\b/i', $query)) {
            return "Yes! While our primary core stack is a Domain-Driven modular monolith (Laravel 13 + Vue 3 + Inertia + PostgreSQL), we regularly engineer high-throughput microservices and cryptographic modules in Rust and Go for specialized compute tasks, sub-millisecond data pipelines, and smart contract integrations. Let's discuss your architectural requirements!";
        }

        // Mobile apps
        if (preg_match('/\b(mobile|android|ios|flutter|react native|app cost|phone app)\b/i', $query)) {
            return "Our mobile apps (iOS & Android) are engineered using Flutter or React Native with 60 FPS fluid performance and offline-first SQLite synchronization. Typical timeline is 4–7 weeks, starting around ₹2,00,000 ($2,500 USD). Check out our case studies for SportsEntertainmentClub (court slot locking) and Garg Enterprises (offline B2B wholesale ordering)!";
        }

        // Pricing, cost, estimate
        if (preg_match('/\b(cost|price|pricing|rates|how much|quote|budget|estimate|investment)\b/i', $query)) {
            return "Our project investment tiers typically range as follows:\n• Custom Web Apps / MVPs: ₹1,25,000 – ₹2,50,000 ($1,500 – $3,000)\n• High-Scale SaaS / Custom ERP: ₹2,50,000 – ₹5,00,000+ ($3,000 – $6,000+)\n• Mobile Apps: ₹2,00,000 – ₹3,50,000 ($2,500 – $4,200)\n• AI Voice/Chat Workflows: ₹1,50,000 – ₹3,00,000 ($1,800 – $3,500)\n\nYou can use our interactive /estimator for an exact tailored scope!";
        }

        // Timeline, how long
        if (preg_match('/\b(timeline|how long|duration|timeframe|weeks|months|schedule)\b/i', $query)) {
            return "We deliver in rapid weekly sprints with live demos. Standard web applications take 3–6 weeks; mobile apps take 4–7 weeks; and enterprise SaaS/ERP platforms take 4–8 weeks. Every project includes a 30-day post-launch bug warranty.";
        }

        // AI, chatbot, voice agents, LLM, RAG
        if (preg_match('/\b(ai|agent|agents|chatbot|rag|openai|gpt|voice|llm|machine learning)\b/i', $query)) {
            return "We engineer autonomous AI agents, voice integrations, and grounded RAG pipelines with pgvector embeddings. In our ZoetiCoach AI case study, we built an automated WhatsApp habit verification agent that reduced client dropout by 65%. We can integrate custom AI pipelines into your stack in 2–4 weeks.";
        }

        // Tech stack, framework, Laravel, Vue, React
        if (preg_match('/\b(stack|technology|tech|laravel|vue|react|next|inertia|postgres|postgresql|database|redis)\b/i', $query)) {
            return "Our battle-tested stack focuses on high developer velocity and sub-100ms response times: Laravel 13, Vue 3, Inertia.js, TypeScript, PostgreSQL (Neon), Redis, Tailwind CSS, Python (FastAPI/LangChain), and Flutter/React Native for mobile.";
        }

        // Founder, Ashish, team, experience
        if (preg_match('/\b(founder|ashish|gupta|team|experience|who are you|background)\b/i', $query)) {
            return "DigitalBuilders is led by Ashish Gupta, a Senior Digital Architect with 8+ years of experience engineering enterprise software, legacy healthcare/aviation modernization, and high-throughput cloud platforms with over $1M/yr cloud cost reductions. View his portfolio at https://ashishgupta.dev.";
        }

        // Case studies, past work, portfolio, clients
        if (preg_match('/\b(case study|case studies|portfolio|past work|clients|examples|projects)\b/i', $query)) {
            return "We have 9 production case studies live on our site, including Habuilt (high-traffic habit SaaS), Dhanda Diary (execution cockpit SaaS), ZoetiCoach AI (WhatsApp coaching ERP), GutTalks (telehealth clinic), and MyAstrova (Vedic AstroTech). Explore all in our /portfolio section!";
        }

        // Contact, call, whatsapp, phone, email
        if (preg_match('/\b(contact|call|whatsapp|phone|email|talk|reach|meeting|hire)\b/i', $query)) {
            return "You can reach Lead Architect Ashish Gupta directly on WhatsApp/Phone at +91 90870 21592 or by email at hello@digitalbuilders.in. You can also submit the discovery form on our homepage for a detailed proposal within 24 hours.";
        }

        // Default greeting / general inquiry
        return "Hello! I am the DigitalBuilders AI Assistant. We engineer enterprise-grade web applications, mobile apps, SaaS platforms, and autonomous AI agents with sub-100ms response times. Would you like to estimate your project scope on our /estimator or schedule a discovery call with our Lead Architect?";
    }
}
