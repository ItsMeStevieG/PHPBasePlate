<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\View;

use ItsMeStevieG\PHPBasePlate\Content\Schema\ContentTypeRegistry;
use ItsMeStevieG\PHPBasePlate\Content\Services\EntryService;
use ItsMeStevieG\PHPBasePlate\Core\Config\Config;
use ItsMeStevieG\PHPBasePlate\Core\Container\Container;
use ItsMeStevieG\PHPBasePlate\Core\Routing\Router;
use ItsMeStevieG\PHPBasePlate\Settings\Services\MenuService;
use ItsMeStevieG\PHPBasePlate\Settings\Services\SettingsService;
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
            new TwigFunction('menu', [$this, 'menu']),
            new TwigFunction('setting', [$this, 'setting']),
            new TwigFunction('settings_group', [$this, 'settingsGroup']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt']),
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('safe_html', [$this, 'safeHtml'], ['is_safe' => ['html']]),
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

    public function menu(string $machineName): array
    {
        return $this->container->get(MenuService::class)->getMenuItems($machineName);
    }

    public function setting(string $group, string $key, mixed $default = null): mixed
    {
        return $this->container->get(SettingsService::class)->get($group, $key, $default);
    }

    public function settingsGroup(string $group): array
    {
        return $this->container->get(SettingsService::class)->getGroup($group);
    }

    /**
     * Sanitise HTML content - allows safe tags only, strips dangerous attributes.
     */
    public function safeHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $allowed = '<p><br><strong><b><em><i><u><s><a><ul><ol><li><h1><h2><h3><h4><h5><h6>'
            . '<blockquote><pre><code><hr><table><thead><tbody><tr><th><td><img><figure><figcaption><div><span>';

        $clean = strip_tags($html, $allowed);

        // Remove dangerous attributes (on*, style with expressions, javascript: URLs)
        $clean = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/\s+on\w+\s*=\s*\S+/i', '', $clean);
        $clean = preg_replace('/href\s*=\s*["\']?\s*javascript\s*:/i', 'href="removed:', $clean);
        $clean = preg_replace('/src\s*=\s*["\']?\s*javascript\s*:/i', 'src="removed:', $clean);

        return $clean;
    }
}
