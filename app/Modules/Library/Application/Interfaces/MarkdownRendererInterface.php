<?php

declare(strict_types=1);

namespace App\Modules\Library\Application\Interfaces;

interface MarkdownRendererInterface
{
    public function toHtml(string $markdown): string;
}
