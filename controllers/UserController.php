<?php

class UserController
{
    private function render($view, $vars = [])
    {
        extract($vars);
        include $view;
    }

    private function requireCsrf($post)
    {
        if (!isset($_SESSION['csrf_token']) || !isset($post['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $post['csrf_token'])) {
            http_response_code(400);
            echo 'Ongeldige CSRF-token';
            exit;
        }
    }

    private function currentUser()
    {
        if (!empty($_SESSION['user_id'])) {
            return User::findById((int)$_SESSION['user_id']);
        }
        return null;
    }

    private function requireAuth()
    {
        $user = $this->currentUser();
        if (!$user) {
            header('Location: index.php?controller=user&action=login');
            exit;
        }
        return $user;
    }

    public function showLoginForm()
    {
        $pageTitle = 'Inloggen';
        $this->render('views/users/login.php', compact('pageTitle'));
    }

    public function login($post)
    {
        $this->requireCsrf($post);
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $user = User::findByEmail($email);
        if (!$user || !$user->verifyPassword($password) || !$user->is_active) {
            $error = 'Onjuiste inloggegevens';
            $pageTitle = 'Inloggen';
            $this->render('views/users/login.php', compact('error', 'pageTitle'));
            return;
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $user->markLogin();
        header('Location: index.php');
        exit;
    }

    public function logout($post)
    {
        $this->requireCsrf($post);
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: index.php');
        exit;
    }

    public function showRegisterForm()
    {
        $pageTitle = 'Registreren';
        $this->render('views/users/register.php', compact('pageTitle'));
    }

    public function register($post)
    {
        $this->requireCsrf($post);
        $email = trim($post['email'] ?? '');
        $password = $post['password'] ?? '';
        $confirm = $post['confirm_password'] ?? '';
        $displayName = trim($post['display_name'] ?? '');
        if ($password !== $confirm) {
            $error = 'Wachtwoorden komen niet overeen';
            $pageTitle = 'Registreren';
            $this->render('views/users/register.php', compact('error', 'pageTitle'));
            return;
        }
        try {
            $user = User::create($email, $password, $displayName);
            $_SESSION['user_id'] = $user->id;
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
            $pageTitle = 'Registreren';
            $this->render('views/users/register.php', compact('error', 'pageTitle'));
        }
    }

    public function showProfile()
    {
        $user = $this->requireAuth();
        $pageTitle = 'Profiel';
        $this->render('views/users/profile.php', compact('user', 'pageTitle'));
    }

    public function updateProfile($post)
    {
        $this->requireCsrf($post);
        $user = $this->requireAuth();
        $displayName = trim($post['display_name'] ?? '');
        // Initialize to avoid undefined variable warnings when using compact()
        $message = null;
        $error = null;
        try {
            $user->updateProfile($displayName);
            $message = 'Profiel bijgewerkt';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
        $pageTitle = 'Profiel';
        $this->render('views/users/profile.php', compact('user', 'message', 'error', 'pageTitle'));
    }

    public function showForgotForm()
    {
        $pageTitle = 'Wachtwoord vergeten';
        $this->render('views/users/forgot.php', compact('pageTitle'));
    }

    public function forgot($post)
    {
        $this->requireCsrf($post);
        $email = trim($post['email'] ?? '');
        $user = User::findByEmail($email);
        if ($user && $user->is_active) {
            $token = $user->setResetToken();
            // In productie: stuur e-mail. In dev: toon geen details.
        }
        $message = 'Als het e-mailadres bestaat, is een reset-link verstuurd.';
        $pageTitle = 'Wachtwoord vergeten';
        $this->render('views/users/forgot.php', compact('message', 'pageTitle'));
    }

    public function showResetForm($token)
    {
        $pageTitle = 'Wachtwoord herstellen';
        $tokenValid = User::findByResetToken($token) !== null;
        $this->render('views/users/reset.php', compact('token', 'tokenValid', 'pageTitle'));
    }

    public function reset($post)
    {
        $this->requireCsrf($post);
        $token = $post['token'] ?? '';
        $password = $post['password'] ?? '';
        $confirm = $post['confirm_password'] ?? '';
        if ($password !== $confirm) {
            $error = 'Wachtwoorden komen niet overeen';
            $pageTitle = 'Wachtwoord herstellen';
            $tokenValid = true;
            $this->render('views/users/reset.php', compact('error', 'token', 'tokenValid', 'pageTitle'));
            return;
        }
        $user = User::findByResetToken($token);
        if (!$user) {
            $error = 'Ongeldige of verlopen token';
            $pageTitle = 'Wachtwoord herstellen';
            $tokenValid = false;
            $this->render('views/users/reset.php', compact('error', 'token', 'tokenValid', 'pageTitle'));
            return;
        }
        try {
            $user->resetPassword($password);
            $message = 'Wachtwoord aangepast. U kunt nu inloggen.';
            $pageTitle = 'Wachtwoord herstellen';
            $tokenValid = false;
            $this->render('views/users/reset.php', compact('message', 'token', 'tokenValid', 'pageTitle'));
        } catch (Exception $e) {
            $error = $e->getMessage();
            $pageTitle = 'Wachtwoord herstellen';
            $tokenValid = true;
            $this->render('views/users/reset.php', compact('error', 'token', 'tokenValid', 'pageTitle'));
        }
    }
}

