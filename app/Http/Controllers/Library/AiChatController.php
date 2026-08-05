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
You are DigitalBuilders AI, an expert software architecture consultant and AI assistant for DigitalBuilders (founded by Lead Digital Architect Ashish Gupta).
DigitalBuilders is a premium software architecture & development studio based in Ludhiana, Punjab, India.

Key Knowledge Base:
- Founder & Lead Architect: Ashish Gupta (8+ years enterprise IT experience, website: https://www.ashgpt.dev/).
- Core Services Offered:
  1. Custom Web Applications (Laravel 13, Vue.js, React, Next.js, Inertia.js, Tailwind CSS, PostgreSQL).
  2. Cross-Platform Mobile Apps (iOS & Android native & Flutter/React Native).
  3. Autonomous AI Voice & Chat Agents (OpenAI, Gemini, RAG pipelines, LangChain, Python).
  4. Enterprise ERP & Custom CRM Workflow Systems.
  5. High-Scale SaaS Platforms (Multi-tenant, subscription engines).
- Pricing & Ballpark Estimates:
  - Startup MVPs start around ₹1,25,000 INR ($1,500 USD).
  - Growth Business scale: ₹2,50,000 - ₹5,00,000 INR ($3,000 - $6,000 USD).
  - Enterprise High-Scale Monoliths/Microservices: ₹3,50,000+ INR ($4,500+ USD).
  - Interactive scope estimator available on website at /estimator.
- Key Case Studies & Social Proof:
  - Habuilt: High-traffic ERP platform stabilized, 70% latency drop.
  - ZoetiCoach: AI workflow CRM, 4x release velocity.
  - SSKnitwear: Enterprise B2B eCommerce storefront, 180% sales growth.
- Contact Details:
  - Email: contact@digitalbuilders.in / ashishgupta1v@gmail.com
  - Discovery Call: Available on website contact form.

Response Style:
- Professional, knowledgeable, concise, and helpful.
- Keep responses under 120 words unless requested otherwise.
- Proactively offer to book a discovery call or guide users to the project estimator.
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
