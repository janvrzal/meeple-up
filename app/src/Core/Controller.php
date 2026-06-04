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

    protected function redirect(string $path): void{
        header('Location: ' . BASE_PATH . $path);
        exit;
    }

    public function index(): void{
        $sessions = (new Session())->upcoming();
        $this->render("sessions/index", ['sessions' => $sessions]);
    }

    public function show(string $id): void{
        $session = (new Session())->findById((int) $id);
        if ($session === null) {
            http_response_code(404);
            echo 'Session not found';
            return;
        }
        $this->render('sessions/show', ['session' => $session]);
    }

    protected function requireLogin(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login');
        }
    }

    protected function requireGuest(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireLogin();
        if ((Auth::user()['role'] ?? 'user') !== 'admin') {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    protected function requireOwner(int $ownerId): void
    {
        $this->requireLogin();

        $isOwner = Auth::id() === $ownerId;
        $isAdmin = (Auth::user()['role'] ?? 'user') === 'admin';

        if (!$isOwner && !$isAdmin) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

}