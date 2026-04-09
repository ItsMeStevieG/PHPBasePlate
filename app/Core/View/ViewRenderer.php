<?php

declare(strict_types=1);

namespace ItsMeStevieG\PHPBasePlate\Core\View;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Loader\FilesystemLoader;

class ViewRenderer
{
    private Environment $twig;

    public function __construct(string $viewPath, ?string $cachePath = null)
    {
        $loader = new FilesystemLoader($viewPath);

        $options = [];
        if ($cachePath !== null) {
            $options['cache'] = $cachePath;
        }

        $this->twig = new Environment($loader, $options);
    }

    public function addExtension(AbstractExtension $extension): void
    {
        $this->twig->addExtension($extension);
    }

    public function render(string $template, array $data = []): string
    {
        if (!str_ends_with($template, '.twig')) {
            $template .= '.twig';
        }

        return $this->twig->render($template, $data);
    }

    public function getTwig(): Environment
    {
        return $this->twig;
    }

    public function addGlobal(string $name, mixed $value): void
    {
        $this->twig->addGlobal($name, $value);
    }
}
