<?php

class ParticipationController extends Controller
{
    public function join(string $id): void
    {
        $this->requireLogin();
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }

        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { http_response_code(404); echo 'Session not found'; return; }

        $participation = new Participation();

        // už přihlášený? nic nedělej
        if ($participation->find(Auth::id(), $sessionId) !== null) {
            $this->redirect('/sessions/' . $sessionId);
        }

        // plno? (jen když je limit nastavený)
        if ($session['max_players'] !== null
            && (int) $session['player_count'] >= (int) $session['max_players']) {
            $this->redirect('/sessions/' . $sessionId);
        }

        // soukromé → pending (čeká na schválení), veřejné → approved
        $status = $session['is_private'] ? 'pending' : 'approved';
        $participation->join(Auth::id(), $sessionId, $status);

        $this->redirect('/sessions/' . $sessionId);
    }

    public function leave(string $id): void
    {
        $this->requireLogin();
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }

        (new Participation())->leave(Auth::id(), (int) $id);
        $this->redirect('/sessions/' . (int) $id);
    }

    public function approve(string $id): void
    {
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }
        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { http_response_code(404); echo 'Session not found'; return; }
        $this->requireOwner((int) $session['creator_id']);

        $userId = (int) ($_POST['user_id'] ?? 0);
        (new Participation())->setStatus($userId, $sessionId, 'approved');
        $this->redirect('/sessions/' . $sessionId);
    }

    public function reject(string $id): void
    {
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }
        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { http_response_code(404); echo 'Session not found'; return; }
        $this->requireOwner((int) $session['creator_id']);

        $userId = (int) ($_POST['user_id'] ?? 0);
        (new Participation())->leave($userId, $sessionId);   // zamítnutí = smazání žádosti
        $this->redirect('/sessions/' . $sessionId);
    }

}