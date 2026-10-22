<?php
// Preventivi Dvor – shared server code: database, sessions, users.
// Needs PHP 7.4+ with pdo_sqlite (standard on Virtualmin).
declare(strict_types=1);

// The database lives outside public_html when possible (one level up, in the domain's home folder).
function data_dir(): string {
    $env = getenv('DVOR_DATA_DIR');
    if ($env) return rtrim($env, '/');
    $outside = dirname(__DIR__) . '/dvor-data';
    if (is_dir($outside) || @mkdir($outside, 0700, true)) return $outside;
    return __DIR__ . '/data'; // fallback, protected by data/.htaccess
}

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $dir = data_dir();
    if (!is_dir($dir)) mkdir($dir, 0700, true);
    $pdo = new PDO('sqlite:' . $dir . '/dvor.sqlite', null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL; PRAGMA foreign_keys = ON;');
    $pdo->exec("
      CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE COLLATE NOCASE,
        name TEXT NOT NULL DEFAULT '',
        pass TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'staff' CHECK (role IN ('staff','admin')),
        active INTEGER NOT NULL DEFAULT 1,
        created INTEGER NOT NULL
      );
      CREATE TABLE IF NOT EXISTS quotes (
        id TEXT PRIMARY KEY,
        data TEXT NOT NULL,
        updated INTEGER NOT NULL,
        updated_by TEXT NOT NULL DEFAULT ''
      );
      CREATE TABLE IF NOT EXISTS settings (
        key TEXT PRIMARY KEY,
        value TEXT NOT NULL
      );
      CREATE TABLE IF NOT EXISTS login_attempts (
        ip TEXT PRIMARY KEY,
        n INTEGER NOT NULL,
        until INTEGER NOT NULL
      );
    ");
    return $pdo;
}

function start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_name('dvor_sid');
    session_set_cookie_params([
        'lifetime' => 30 * 86400,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    ini_set('session.gc_maxlifetime', (string)(30 * 86400));
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function security_headers(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');
    header('Cache-Control: no-store');
}

// The logged-in user, re-read from the database so a blocked or deleted user is out at once.
function current_user(): ?array {
    start_session();
    if (empty($_SESSION['uid'])) return null;
    $st = db()->prepare('SELECT id, username, name, role, active, pass FROM users WHERE id = ?');
    $st->execute([$_SESSION['uid']]);
    $u = $st->fetch();
    // a password change by an admin ends the other sessions of that user
    if (!$u || !$u['active'] || ($_SESSION['pv'] ?? '') !== substr(hash('sha256', $u['pass']), 0, 16)) {
        $_SESSION = [];
        return null;
    }
    return ['id' => (int)$u['id'], 'username' => $u['username'], 'name' => $u['name'], 'role' => $u['role']];
}

function login_user(array $row): void {
    start_session();
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$row['id'];
    $_SESSION['pv'] = substr(hash('sha256', $row['pass']), 0, 16);
}

function setting(string $key, string $default = ''): string {
    $st = db()->prepare('SELECT value FROM settings WHERE key = ?'); $st->execute([$key]);
    $v = $st->fetchColumn();
    return $v === false ? $default : (string)$v;
}
function set_setting(string $key, string $value): void {
    db()->prepare('INSERT INTO settings (key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value')->execute([$key, $value]);
}

function user_count(): int {
    return (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function client_ip(): string {
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
