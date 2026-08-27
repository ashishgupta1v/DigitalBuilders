<?php

use App\Http\Controllers\Library\AiChatController;
use App\Http\Controllers\Library\BlogController;
use App\Http\Controllers\Library\EstimatorController;
use App\Http\Controllers\Library\LeadController;
use App\Http\Controllers\Library\PortfolioController;
use App\Http\Controllers\Library\ServiceController;
use App\Http\Controllers\Library\SitemapController;
use App\Http\Controllers\Library\TestimonialController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'app' => 'DigitalBuilders',
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
    ], 200, ['Cache-Control' => 'no-store, no-cache']);
})->name('health');

Route::get('/', function () {
    return Inertia::render('Home');
});

// Blog Routes (Public)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/feed.xml', [BlogController::class, 'feed'])->name('blog.feed');
Route::get('/blog/feed.xml', [BlogController::class, 'feed']);
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

// Service Landing Pages (Public)
Route::prefix('services')->name('services.')->group(function () {
    Route::get('/web-applications', [ServiceController::class, 'webApp'])->name('web-app');
    Route::get('/mobile-apps', [ServiceController::class, 'mobileApp'])->name('mobile-app');
    Route::get('/ai-solutions', [ServiceController::class, 'aiSolutions'])->name('ai-solutions');
    Route::get('/erp-crm', [ServiceController::class, 'erpCrm'])->name('erp-crm');
    Route::get('/saas-platforms', [ServiceController::class, 'saasPlatforms'])->name('saas-platforms');
});

// Portfolio Case Study Detail Pages (Public)
Route::prefix('portfolio')->name('portfolio.')->group(function () {
    Route::get('/habuilt', [PortfolioController::class, 'habuilt'])->name('habuilt');
    Route::get('/dhandadiary', [PortfolioController::class, 'dhandadiary'])->name('dhandadiary');
    Route::get('/zoeticoach', [PortfolioController::class, 'zoeticoach'])->name('zoeticoach');
    Route::get('/guttalks', [PortfolioController::class, 'guttalks'])->name('guttalks');
    Route::get('/myastrova', [PortfolioController::class, 'myastrova'])->name('myastrova');
    Route::get('/gaushala', [PortfolioController::class, 'gaushala'])->name('gaushala');
    Route::get('/sports-club', [PortfolioController::class, 'sportsClub'])->name('sports-club');
    Route::get('/garg-enterprises', [PortfolioController::class, 'gargEnterprises'])->name('garg-enterprises');
    Route::get('/ashishgupta', [PortfolioController::class, 'ashishgupta'])->name('ashishgupta');
    Route::get('/ssknitwear', [PortfolioController::class, 'ssknitwear'])->name('ssknitwear');
});

// Pricing & Rate Card Brochures (Public)
Route::get('/pricing', [ServiceController::class, 'pricing'])->name('pricing.index');

Route::get('/downloads/{file}', function (string $file) {
    $path = public_path('downloads/' . $file);
    if (!file_exists($path)) {
        abort(404);
    }
    return response(file_get_contents($path), 200, [
        'Content-Type' => 'text/html; charset=utf-8',
    ]);
})->where('file', '[A-Za-z0-9\-\_\.]+')->name('downloads.show');

// Estimator routes (public)
Route::get('/estimator', [EstimatorController::class, 'index'])->name('estimator.index');
Route::post('/estimator/submit', [EstimatorController::class, 'submitEstimate'])->middleware('throttle:5,60')->name('estimator.submit');

// Public Testimonials & AI Chat API (Dual-routed for Vercel Serverless compatibility)
Route::get('/api/testimonials', [TestimonialController::class, 'index'])->name('api.testimonials');
Route::get('/ajax/testimonials', [TestimonialController::class, 'index'])->name('ajax.testimonials');
Route::post('/api/ai-chat', [AiChatController::class, 'chat'])->middleware('throttle:15,60')->name('api.ai-chat');
Route::post('/ajax/ai-chat', [AiChatController::class, 'chat'])->middleware('throttle:15,60')->name('ajax.ai-chat');

// Library module routes
Route::prefix('library')->name('library.')->group(function () {
    // Public routes
    Route::get('/contact', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/contact', [LeadController::class, 'store'])->middleware('throttle:3,60')->name('leads.store');
    Route::get('/docs', [LeadController::class, 'docs'])->name('docs');

    // Legal pages (public)
    Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy');
    Route::view('/terms-of-service', 'pages.terms-of-service')->name('terms');
});
