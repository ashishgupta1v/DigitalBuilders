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
                'client_name' => 'Rajesh Sharma',
                'company' => 'ZoetiCoach ERP',
                'role' => 'Managing Director',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'We needed a complex custom ERP system integrated with automated AI workflows. DigitalBuilders delivered ahead of schedule with zero legacy tech debt. Their architecture-first mindset is unmatched.',
                'project_type' => 'ERP / CRM & AI Automation',
                'metric_highlight' => '4x Operational Velocity',
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'client_name' => 'Simran Kaur',
                'company' => 'SSKnitwear International',
                'role' => 'Head of Digital Transformation',
                'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'Modernizing a 25-year legacy retail brand was a daunting challenge. Ashish designed a ultra-fast storefront with seamless inventory synchronization. Sales grew 180% in the first quarter post-launch.',
                'project_type' => 'Enterprise E-Commerce Platform',
                'metric_highlight' => '+180% Sales Growth',
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'client_name' => 'Vikramaditya Verma',
                'company' => 'OmniFlow SaaS',
                'role' => 'Product Director',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=250',
                'rating' => 5,
                'content' => 'Working with DigitalBuilders felt like having a Principal Architect embedded in our team. Their code quality, test coverage, and documentation set a gold standard for our engineering org.',
                'project_type' => 'SaaS Platform Architecture',
                'metric_highlight' => '99.99% System Uptime',
                'is_featured' => true,
                'sort_order' => 4,
            ],
        ];

        foreach ($testimonials as $data) {
            Testimonial::updateOrCreate(['company' => $data['company']], $data);
        }
    }
}
