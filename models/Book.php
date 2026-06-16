<?php

class Book
{
    public $id = null;
    public $title = "";
    public $author = "";
    public $isbn = "";
    public $date_added = "";
    // Added: owner
    public $user_id = null;

    public function load($id)
    {
        // Zorg dat de databaseverbinding gebruikt kan worden
        global $pdo;
        // Zoek de gegevens in de database
        $query = "SELECT * FROM mvc_boeken WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['id' => $id]);
        // Is er een boek met dit id?
        if ($stmt->rowCount() == 1) {
            // Lees de data van het boek uit
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            // Vul de properties van dit object
            $this->id = $id;
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->isbn = $row['isbn'];
            $this->date_added = $row['date_added'];
            // Added: owner
            $this->user_id = isset($row['user_id']) ? (int)$row['user_id'] : null;
        } else {
            throw new Exception("Kan het boek met id {$id} niet vinden!");
        }
    }

    public function saveNew()
    {
        global $pdo;
        $this->title = htmlspecialchars($this->title);
        $this->author = htmlspecialchars($this->author);

        // Determine owner; require session user
        $ownerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        if ($ownerId <= 0) {
            throw new Exception('Niet ingelogd of ongeldig gebruiker');
        }
        // Verify that the user exists to satisfy FK constraint
        $check = $pdo->prepare('SELECT 1 FROM users WHERE id = :id');
        $check->bindValue(':id', $ownerId, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() === false) {
            throw new Exception('Gebruiker bestaat niet (FK)');
        }
        $this->user_id = $ownerId;

        $query = "INSERT INTO mvc_boeken (title, author, isbn, date_added, user_id) VALUES (:title, :author, :isbn, :date_added, :user_id)";
        $stmt = $pdo->prepare($query);

        if ($stmt->execute([
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'date_added' => date('Y-m-d H:i:s'),
            'user_id' => $this->user_id
        ])) {
            $this->id = $pdo->lastInsertId();
            return true;
        } else {
            throw new Exception("Kan het boek niet toevoegen aan de database!");
        }
    }

    public function showAll()
    {
        global $pdo;

        // Maak een Array van alle boeken
        $boeken = [];
        // Lees de id's van alle boeken
        $query = "SELECT id FROM mvc_boeken ORDER BY id";
        $stmt = $pdo->query($query);
        // Zijn er boeken gevonden?
        if ($stmt->rowCount() > 0) {
            // Voeg alle boeken toe aan de array
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $bookAdd = new Book();
                $bookAdd->load($row['id']);
                $boeken[] = $bookAdd;
            }
        }
        return $boeken;
    }

    public function delete()
    {
        global $pdo;
        // Enforce ownership on delete
        $ownerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $query = "DELETE FROM mvc_boeken WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        if ($stmt->execute(['id' => $this->id, 'user_id' => $ownerId])) {
            return $stmt->rowCount() > 0;
        } else {
            return false;
        }
    }


    public function update()
    {
        global $pdo;
        // Schoon de data op
        $this->title = htmlspecialchars($this->title);
        $this->author = htmlspecialchars($this->author);
        // Enforce ownership on update
        $ownerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        // Maak de query
        $query = "UPDATE mvc_boeken SET title = :title, author = :author, isbn = :isbn WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        // Voer de query uit
        return $stmt->execute([
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'id' => $this->id,
            'user_id' => $ownerId
        ]);
    }

    // Added: search with filters, sorting, pagination, returns [results, total]
    public static function search($q = '', $filters = [], $sort = 'title', $dir = 'ASC', $page = 1, $limit = 10)
    {
        global $pdo;

        $allowedSort = ['title' => 'title', 'author' => 'author', 'isbn' => 'isbn', 'date_added' => 'date_added'];
        $sortColumn = $allowedSort[$sort] ?? 'title';
        $dirUpper = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $page = max(1, (int)$page);
        $limit = max(1, min(100, (int)$limit));
        $offset = ($page - 1) * $limit;

        $where = [];
        $params = [];

        // Scope to current user
        $ownerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if (!$ownerId) {
            // No user: return empty result
            return ['results' => [], 'total' => 0, 'page' => $page, 'limit' => $limit];
        }
        $where[] = 'user_id = :user_id';
        $params['user_id'] = $ownerId;

        if ($q !== '') {
            $like = '%' . $q . '%';
            $params['like_title'] = $like;
            $params['like_author'] = $like;
            $params['like_isbn'] = $like;
            $where[] = '(title LIKE :like_title OR author LIKE :like_author OR isbn LIKE :like_isbn)';
        }

        // Note: Optional filters (year/genre) are not active yet; do NOT add params unless included in WHERE.
        // When enabling filters, add both the WHERE clause and matching params.

        $whereSql = '';
        if (count($where) > 0) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        // Total count
        $countSql = "SELECT COUNT(*) AS cnt FROM mvc_boeken $whereSql";
        $countStmt = $pdo->prepare($countSql);
        foreach ($params as $key => $val) {
            $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $countStmt->bindValue(':' . $key, $val, $paramType);
        }
        $countStmt->execute();
        $total = (int)$countStmt->fetchColumn();

        // Data query with positional params for LIMIT/OFFSET
        $dataSql = "SELECT id FROM mvc_boeken $whereSql ORDER BY $sortColumn $dirUpper LIMIT :limit OFFSET :offset";
        $dataStmt = $pdo->prepare($dataSql);
        foreach ($params as $key => $val) {
            $paramType = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $dataStmt->bindValue(':' . $key, $val, $paramType);
        }
        $dataStmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $dataStmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $dataStmt->execute();

        $results = [];
        while ($row = $dataStmt->fetch(PDO::FETCH_ASSOC)) {
            $book = new Book();
            $book->load($row['id']);
            $results[] = $book;
        }

        return ['results' => $results, 'total' => $total, 'page' => $page, 'limit' => $limit];
    }

    // Added: suggest for autocomplete, returns up to $limit suggestions
    public static function suggest($q, $limit = 10)
    {
        global $pdo;
        $q = trim(substr($q ?? '', 0, 200));
        if (strlen($q) < 2) {
            return [];
        }
        $prefix = $q . '%';

        // Scope to current user
        $ownerId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if (!$ownerId) { return []; }

        // Prefer title matches, then author, then exact/prefix ISBN
        $sql = "SELECT title AS value, 'title' AS type FROM mvc_boeken WHERE user_id = :user_id AND title LIKE :prefix
                UNION
                SELECT author AS value, 'author' AS type FROM mvc_boeken WHERE user_id = :user_id AND author LIKE :prefix
                UNION
                SELECT isbn AS value, 'isbn' AS type FROM mvc_boeken WHERE user_id = :user_id AND isbn LIKE :prefix
                LIMIT :limit";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':user_id', $ownerId, PDO::PARAM_INT);
        $stmt->bindValue(':prefix', $prefix, PDO::PARAM_STR);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $suggestions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Basic sanitation; view/controller will JSON-encode
            $suggestions[] = [
                'type' => $row['type'],
                'value' => $row['value']
            ];
        }
        return $suggestions;
    }
}
