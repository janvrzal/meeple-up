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

        $title       = trim($_POST['title'] ?? '');
        $locationId = (int) ($_POST['location_id'] ?? 0);
        $newName    = trim($_POST['new_location_name'] ?? '');
        $newCity    = trim($_POST['new_location_city'] ?? '');
        $locationModel = new Location();

        // uživatel zadal novou lokaci → vytvoř ji a použij ji
        $date = trim($_POST['date'] ?? '');
        $time = trim($_POST['time'] ?? '');
        $scheduledAt = $date . ' ' . $time;
        $maxPlayersRaw = trim($_POST['max_players'] ?? '');
        $maxPlayers = $maxPlayersRaw === '' ? null : (int) $maxPlayersRaw;
        $isPrivate   = isset($_POST['is_private']) ? 1 : 0;
        $description = trim($_POST['description'] ?? '');

        $errors = [];

        if ($title === '' || mb_strlen($title) > 150) {
            $errors[] = 'Title is required (max 150 characters).';
        }
        if ($newName !== '' && $newCity !== '') {
            $locationId = $locationModel->create($newName, $newCity);
        } elseif ($locationId <= 0 || $locationModel->findById($locationId) === null) {
            $errors[] = 'Please choose a location or add a new one.';
        }
        if ($date === '' || $time === '' || strtotime($scheduledAt) === false) {
            $errors[] = 'Please enter a valid date and time.';
        } elseif (strtotime($scheduledAt) < time()) {
            $errors[] = 'The session must be scheduled in the future.';
        }
        if ($maxPlayers !== null && ($maxPlayers < 2 || $maxPlayers > 255)) {
            $errors[] = 'Max players must be between 2 and 255 (or empty for no limit).';
        }

        if ($errors) {
            $this->render('sessions/create', [
                'errors'    => $errors,
                'locations' => (new Location())->all(),
                'old'       => $_POST,
            ]);
            return;
        }

        $id = (new Session())->create([
            'creator_id'   => Auth::id(),
            'location_id'  => $locationId,
            'game_id'      => null,
            'title'        => $title,
            'scheduled_at' => date('Y-m-d H:i:s', strtotime($scheduledAt)),
            'max_players'  => $maxPlayers,
            'is_private'   => $isPrivate,
            'description'  => $description,
        ]);

        $this->redirect('/sessions/' . $id);
    }
}