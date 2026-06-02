<?php

abstract class Controller
{
    protected function render(string $view, array $data = []) : void {
        extract($data);
        $viewPath = __DIR__ . '/../../views/' . $view . '.php';

        if(!is_file($viewPath)) {
            http_response_code(500);
            echo 'View ' . $view . ' not found';
            return;
        }

        ob_start(); // pro záchyt výstupů
        require $viewPath;
        $content = ob_get_clean(); // výstup uložen, buffer uvolněn

        require __DIR__.'/../../views/layout.php'; // načte layout, content využije layout.php
    }

}