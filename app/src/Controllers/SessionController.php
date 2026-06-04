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
            return;
        }

        $this->requireOwner((int) $session['creator_id']);

        $model->delete((int) $id);
        $this->redirect('/sessions');
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
                'game_id'      => null,
                'title'        => $title,
                'scheduled_at' => $errors ? null : date('Y-m-d H:i:s', strtotime($scheduledAt)),
                'max_players'  => $maxPlayers,
                'is_private'   => $isPrivate,
                'description'  => $description,
            ],
        ];
    }
}