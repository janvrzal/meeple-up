<?php

class GameController extends Controller
{
    public function search(): void
    {
        $q = trim($_GET['q'] ?? '');
        $results = mb_strlen($q) >= 2 ? (new GameService())->search($q) : [];

        header('Content-Type: application/json');
        echo json_encode($results);
    }
}