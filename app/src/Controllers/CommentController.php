<?php

class CommentController extends Controller
{
    public function store(string $id): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $sessionId = (int) $id;

        $session = (new Session())->findById($sessionId);
        if ($session === null) { $this->abort(404, 'Session not found'); }

        $isCreator = (int) $session['creator_id'] === Auth::id();
        $mine = (new Participation())->find(Auth::id(), $sessionId);

        if (!$isCreator && ($mine === null || $mine['status'] !== 'approved')) {
            $this->abort(403, 'Only participants can post messages.');
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
        $this->verifyCsrf();

        $model = new Comment();
        $comment = $model->findById((int) $id);
        if ($comment === null) { $this->abort(404, 'Comment not found'); }

        $this->requireOwner((int) $comment['user_id']);

        $model->delete((int) $id);
        $this->redirect('/sessions/' . (int) $comment['session_id']);
    }
}