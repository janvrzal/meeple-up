<?php

class AuthController extends Controller
{
    public function showRegister(): void{
        $this->requireGuest();
        $this->render('auth/register');
    }

    public function register(): void{
        $this->requireGuest();
        if(!Csrf::check($_POST['csrf_token'] ?? null)){
            http_response_code(419);
            exit("Invalid CSRF token");
        }

        $username = trim($_POST['username'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $password = $_POST['password'] ?? "";

        $errors = [];

        if ($username === '' || mb_strlen($username) > 50) {
            $errors[] = 'Username cannot be longer than 50 characters';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Unknown email';
        }
        if (mb_strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        $userModel = new User();

        if ($userModel->findByEmail($email) !== null) {
            $errors[] = 'Email is already in use';
        }
        if ($userModel->findByUsername($username) !== null) {
            $errors[] = 'Username is already taken';
        }

        if ($errors) {
            $this->render('auth/register', [
                'errors'   => $errors,
                'username' => $username,
                'email'    => $email,
            ]);
            return;
        }

        $id = $userModel->create($username, $email, $password);
        Auth::login(['id' => $id]);
        $this->redirect('/');
    }

    public function showLogin(): void{
        $this->requireGuest();
        $this->render('auth/login');
    }

    public function login(): void{
        $this->requireGuest();
        if(!Csrf::check($_POST['csrf_token'] ?? null)){
            http_response_code(419);
            exit("Invalid CSRF token");
        }

        $email = trim($_POST['email'] ?? "");
        $password = $_POST['password'] ?? "";

        $user = (new User())->findByEmail($email);

        $peppered = hash_hmac('sha256', $password, PEPPER);
        if ($user === null || !password_verify($peppered, $user['password_hash'])) {
            $this->render('auth/login', [
                'error' => 'Invalid email or password',
                'email' => $email,
            ]);
            return;
        }

        Auth::login($user);
        $this->redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/');
    }

}