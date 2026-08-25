<?php

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
- We build custom, modular monoliths and resilient cloud microservices that eliminate tech debt and deliver sub-100ms API response times.
- Technology Stack: Laravel 13, Vue 3, Inertia.js, TypeScript, PostgreSQL (Neon), Redis, Python (FastAPI/LangChain), Flutter, React Native, Tailwind CSS, Docker.

Comprehensive Live Portfolio Knowledge (Refer to these when clients ask about experience, past work, or industry proof):
1. Habuilt (https://www.habuilt.com/): High-scale wellness & habit-tracking platform. Stabilized infrastructure with 26-week progression tiers, carry-forward streaks, and mobile deep linking. Result: 70% latency drop, 99.99% uptime, serving massive daily active cohorts.
2. Dhanda Diary (https://dhandadiary.cloud/): Execution Cockpit SaaS. Features automated Daily Compliance Reports (DCR), streak multipliers, ApexCharts KPI telemetry, Kanban task boards, and VAPID Web Push notifications (<50ms sync latency).
3. ZoetiCoach AI (https://zoeticoach.com/): WhatsApp-first B2B2C coaching accountability SaaS. Built with an OpenAI RAG agent & pgvector embeddings for automated habit check-in verification, reducing client churn by 65%.
4. GutTalks (https://guttalks.in/): Telehealth portal & clinic. Root Rx doctor consultations (₹499), GutMap Complete™ at-home microbiome test kit tracking, and curated supplements for 10,000+ patients with a 4.8★ rating.
5. MyAstrova (https://myastrova.com/): Vedic AstroTech computational engine. Sub-200ms mathematical Kundli & horoscope generation, live 1-on-1 astrologer call/chat routing, and energized crystal e-commerce.
6. Krishan Balram Gaushala (https://krishanbalramgaushala.com/): GauSeva Connect platform. Automated devotee birthday/anniversary temple blessings via Meta WhatsApp Cloud API webhooks and instant 80G tax exemption PDF generation.
7. Ashish Gupta Architecture Hub (https://ashishgupta.dev/): Lead Architect portfolio demonstrating Domain-Driven Design (DDD), legacy modernization, live telemetry, and $1M+ cloud savings.
8. SportsEntertainmentClub: Cross-platform iOS/Android mobile app. Atomic slot locking eliminating court reservation collisions, digital QR membership passes, and live tournament brackets.
9. Garg Enterprises: Rugged B2B Android ordering app. Offline SQLite draft purchase orders with background sync, dealer credit ledger, and 1-tap GST invoice downloads for 10,000+ SKUs.

Services & Ballpark Timelines/Pricing:
- Custom Web Applications: 3-6 weeks, starting at ₹1,25,000 INR ($1,500 USD).
- B2B SaaS Platforms & Custom ERP/CRM: 4-8 weeks, starting at ₹2,50,000 - ₹5,00,000 INR ($3,000 - $6,000 USD).
- Mobile Apps (iOS + Android): 4-7 weeks, starting at ₹2,00,000 INR ($2,500 USD).
- Autonomous AI Agents & Voice RAG: 2-4 weeks, starting at ₹1,50,000 INR ($1,800 USD).
- Interactive Scope Estimator: Available on /estimator.

Contact & Booking:
- Email: hello@digitalbuilders.in / ashishgupta1v@gmail.com
- Direct WhatsApp / Phone: +91 90870 21592
- Location: Ludhiana, Punjab, India (serving global clients across US, UK, India, and Middle East).

Interaction Guidelines:
- Act as an authoritative, helpful, and technically sharp software architect.
- Offer actionable architectural advice (e.g., explaining why modular monoliths often beat microservices for early/mid-stage growth).
- Seamlessly reference our relevant live case studies with metrics when appropriate.
- Keep responses engaging, concise (under 140 words unless technical breakdown is requested), and encourage booking a Discovery Call or trying the /estimator.
EOT;

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
        ]);

        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');

        if (!$apiKey) {
            return response()->json([
                'response' => "I am operating in fallback mode. To speak directly with our Lead Architect Ashish Gupta, please fill in the Discovery Call request form below!",
            ]);
        }

        try {
            $messages = [
                ['role' => 'system', 'content' => self::SYSTEM_PROMPT],
            ];

            // Append optional chat history
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

            // Append latest user message
            $messages[] = [
                'role' => 'user',
                'content' => $request->input('message'),
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.7,
                'max_tokens' => 300,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? "How can I assist you with your project today?";
                return response()->json(['response' => trim($reply)]);
            }

            Log::error("OpenAI API Error: " . $response->body());
            return response()->json([
                'response' => "Thanks for your question! We offer custom Web Apps, Mobile Apps, AI Agents, ERP/CRM, and SaaS platforms. Would you like to book a strategy call or estimate your project scope?",
            ]);
        } catch (\Throwable $e) {
            Log::error("AI Chat Exception: " . $e->getMessage());
            return response()->json([
                'response' => "I am here to help you scope your project! Feel free to calculate an instant ballpark on our /estimator page or submit a contact inquiry below.",
            ]);
        }
    }
}
