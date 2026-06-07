<?php

/**
 * Vykreslí PHP šablonu do společného layoutu.
 * Sdílené jádro pro Controller::render() i pro chybové stránky (Router / abort).
 */
class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';

        if (!is_file($viewPath)) {
            http_response_code(500);
            echo "View '" . htmlspecialchars($view) . "' not found";
            return;
        }

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layout.php';
    }
}
