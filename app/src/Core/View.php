<?php

/**
 * Vykreslí PHP šablonu do společného layoutu.
 * Sdílené jádro pro Controller::render() i pro chybové stránky (Router / abort).
 */
class View
{
    public static function render(string $__view_template__, array $__view_data__ = []): void
    {
        extract($__view_data__, EXTR_SKIP);
        $__view_path__ = __DIR__ . '/../../views/' . $__view_template__ . '.php';

        if (!is_file($__view_path__)) {
            http_response_code(500);
            echo "View '" . htmlspecialchars($__view_template__) . "' not found";
            return;
        }

        ob_start();
        require $__view_path__;
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layout.php';
    }
}
