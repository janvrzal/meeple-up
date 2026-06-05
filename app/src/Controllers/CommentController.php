<?php

class CommentController extends Controller
{
    public function store(string $id): void
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

    public function destroy(string $id): void
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