<?php

class TournamentController extends Controller
{
    public function index(): void
    {
        $this->render('tournaments/index', ['tournaments' => (new Tournament())->all()]);
    }

    public function show(string $id): void
    {
        $tournament = (new Tournament())->findById((int) $id);
        if ($tournament === null) { $this->abort(404, 'Tournament not found'); }

        $this->render('tournaments/show', [
            'tournament' => $tournament,
            'sessions'   => (new Session())->forTournament((int) $id),
            'isMember'    => Auth::check() && (new TournamentParticipation())->isMember(Auth::id(), (int)$id),
            'memberCount' => (new TournamentParticipation())->memberCount((int) $id),
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();
        $this->render('tournaments/create');
    }

    public function store(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $name        = trim($_POST['name'] ?? '');
        $bggId       = (int) ($_POST['bgg_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 150) {
            $errors[] = 'Name is required (max 150 characters).';
        }
        $gameId = $bggId > 0 ? (new GameService())->resolve($bggId) : null;
        if ($gameId === null) {
            $errors[] = 'Please choose a game for the tournament.';
        }

        if ($errors) {
            $this->render('tournaments/create', ['errors' => $errors, 'old' => $_POST]);
            return;
        }

        $id = (new Tournament())->create(Auth::id(), $gameId, $name, $description);
        if (!empty($_POST['join_self'])) {
            (new TournamentParticipation())->join(Auth::id(), $id);
        }
        $this->redirect('/tournaments/' . $id);
    }

    public function destroy(string $id): void
    {
        $this->verifyCsrf();
        $tournament = (new Tournament())->findById((int) $id);
        if ($tournament === null) { $this->abort(404, 'Tournament not found'); }
        $this->requireOwner((int) $tournament['creator_id']);

        (new Tournament())->delete((int) $id);
        $this->redirect('/tournaments');
    }

    public function join(string $id): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $tid = (int) $id;
        if ((new Tournament())->findById($tid) === null) { $this->abort(404, 'Tournament not found'); }
        (new TournamentParticipation())->join(Auth::id(), $tid);
        $this->redirect('/tournaments/' . $tid);
    }

    public function leave(string $id): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        (new TournamentParticipation())->leave(Auth::id(), (int) $id);
        $this->redirect('/tournaments/' . (int) $id);
    }
}