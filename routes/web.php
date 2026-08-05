<?php

use App\Http\Controllers\Library\AiChatController;
use App\Http\Controllers\Library\BlogController;
use App\Http\Controllers\Library\EstimatorController;
use App\Http\Controllers\Library\LeadController;
use App\Http\Controllers\Library\PortfolioController;
use App\Http\Controllers\Library\ServiceController;
use App\Http\Controllers\Library\TestimonialController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Blog Routes (Public)
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
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
    Route::get('/zoeticoach', [PortfolioController::class, 'zoeticoach'])->name('zoeticoach');
    Route::get('/ssknitwear', [PortfolioController::class, 'ssknitwear'])->name('ssknitwear');
});

// Estimator routes (public)
Route::get('/estimator', [EstimatorController::class, 'index'])->name('estimator.index');
Route::post('/estimator/submit', [EstimatorController::class, 'submitEstimate'])->middleware('throttle:5,60')->name('estimator.submit');

// Public Testimonials & AI Chat API
Route::get('/api/testimonials', [TestimonialController::class, 'index'])->name('api.testimonials');
Route::post('/api/ai-chat', [AiChatController::class, 'chat'])->middleware('throttle:15,60')->name('api.ai-chat');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Library module routes
Route::prefix('library')->name('library.')->group(function () {
    // Admin-only management
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/export', [LeadController::class, 'export'])->name('leads.export');
        Route::patch('/leads/{id}/status', [LeadController::class, 'updateStatus'])->name('leads.status');
        Route::get('/leads/{id}/notes', [LeadController::class, 'getNotes'])->name('leads.notes');
        Route::post('/leads/{id}/notes', [LeadController::class, 'addNote'])->name('leads.notes.store');

        // Testimonial admin management
        Route::post('/testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
        Route::put('/testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');
    });

    // Public routes
    Route::get('/contact', [LeadController::class, 'create'])->name('leads.create');
    Route::post('/contact', [LeadController::class, 'store'])->middleware('throttle:3,60')->name('leads.store');
    Route::get('/docs', [LeadController::class, 'docs'])->name('docs');

    // Legal pages (public)
    Route::view('/privacy-policy', 'pages.privacy-policy')->name('privacy');
    Route::view('/terms-of-service', 'pages.terms-of-service')->name('terms');
});

require __DIR__.'/auth.php';
