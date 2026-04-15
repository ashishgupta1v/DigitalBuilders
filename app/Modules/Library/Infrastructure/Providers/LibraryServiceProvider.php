<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Providers;

use App\Modules\Library\Application\Interfaces\LeadNotifierInterface;
use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;
use App\Modules\Library\Domain\Services\LeadFactory;
use App\Modules\Library\Infrastructure\Persistence\Repositories\EloquentLeadRepository;
use App\Modules\Library\Infrastructure\Services\MailLeadNotifier;
use App\Modules\Library\Infrastructure\Services\ParsedownRenderer;
use Illuminate\Support\ServiceProvider;

class LibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Domain → Infrastructure bindings
        $this->app->bind(LeadRepositoryInterface::class, EloquentLeadRepository::class);

        // Application → Infrastructure bindings
        $this->app->bind(LeadNotifierInterface::class, MailLeadNotifier::class);
        $this->app->bind(MarkdownRendererInterface::class, ParsedownRenderer::class);

        // Domain services
        $this->app->bind(LeadFactory::class);
    }

    public function boot(): void
    {
        // Load module migrations
        $this->loadMigrationsFrom(__DIR__ . '/../Persistence/Migrations');
    }
}
