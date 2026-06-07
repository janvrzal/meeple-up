<?php

/**
 * Vykreslí PHP šablonu do společného layoutu.
 * Sdílené jádro pro Controller::render() i pro chybové stránky (Router / abort).
 */
class View
{
    public static function render(string $__view__, array $__data__ = []): void
    {
        $__path__ = __DIR__ . '/../../views/' . $__view__ . '.php';

        if (!is_file($__path__)) {
            http_response_code(500);
            echo "View '" . htmlspecialchars($__view__) . "' not found";
            return;
        }

        // EXTR_SKIP: data z controlleru nikdy nepřepíšou lokální proměnné ($__view__, $__path__)
        extract($__data__, EXTR_SKIP);

        ob_start();
        require $__path__;
        $content = ob_get_clean();

        require __DIR__ . '/../../views/layout.php';
    }
}
