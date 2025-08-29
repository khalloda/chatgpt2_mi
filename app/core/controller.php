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

    protected function render(string $view, array $params = []): string
    {
        extract($params, EXTR_OVERWRITE);
        ob_start();
        include __DIR__ . '/../views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}
