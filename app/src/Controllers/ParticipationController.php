<?php

class ParticipationController extends Controller
{
    public function join(string $id): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { $this->abort(404, 'Session not found'); }

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
        if (!empty($session['tournament_id'])
            && (new TournamentParticipation())->isMember(Auth::id(), (int) $session['tournament_id'])) {
            $status = 'approved';   // člen turnaje → bez schvalování
        }

        $participation->join(Auth::id(), $sessionId, $status);

        $this->redirect('/sessions/' . $sessionId);
    }

    public function leave(string $id): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        (new Participation())->leave(Auth::id(), (int) $id);

        // volitelný návrat (dismiss z dashboardu pošle "/")
        $back = $_POST['redirect'] ?? '';
        if ($back === '' || $back[0] !== '/' || str_starts_with($back, '//')) {
            $back = '/sessions/' . (int) $id;
        }
        $this->redirect($back);
    }

    public function approve(string $id): void
    {
        $this->verifyCsrf();
        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { $this->abort(404, 'Session not found'); }
        $this->requireOwner((int) $session['creator_id']);

        $userId = (int) ($_POST['user_id'] ?? 0);
        (new Participation())->setStatus($userId, $sessionId, 'approved');

        $back = $_POST['redirect'] ?? '';
        if ($back === '' || $back[0] !== '/' || str_starts_with($back, '//')) {
            $back = '/sessions/' . $sessionId;
        }
        $this->redirect($back);
    }

    public function reject(string $id): void
    {
        $this->verifyCsrf();
        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) { $this->abort(404, 'Session not found'); }
        $this->requireOwner((int) $session['creator_id']);

        $userId = (int) ($_POST['user_id'] ?? 0);
        (new Participation())->leave($userId, $sessionId);   // zamítnutí = smazání žádosti

        $back = $_POST['redirect'] ?? '';
        if ($back === '' || $back[0] !== '/' || str_starts_with($back, '//')) {
            $back = '/sessions/' . $sessionId;
        }
        $this->redirect($back);
    }

}