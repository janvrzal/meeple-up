<?php

class FavoriteController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $games = (new Favorite())->forUser(Auth::id());
        $this->render('games/collection', ['games' => $games]);
    }

    public function toggle(string $gameId): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $gid = (int) $gameId;
        $fav = new Favorite();

        if ($fav->isFavorite(Auth::id(), $gid)) {
            $fav->remove(Auth::id(), $gid);
        } else {
            $fav->add(Auth::id(), $gid);
        }

        $back = $_POST['redirect'] ?? '/games';
        if ($back === '' || $back[0] !== '/' || str_starts_with($back, '//')) {
            $back = '/games';
        }
        $this->redirect($back);
    }
}