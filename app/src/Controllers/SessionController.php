<?php

class SessionController extends Controller
{
    public function create(): void{
        $this->requireLogin();
        $locations = (new Location())->all();
        $this->render('sessions/create', ['locations' => $locations]);
    }

    public function store(): void{
        $this->requireLogin();

        if(!Csrf::check($_POST['csrf_token'] ?? null)){
            http_response_code(419);
            exit('Invalid CSRF token');
        }

        $result=$this->collectInput();
        if ($result['errors']) {
            $this->render('sessions/create', [
                'errors'    => $result['errors'],
                'locations' => (new Location())->all(),
                'old'       => $_POST,
            ]);
            return;
        }

        $data = $result['data'];
        $data['creator_id'] = Auth::id();
        $id = (new Session())->create($data);
        (new Participation())->join(Auth::id(), $id, 'approved');

        $this->redirect('/sessions/' . $id);
    }

    public function edit(int $id): void{
        $session = (new Session())->findById((int) $id);

        if ($session === null) {
            http_response_code(404);
            echo 'Session not found';
            return;
        }

        $this->requireOwner((int) $session['creator_id']);

        $game = $session['game_id'] ? (new Game())->findById((int) $session['game_id']) : null;

        $dt = strtotime($session['scheduled_at']);
        $this->render('sessions/create', [
            'locations' => (new Location())->all(),
            'heading'   => 'Edit session',
            'submit'    => 'Save changes',
            'action'    => BASE_PATH . '/sessions/' . $id . '/update',
            'old'       => [
                'title'       => $session['title'],
                'location_id' => $session['location_id'],
                'date'        => date('Y-m-d', $dt),
                'time'        => date('H:i', $dt),
                'max_players' => $session['max_players'],
                'is_private'  => $session['is_private'],
                'description' => $session['description'],
                'bgg_id'      => $game['bgg_id'] ?? '',
                'game_name'   => $game['name'] ?? '',
            ],
        ]);
    }

    public function update(int $id): void{
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }

        $model = new Session();
        $session = $model->findById((int) $id);
        if ($session === null) {
            http_response_code(404);
            echo 'Session not found';
            return;
        }

        $this->requireOwner((int) $session['creator_id']);

        $result = $this->collectInput();
        if ($result['errors']) {
            $this->render('sessions/create', [
                'errors'    => $result['errors'],
                'locations' => (new Location())->all(),
                'old'       => $_POST,
                'heading'   => 'Edit session',
                'submit'    => 'Save changes',
                'action'    => BASE_PATH . '/sessions/' . $id . '/update',
            ]);
            return;
        }

        $model->update((int) $id, $result['data']);
        $this->redirect('/sessions/' . $id);
    }

    public function destroy(string $id): void{
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Invalid CSRF token');
        }

        $model = new Session();
        $session = $model->findById((int) $id);
        if ($session === null) {
            http_response_code(404);
            exit('Session not found.');
        }

        $this->requireOwner((int) $session['creator_id']);

        $model->delete((int) $id);
        $this->redirect('/sessions');
    }

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

    private function collectInput(): array
    {
        $errors = [];

        $title       = trim($_POST['title'] ?? '');
        $locationId  = (int) ($_POST['location_id'] ?? 0);
        $newName     = trim($_POST['new_location_name'] ?? '');
        $newCity     = trim($_POST['new_location_city'] ?? '');
        $date        = trim($_POST['date'] ?? '');
        $time        = trim($_POST['time'] ?? '');
        $scheduledAt = $date . ' ' . $time;
        $maxRaw      = trim($_POST['max_players'] ?? '');
        $maxPlayers  = $maxRaw === '' ? null : (int) $maxRaw;
        $isPrivate   = isset($_POST['is_private']) ? 1 : 0;
        $description = trim($_POST['description'] ?? '');
        $bggId  = (int) ($_POST['bgg_id'] ?? 0);
        $gameId = $bggId > 0 ? (new GameService())->resolve($bggId) : null;

        $locationModel = new Location();
        if ($newName !== '' && $newCity !== '') {
            $locationId = $locationModel->create($newName, $newCity);
        } elseif ($locationId <= 0 || $locationModel->findById($locationId) === null) {
            $errors[] = 'Please choose a location or add a new one.';
        }

        if ($title === '' || mb_strlen($title) > 150) {
            $errors[] = 'Title is required (max 150 characters).';
        }
        if ($date === '' || $time === '' || strtotime($scheduledAt) === false) {
            $errors[] = 'Please enter a valid date and time.';
        } elseif (strtotime($scheduledAt) < time()) {
            $errors[] = 'The session must be scheduled in the future.';
        }
        if ($maxPlayers !== null && ($maxPlayers < 2 || $maxPlayers > 255)) {
            $errors[] = 'Max players must be between 2 and 255 (or empty for no limit).';
        }

        return [
            'errors' => $errors,
            'data'   => [
                'location_id'  => $locationId,
                'game_id'      => $gameId,
                'title'        => $title,
                'scheduled_at' => $errors ? null : date('Y-m-d H:i:s', strtotime($scheduledAt)),
                'max_players'  => $maxPlayers,
                'is_private'   => $isPrivate,
                'description'  => $description,
            ],
        ];
    }

    public function index(): void{
        $filters = [
            'location_id' => $_GET['location_id'] ?? null,
            'game_id'     => $_GET['game_id'] ?? null,
            'free_only'   => !empty($_GET['free_only']),
        ];

        $this->render('sessions/index', [
            'sessions'  => (new Session())->filtered($filters),
            'locations' => (new Location())->all(),
            'games'     => (new Session())->gamesInUpcoming(),
            'filters'   => $filters,
        ]);
    }

    public function show(string $id): void
    {
        $sessionId = (int) $id;
        $session = (new Session())->findById($sessionId);
        if ($session === null) {
            http_response_code(404);
            echo 'Session not found';
            return;
        }

        $participation = new Participation();

        $this->render('sessions/show', [
            'session'      => $session,
            'participants' => $participation->forSession($sessionId),
            'mine'         => Auth::check() ? $participation->find(Auth::id(), $sessionId) : null,
            'comments'     => (new Comment())->forSession($sessionId),
        ]);


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

    public function addComment(string $id): void
    {
        $this->requireLogin();
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }

        $sessionId = (int) $id;

        $session = (new Session())->findById($sessionId);
        if ($session === null) { http_response_code(404); echo 'Session not found'; return; }

        $isCreator = (int) $session['creator_id'] === Auth::id();
        $mine = (new Participation())->find(Auth::id(), $sessionId);

        if (!$isCreator && ($mine === null || $mine['status'] !== 'approved')) {
            http_response_code(403);
            exit('Only participants can post messages.');
        }

        $body = trim($_POST['body'] ?? '');

        if ($body !== '') {
            (new Comment())->create($sessionId, Auth::id(), $body);
        }

        $this->redirect('/sessions/' . $sessionId);
    }

    public function deleteComment(string $id): void
    {
        $this->requireLogin();
        if (!Csrf::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419); exit('Invalid CSRF token');
        }

        $model = new Comment();
        $comment = $model->findById((int) $id);
        if ($comment === null) { http_response_code(404); echo 'Comment not found'; return; }

        $this->requireOwner((int) $comment['user_id']);

        $model->delete((int) $id);
        $this->redirect('/sessions/' . (int) $comment['session_id']);
    }
}