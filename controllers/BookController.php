<?php

class BookController
{
    private $book;

    public function __construct()
    {
        // Als de controller wordt geladen, wordt er een (leeg) Book gemaakt
        $this->book = new Book();
    }

    private function requireAuth()
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?controller=user&action=login');
            exit;
        }
    }

    public function index()
    {
        // Require login to view list
        $this->requireAuth();

        // Lees zoek- en sorteerparameters uit GET
        $q = isset($_GET['q']) ? trim(substr($_GET['q'], 0, 200)) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
        $dir = isset($_GET['dir']) ? strtoupper($_GET['dir']) : 'ASC';
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;

        // Normaliseer toelaatbare waarden
        $allowedSort = ['title', 'author', 'isbn', 'date_added'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'title'; }
        $dir = $dir === 'DESC' ? 'DESC' : 'ASC';

        // Vraag gefilterde boeken op via model search (scoped by user_id in model)
        $result = Book::search($q, /*filters*/ [], $sort, $dir, $page, $limit);
        $boekenArray = $result['results'];
        $total = $result['total'];
        $page = $result['page'];
        $limit = $result['limit'];
        $totalPages = (int)ceil($total / max(1, $limit));

        // Maak variabelen beschikbaar voor de view
        // $q, $sort, $dir, $page, $limit, $total, $totalPages, $boekenArray
        include "views/bookList.php";
    }

    public function autocomplete()
    {
        $q = isset($_GET['q']) ? trim(substr($_GET['q'], 0, 200)) : '';
        header('Content-Type: application/json; charset=utf-8');
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }
        try {
            $suggestions = Book::suggest($q, 10);
            echo json_encode($suggestions);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'server_error']);
        }
    }

    public function googleBooks()
    {
        $this->requireAuth();

        $q = isset($_GET['q']) ? trim(substr($_GET['q'], 0, 200)) : '';
        $type = isset($_GET['type']) ? $_GET['type'] : 'all';
        $allowedTypes = ['all', 'title', 'author', 'isbn'];
        if (!in_array($type, $allowedTypes, true)) {
            $type = 'all';
        }
        header('Content-Type: application/json; charset=utf-8');
        if (strlen($q) < 2) {
            echo json_encode([]);
            return;
        }

        try {
            echo json_encode(GoogleBooks::search($q, 8, $type));
        } catch (Exception $e) {
            echo json_encode([
                'error' => 'google_books_unavailable',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function showBook($id)
    {
        if (!is_null($id)) {
            $this->book->load($id);
            // Enforce ownership: only owner can view details
            if ((int)($_SESSION['user_id'] ?? 0) !== (int)($this->book->user_id ?? -1)) {
                http_response_code(403);
                echo 'Toegang geweigerd';
                exit;
            }
        }
        $boek = $this->book;
        // Laad de view 'bookDetails' om de details te tonen
        include 'views/bookDetails.php';
    }

    public function showNewBookForm()
    {
        $this->requireAuth();
        include 'views/newBookForm.php';
    }

    public function newBook($titel, $auteur, $isbn)
    {
        $this->requireAuth();
        $result = "";

        if ($titel && $auteur && $isbn) {
            $this->book->title = $titel;
            $this->book->author = $auteur;
            $this->book->isbn = htmlentities($isbn);
            // Set owner
            $this->book->user_id = (int)$_SESSION['user_id'];

            try {
                $result = $this->book->saveNew() ? "{$this->book->title} is toegevoegd." : "FOUT bij toevoegen {$this->book->title}";
            } catch (Exception $e) {
                // Provide a readable error; log as needed
                $result = "FOUT: kon het boek niet opslaan (" . htmlspecialchars($e->getMessage()) . ")";
            }
        } else {
            $result = "Niet alle eigenschappen gevuld";
        }

        include 'views/newBookResult.php';
    }

    public function deleteBook($id)
    {
        $this->requireAuth();
        if (!is_null($id)) {
            //laad het boek in dat moet worden verwijderd
            $this->book->load($id);
            // Check ownership
            if ((int)($_SESSION['user_id'] ?? 0) !== (int)($this->book->user_id ?? -1)) {
                http_response_code(403);
                $result = 'Toegang geweigerd';
            } else {
                //verwijder het boek
                if ($this->book->delete()) {
                    $result = "Boek met id {$id} is verwijderd.";
                } else {
                    $result = "FOUT bij verwijderen boek met id {$id}";
                }
            }
        } else {
            $result = "Boek met id {$id} is niet gevonden.";
        }
        include 'views/deleteBookResults.php';
    }

    public function confirmDeleteBook($id)
    {
        $this->requireAuth();
        if (!is_null($id)) {
            // Laad het boek in dat moet worden verwijderd
            $this->book->load($id);
            // Enforce ownership
            if ((int)($_SESSION['user_id'] ?? 0) !== (int)($this->book->user_id ?? -1)) {
                http_response_code(403);
                $result = 'Toegang geweigerd';
                include 'views/deleteBookResults.php';
                return;
            }
            // Maak de variabele beschikbaar voor de view
            $boek = $this->book;
            require 'views/confirmDelete.php';
        } else {
            $result = "Boek met id {$id} is niet gevonden.";
            include 'views/deleteBookResults.php';
        }
    }

    public function showUpdateForm($id)
    {
        $this->requireAuth();
        if (!is_null($id)) {
            // Laad het boek in dat moet worden aangepast
            $this->book->load($id);
            // Enforce ownership
            if ((int)($_SESSION['user_id'] ?? 0) !== (int)($this->book->user_id ?? -1)) {
                http_response_code(403);
                echo 'Toegang geweigerd';
                exit;
            }
            // Zet het boek in een object dat gebruikt wordt in de view
            $boek = $this->book;
            // Laad de view
            include 'views/updateBookForm.php';
        }
    }

    public function updateBook($id, $titel, $auteur, $isbn)
    {
        $this->requireAuth();
        $result = "";
        if (strlen($id) > 0 && strlen($titel) > 0 && strlen($auteur) > 0 && strlen($isbn) > 0) {
            // Schoon de data op en zet de waarde in het boek
            $this->book->id = $id;
            $this->book->title = $titel;
            $this->book->author = $auteur;
            $this->book->isbn = htmlentities($isbn);
            // Enforce ownership by loading and checking first
            $this->book->load($id);
            if ((int)($_SESSION['user_id'] ?? 0) !== (int)($this->book->user_id ?? -1)) {
                http_response_code(403);
                $result = 'Toegang geweigerd';
            } else if ($this->book->update()) {
                $result = $this->book->title . " is aangepast.";
            } else {
                $result = "FOUT bij aanpassen " . $this->book->title;
            }
        } else {
            $result = "Niet alle eigenschappen gevuld";
        }
        include 'views/updateBookResults.php';
    }
}
