<?php declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $params = []): void
    {
        extract($params, EXTR_OVERWRITE);

        $content = $this->render($view, $params);

        // layout wrapper
        include __DIR__ . '/../views/layouts/main.php';
    }

protected function view_raw(string $view, array $params = []): void
{
    // Render a view file directly, without layouts (perfect for print/PDF).
    extract($params, EXTR_SKIP);
    $__view_file = __DIR__ . '/../views/' . $view . '.php';
    if (!is_file($__view_file)) {
        throw new \RuntimeException("View not found: {$view}");
    }
    include $__view_file;
}

    protected function render(string $view, array $params = []): string
    {
        extract($params, EXTR_OVERWRITE);
        ob_start();
        include __DIR__ . '/../views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}
