<?php

class AccountController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $this->render('account/index', [
            'user'   => Auth::user(),
            'flash'  => $flash,
            'cities' => (new Location())->cities(),
        ]);
    }

    public function updateProfile(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $city = trim($_POST['city'] ?? '');
        (new User())->updateCity(Auth::id(), $city);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Profile updated.'];
        $this->redirect('/account');
    }

    public function updatePassword(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = (new User())->findById(Auth::id());

        $error = null;
        if (!password_verify(hash_hmac('sha256', $current, PEPPER), $user['password_hash'])) {
            $error = 'Current password is incorrect.';
        } elseif (mb_strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($new !== $confirm) {
            $error = 'New passwords do not match.';
        }

        if ($error !== null) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => $error];
        } else {
            $hash = password_hash(hash_hmac('sha256', $new, PEPPER), PASSWORD_DEFAULT);
            (new User())->updatePassword(Auth::id(), $hash);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Password changed.'];
        }

        $this->redirect('/account');
    }
}