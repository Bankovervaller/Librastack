<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
 
    // Start session early for authentication and CSRF
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // Secure session cookie settings; adjust for production HTTPS
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }

    require 'inc/config.inc.php';
    require_once 'models/Book.php';
    require_once 'models/GoogleBooks.php';
    require_once 'controllers/BookController.php';
    // Load user components
    require_once 'models/User.php';
    require_once 'controllers/UserController.php';

    // Helper: generate CSRF token if absent
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Route: dedicated controller/action front-controller
    if (isset($_GET['controller']) && isset($_GET['action'])) {
        $controller = $_GET['controller'];
        $action = $_GET['action'];

        if ($controller === 'book') {
            $ctr = new BookController();
            if ($action === 'autocomplete') {
                $ctr->autocomplete();
                exit;
            }
            if ($action === 'googleBooks') {
                $ctr->googleBooks();
                exit;
            }
        } elseif ($controller === 'user') {
            $uctr = new UserController();
            // GET forms
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                if ($action === 'login') { $uctr->showLoginForm(); exit; }
                if ($action === 'register') { $uctr->showRegisterForm(); exit; }
                if ($action === 'profile') { $uctr->showProfile(); exit; }
                if ($action === 'forgot') { $uctr->showForgotForm(); exit; }
                if ($action === 'reset') { $uctr->showResetForm($_GET['token'] ?? ''); exit; }
            }
            // POST handlers
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($action === 'login') { $uctr->login($_POST); exit; }
                if ($action === 'register') { $uctr->register($_POST); exit; }
                if ($action === 'logout') { $uctr->logout($_POST); exit; }
                if ($action === 'profile') { $uctr->updateProfile($_POST); exit; }
                if ($action === 'forgot') { $uctr->forgot($_POST); exit; }
                if ($action === 'reset') { $uctr->reset($_POST); exit; }
            }
        }
        // Unknown controller/action falls through to default routing below
    }

    // Legacy routing
    //laad de Bookcontroller
    $ctr = new BookController();

    //VERWIJDEREN
    if (isset($_GET['verwijder']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $ctr->deleteBook($_POST['id']);
    } elseif (isset($_GET['verwijder'])) {
        $ctr->confirmDeleteBook($_GET['verwijder']);
    } elseif (isset($_GET['voegtoe'])) {
        if (isset($_POST['knop'])) {
            $ctr->newBook($_POST['naam'], $_POST['auteur'], $_POST['isbn']);
        } else {
            $ctr->showNewBookForm($_GET['voegtoe']);
        }
    } elseif (isset($_GET['pasaan'])) {
        if (isset($_POST['aanpasknop'])) {
            $ctr->updateBook($_POST['id'], $_POST['naam'], $_POST['auteur'], $_POST['isbn']);
        } else {
            $ctr->showUpdateForm($_GET['pasaan']);
        }
    } elseif (isset($_GET['id'])) {
        $ctr->showBook($_GET['id']);
    } else {
        $ctr->index();
    }
    ?>
