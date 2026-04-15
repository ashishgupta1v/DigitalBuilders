<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Services;

use App\Modules\Library\Application\Interfaces\MarkdownRendererInterface;
use Parsedown;

final class ParsedownRenderer implements MarkdownRendererInterface
{
    private Parsedown $parsedown;

    public function __construct()
    {
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(true);
    }

    public function toHtml(string $markdown): string
    {
        return $this->parsedown->text($markdown);
    }
}
