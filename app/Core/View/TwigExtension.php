<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\View;

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Config\Config;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Routing\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class TwigExtension extends AbstractExtension
{
    public function __construct(private readonly Container $container)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('config', [$this, 'config']),
            new TwigFunction('route', [$this, 'route']),
            new TwigFunction('content_list', [$this, 'contentList']),
            new TwigFunction('content_entry', [$this, 'contentEntry']),
            new TwigFunction('content_types', [$this, 'contentTypes']),
            new TwigFunction('asset', [$this, 'asset']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt']),
            new TwigFilter('time_ago', [$this, 'timeAgo']),
        ];
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return $this->container->get(Config::class)->get($key, $default);
    }

    public function route(string $name, array $params = []): string
    {
        return $this->container->get(Router::class)->url($name, $params);
    }

    public function contentList(string $type, int $limit = 10, ?string $status = 'published'): array
    {
        $result = $this->container->get(EntryService::class)->list($type, 1, $limit, $status);

        return $result['items'];
    }

    public function contentEntry(string $type, string $slug): ?array
    {
        return $this->container->get(EntryService::class)->findBySlug($type, $slug);
    }

    public function contentTypes(): array
    {
        return $this->container->get(ContentTypeRegistry::class)->all();
    }

    public function asset(string $path): string
    {
        $baseUrl = $this->container->get(Config::class)->get('app.url', '');

        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }

    public function excerpt(string $text, int $length = 200): string
    {
        $stripped = strip_tags($text);

        if (mb_strlen($stripped) <= $length) {
            return $stripped;
        }

        return rtrim(mb_substr($stripped, 0, $length)) . '...';
    }

    public function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'just now';
        }
        if ($diff < 3600) {
            $mins = (int) floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        }
        if ($diff < 2592000) {
            $days = (int) floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }

        return date('M j, Y', $time);
    }
}
