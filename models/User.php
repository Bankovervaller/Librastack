<?php

class User
{
    public $id = null;
    public $email = '';
    public $display_name = '';
    public $password_hash = '';
    public $created_at = '';
    public $updated_at = '';
    public $last_login_at = '';
    public $is_active = 1;
    public $reset_token = null;
    public $reset_token_expires = null;

    private static function now()
    {
        return date('Y-m-d H:i:s');
    }

    public static function findByEmail($email)
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::fromRow($row) : null;
    }

    public static function findById($id)
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::fromRow($row) : null;
    }

    public static function findByResetToken($token)
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE reset_token = :token AND reset_token_expires > :now LIMIT 1');
        $stmt->execute(['token' => $token, 'now' => self::now()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::fromRow($row) : null;
    }

    private static function fromRow(array $row)
    {
        $u = new User();
        $u->id = (int)$row['id'];
        $u->email = $row['email'];
        $u->display_name = $row['display_name'];
        $u->password_hash = $row['password_hash'];
        $u->created_at = $row['created_at'];
        $u->updated_at = $row['updated_at'] ?? null;
        $u->last_login_at = $row['last_login_at'] ?? null;
        $u->is_active = (int)$row['is_active'];
        $u->reset_token = $row['reset_token'] ?? null;
        $u->reset_token_expires = $row['reset_token_expires'] ?? null;
        return $u;
    }

    public static function create($email, $password, $displayName)
    {
        global $pdo;
        $email = trim(substr($email, 0, 254));
        $displayName = trim(substr($displayName, 0, 100));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Ongeldig e-mailadres');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Wachtwoord te kort');
        }
        if (strlen($displayName) < 2) {
            throw new InvalidArgumentException('Naam te kort');
        }
        // Ensure unique email
        $existsStmt = $pdo->prepare('SELECT 1 FROM users WHERE email = :email');
        $existsStmt->execute(['email' => $email]);
        if ($existsStmt->fetchColumn()) {
            throw new RuntimeException('E-mailadres is al in gebruik');
        }
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, display_name, created_at, is_active) VALUES (:email, :hash, :name, :created, 1)');
        $ok = $stmt->execute([
            'email' => $email,
            'hash' => $hash,
            'name' => $displayName,
            'created' => self::now(),
        ]);
        if (!$ok) { throw new RuntimeException('Kan gebruiker niet aanmaken'); }
        $id = (int)$pdo->lastInsertId();
        return self::findById($id);
    }

    public function verifyPassword($password)
    {
        return password_verify($password, $this->password_hash);
    }

    public function markLogin()
    {
        global $pdo;
        $this->last_login_at = self::now();
        $stmt = $pdo->prepare('UPDATE users SET last_login_at = :ts WHERE id = :id');
        $stmt->execute(['ts' => $this->last_login_at, 'id' => $this->id]);
    }

    public function updateProfile($displayName)
    {
        global $pdo;
        $displayName = trim(substr($displayName, 0, 100));
        if (strlen($displayName) < 2) {
            throw new InvalidArgumentException('Naam te kort');
        }
        $this->display_name = $displayName;
        $stmt = $pdo->prepare('UPDATE users SET display_name = :name, updated_at = :updated WHERE id = :id');
        return $stmt->execute(['name' => $displayName, 'updated' => self::now(), 'id' => $this->id]);
    }

    public function setResetToken()
    {
        global $pdo;
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 1800); // 30 minutes
        $this->reset_token = $token;
        $this->reset_token_expires = $expires;
        $stmt = $pdo->prepare('UPDATE users SET reset_token = :token, reset_token_expires = :exp WHERE id = :id');
        $stmt->execute(['token' => $token, 'exp' => $expires, 'id' => $this->id]);
        return $token;
    }

    public function clearResetToken()
    {
        global $pdo;
        $this->reset_token = null;
        $this->reset_token_expires = null;
        $stmt = $pdo->prepare('UPDATE users SET reset_token = NULL, reset_token_expires = NULL WHERE id = :id');
        $stmt->execute(['id' => $this->id]);
    }

    public function resetPassword($newPassword)
    {
        global $pdo;
        if (strlen($newPassword) < 8) {
            throw new InvalidArgumentException('Wachtwoord te kort');
        }
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->password_hash = $hash;
        $stmt = $pdo->prepare('UPDATE users SET password_hash = :hash, updated_at = :updated WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'updated' => self::now(), 'id' => $this->id]);
        $this->clearResetToken();
        return true;
    }
}

