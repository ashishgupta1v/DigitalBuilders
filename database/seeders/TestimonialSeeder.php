<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $testimonials = [
            [
                'client_name' => 'Gurpreet Singh',
                'company' => 'Habuilt Technologies',
                'role' => 'CTO & Co-Founder',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'DigitalBuilders re-architected our high-traffic platform from the ground up. Ashish brought Silicon Valley-level engineering standards. Our server latencies dropped by 70% and database throughput doubled within weeks.',
                'project_type' => 'Custom Web Application',
                'metric_highlight' => '70% Latency Reduction',
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'client_name' => 'Harpreet Singh',
                'company' => 'Dhanda Diary Cloud',
                'role' => 'Co-Founder & Product Head',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'Dhanda Diary transformed daily bookkeeping for our business network. The platform is responsive, secure, and handles complex multi-business ledgers with zero friction. DigitalBuilders engineered a world-class FinTech product.',
                'project_type' => 'Cloud SaaS & FinTech',
                'metric_highlight' => '100% Data Integrity',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'client_name' => 'Rajesh Sharma',
                'company' => 'ZoetiCoach ERP',
                'role' => 'Managing Director',
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'We needed a complex custom ERP system integrated with automated AI workflows. DigitalBuilders delivered ahead of schedule with zero legacy tech debt. Their architecture-first mindset is unmatched.',
                'project_type' => 'ERP / CRM & AI Automation',
                'metric_highlight' => '4x Operational Velocity',
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'client_name' => 'Dr. Mehak Verma',
                'company' => 'GutTalks Health',
                'role' => 'Head of Clinical Nutrition',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'The interactive gut health assessment and booking flow engineered by DigitalBuilders increased our consultation bookings dramatically. Our patients love how intuitive the interface is.',
                'project_type' => 'Telehealth Web Platform',
                'metric_highlight' => '3.2x Booking Conversion',
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'client_name' => 'Acharya Raman',
                'company' => 'MyAstrova',
                'role' => 'Principal Astrologer & Founder',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'DigitalBuilders delivered a mathematically accurate and stunning AstroTech platform. The charts render instantly and the seamless booking process has made MyAstrova a trusted name for Vedic consultations.',
                'project_type' => 'AstroTech & Consumer Platform',
                'metric_highlight' => '<200ms Ephemeris Compute',
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'client_name' => 'Trust Secretariat',
                'company' => 'Krishan Balram Gaushala',
                'role' => 'Executive Trustee',
                'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'The online portal built by DigitalBuilders gave our donors complete transparency and instant 80G tax receipts. It eliminated thousands of manual bookkeeping hours for our trust team.',
                'project_type' => 'Non-Profit & Trust Platform',
                'metric_highlight' => '100% 80G Receipt Delivery',
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'client_name' => 'Simran Kaur',
                'company' => 'SSKnitwear International',
                'role' => 'Head of Digital Transformation',
                'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'Modernizing a 25-year legacy retail brand was a daunting challenge. Ashish designed an ultra-fast storefront with seamless inventory synchronization. Sales grew 180% in the first quarter post-launch.',
                'project_type' => 'Enterprise E-Commerce Platform',
                'metric_highlight' => '+180% Sales Growth',
                'is_featured' => true,
                'sort_order' => 7,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::updateOrCreate(['company' => $data['company']], $data);
        }
    }
}
