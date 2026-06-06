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

        $this->verifyCsrf();

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

        if ($session === null) { $this->abort(404, 'Session not found'); }

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
        $this->verifyCsrf();

        $model = new Session();
        $session = $model->findById((int) $id);
        if ($session === null) { $this->abort(404, 'Session not found'); }

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
        $this->verifyCsrf();

        $model = new Session();
        $session = $model->findById((int) $id);
        if ($session === null) { $this->abort(404, 'Session not found'); }

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
        if ($session === null) { $this->abort(404, 'Session not found'); }

        $participation = new Participation();

        [$start, $end] = $this->eventTimes($session);

        $title = $session['title'] . (!empty($session['game_name']) ? ' – ' . $session['game_name'] : '');
        $googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
            . '&text='     . urlencode($title)
            . '&dates='    . $start->format('Ymd\THis\Z') . '/' . $end->format('Ymd\THis\Z')
            . '&location=' . urlencode($session['location_name'] . ', ' . $session['location_city'])
            . '&details='  . urlencode($session['description'] ?? '');

        $this->render('sessions/show', [
            'session'      => $session,
            'participants' => $participation->forSession($sessionId),
            'mine'         => Auth::check() ? $participation->find(Auth::id(), $sessionId) : null,
            'comments'     => (new Comment())->forSession($sessionId),
            'googleUrl'    => $googleUrl,
        ]);
    }

    public function calendar(string $id): void
    {
        $session = (new Session())->findById((int) $id);
        if ($session === null) { $this->abort(404, 'Session not found'); }

        [$start, $end] = $this->eventTimes($session);

        $summary  = $session['title'];
        if (!empty($session['game_name'])) {
            $summary .= ' – ' . $session['game_name'];
        }
        $location = $session['location_name'] . ', ' . $session['location_city'];

        $ics = "BEGIN:VCALENDAR\r\n"
            . "VERSION:2.0\r\n"
            . "PRODID:-//Meeple-Up//EN\r\n"
            . "BEGIN:VEVENT\r\n"
            . 'UID:session-' . (int) $session['id'] . "@meeple-up\r\n"
            . 'DTSTAMP:' . gmdate('Ymd\THis\Z') . "\r\n"
            . 'DTSTART:' . $start->format('Ymd\THis\Z') . "\r\n"
            . 'DTEND:' . $end->format('Ymd\THis\Z') . "\r\n"
            . 'SUMMARY:' . $this->icsEscape($summary) . "\r\n"
            . 'LOCATION:' . $this->icsEscape($location) . "\r\n"
            . 'DESCRIPTION:' . $this->icsEscape($session['description'] ?? '') . "\r\n"
            . "END:VEVENT\r\n"
            . "END:VCALENDAR\r\n";

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="session-' . (int) $session['id'] . '.ics"');
        echo $ics;
    }

    private function icsEscape(string $text): string
    {
        // RFC 5545: escapovat \ ; , a nové řádky
        $text = str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\\;', '\\,', '\\n', '\\n'], $text);
        return $text;
    }

    /** Vrátí [start, end] jako DateTime v UTC pro daný session. */
    private function eventTimes(array $session): array
    {
        $start = new DateTime($session['scheduled_at'], new DateTimeZone('Europe/Prague'));
        $start->setTimezone(new DateTimeZone('UTC'));

        $minutes = (int) ($session['playing_time'] ?? 0) ?: 120;
        $end = (clone $start)->modify("+{$minutes} minutes");

        return [$start, $end];
    }
}