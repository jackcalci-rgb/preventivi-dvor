<?php
// Preventivi Dvor – JSON API: login, quotes, users. Called only by the app's own pages.
declare(strict_types=1);
require __DIR__ . '/lib.php';
security_headers();
header('Content-Type: application/json; charset=utf-8');

function out(int $status, $data): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
function fail(int $status, string $msg): void { out($status, ['error' => $msg]); }
function input(): array {
    $raw = file_get_contents('php://input', false, null, 0, 2_000_000);
    $d = json_decode($raw ?: '{}', true);
    return is_array($d) ? $d : [];
}
function public_user(array $u): array {
    return ['id' => (int)$u['id'], 'username' => $u['username'], 'name' => $u['name'], 'role' => $u['role'], 'active' => (bool)$u['active'], 'created' => (int)$u['created']];
}
function hash_pw(string $pw): string { return password_hash($pw, PASSWORD_DEFAULT); }

$method = $_SERVER['REQUEST_METHOD'];
$r = $_GET['r'] ?? '';

// Requests that change data must come from our own pages: the custom header cannot be sent cross-site without a preflight.
if ($method !== 'GET') {
    if (($_SERVER['HTTP_X_DVOR'] ?? '') !== '1') fail(403, 'Richiesta non permessa');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin && parse_url($origin, PHP_URL_HOST) !== parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? ''), PHP_URL_HOST)) fail(403, 'Richiesta non permessa');
}

try {
    $db = db();

    /* ---------- first setup: create the first administrator ---------- */
    if ($r === 'setup' && $method === 'POST') {
        if (user_count() > 0) fail(403, 'Il primo amministratore esiste già');
        $b = input();
        $u = trim((string)($b['username'] ?? ''));
        $pw = (string)($b['password'] ?? '');
        if (!preg_match('/^[A-Za-z0-9._-]{3,32}$/', $u)) fail(400, 'Nome utente: 3-32 caratteri tra lettere, numeri, punto, trattino');
        if (strlen($pw) < 8) fail(400, 'La password deve avere almeno 8 caratteri');
        $db->prepare("INSERT INTO users (username, name, pass, role, created) VALUES (?, ?, ?, 'admin', ?)")
           ->execute([$u, trim((string)($b['name'] ?? '')), hash_pw($pw), time() * 1000]);
        $st = $db->prepare('SELECT * FROM users WHERE username = ?'); $st->execute([$u]);
        login_user($st->fetch());
        out(200, ['ok' => true]);
    }

    /* ---------- login / logout ---------- */
    if ($r === 'login' && $method === 'POST') {
        $ip = client_ip();
        $st = $db->prepare('SELECT n, until FROM login_attempts WHERE ip = ?'); $st->execute([$ip]);
        $a = $st->fetch();
        if ($a && (int)$a['until'] > time()) fail(429, 'Troppi tentativi. Riprova tra qualche minuto.');
        $b = input();
        $st = $db->prepare('SELECT * FROM users WHERE username = ?'); $st->execute([trim((string)($b['username'] ?? ''))]);
        $row = $st->fetch();
        $ok = $row && password_verify((string)($b['password'] ?? ''), $row['pass']) && $row['active'];
        if (!$ok) {
            $n = ($a ? (int)$a['n'] : 0) + 1;
            $until = $n >= 5 ? time() + 300 : 0;
            $db->prepare('INSERT INTO login_attempts (ip, n, until) VALUES (?, ?, ?) ON CONFLICT(ip) DO UPDATE SET n = excluded.n, until = excluded.until')
               ->execute([$ip, $until ? 0 : $n, $until]);
            fail(401, 'Utente o password non corretti');
        }
        $db->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([$ip]);
        if (password_needs_rehash($row['pass'], PASSWORD_DEFAULT)) {
            $row['pass'] = hash_pw((string)$b['password']);
            $db->prepare('UPDATE users SET pass = ? WHERE id = ?')->execute([$row['pass'], $row['id']]);
        }
        login_user($row);
        out(200, ['ok' => true, 'role' => $row['role']]);
    }
    if ($r === 'logout' && $method === 'POST') {
        start_session(); $_SESSION = []; session_destroy();
        out(200, ['ok' => true]);
    }

    /* ---------- everything below needs a logged-in user ---------- */
    $user = current_user();
    if (!$user) fail(401, 'Sessione scaduta: accedi di nuovo');

    if ($r === 'me' && $method === 'GET') out(200, $user);

    if ($r === 'password' && $method === 'POST') {
        $b = input();
        $st = $db->prepare('SELECT pass FROM users WHERE id = ?'); $st->execute([$user['id']]);
        if (!password_verify((string)($b['current'] ?? ''), (string)$st->fetchColumn())) fail(400, 'La password attuale non è corretta');
        $pw = (string)($b['password'] ?? '');
        if (strlen($pw) < 8) fail(400, 'La nuova password deve avere almeno 8 caratteri');
        $h = hash_pw($pw);
        $db->prepare('UPDATE users SET pass = ? WHERE id = ?')->execute([$h, $user['id']]);
        login_user(['id' => $user['id'], 'pass' => $h]);
        out(200, ['ok' => true]);
    }

    /* ---------- quotes (shared by all staff) ---------- */
    if ($r === 'quotes' && $method === 'GET') {
        $rows = $db->query('SELECT data FROM quotes ORDER BY updated DESC')->fetchAll();
        out(200, array_map(fn($x) => json_decode($x['data'], true), $rows));
    }
    if ($r === 'quote') {
        $id = (string)($_GET['id'] ?? '');
        if (!preg_match('/^[A-Za-z0-9_-]{1,64}$/', $id)) fail(400, 'Preventivo non valido');
        if ($method === 'POST') {
            $q = input();
            if (($q['id'] ?? null) !== $id || !isset($q['sezioni']) || !is_array($q['sezioni'])) fail(400, 'Preventivo non valido');
            $db->prepare('INSERT INTO quotes (id, data, updated, updated_by) VALUES (?, ?, ?, ?)
                          ON CONFLICT(id) DO UPDATE SET data = excluded.data, updated = excluded.updated, updated_by = excluded.updated_by')
               ->execute([$id, json_encode($q, JSON_UNESCAPED_UNICODE), time(), $user['username']]);
            out(200, ['ok' => true]);
        }
        if ($method === 'DELETE') {
            $db->prepare('DELETE FROM quotes WHERE id = ?')->execute([$id]);
            out(200, ['ok' => true]);
        }
    }

    /* ---------- quote numbers: one counter shared by all staff ---------- */
    if ($r === 'nextnumber' && $method === 'POST') {
        $db->exec('BEGIN IMMEDIATE');
        $seq = (int)setting('seq', '0');
        // never below a number already used (e.g. quotes imported from a browser)
        foreach ($db->query("SELECT json_extract(data, '$.numero') AS n FROM quotes")->fetchAll() as $row) {
            if (preg_match('/^\d{8}[A-Za-z]{2}(\d+)$/', (string)$row['n'], $m)) $seq = max($seq, (int)$m[1]);
        }
        $seq++;
        set_setting('seq', (string)$seq);
        $db->exec('COMMIT');
        out(200, ['seq' => $seq, 'letters' => setting('letters', 'PV')]);
    }
    if ($r === 'letters' && $method === 'POST') {
        $l = strtoupper(trim((string)(input()['letters'] ?? '')));
        if (!preg_match('/^[A-Z]{2}$/', $l)) fail(400, 'Servono esattamente due lettere, es. PV');
        set_setting('letters', $l);
        out(200, ['letters' => $l]);
    }

    /* ---------- users (admins only) ---------- */
    if (str_starts_with($r, 'user')) {
        if ($user['role'] !== 'admin') fail(403, 'Solo gli amministratori possono gestire gli utenti');
        $activeAdmins = fn() => (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND active = 1")->fetchColumn();

        if ($r === 'users' && $method === 'GET') {
            out(200, array_map('public_user', $db->query('SELECT * FROM users ORDER BY username COLLATE NOCASE')->fetchAll()));
        }
        if ($r === 'user' && $method === 'POST' && empty($_GET['id'])) {
            $b = input();
            $u = trim((string)($b['username'] ?? ''));
            $pw = (string)($b['password'] ?? '');
            $role = (string)($b['role'] ?? 'staff');
            if (!preg_match('/^[A-Za-z0-9._-]{3,32}$/', $u)) fail(400, 'Nome utente: 3-32 caratteri tra lettere, numeri, punto, trattino');
            if (strlen($pw) < 8) fail(400, 'La password deve avere almeno 8 caratteri');
            if (!in_array($role, ['staff', 'admin'], true)) fail(400, 'Ruolo non valido');
            $st = $db->prepare('SELECT 1 FROM users WHERE username = ?'); $st->execute([$u]);
            if ($st->fetch()) fail(409, 'Esiste già un utente con questo nome');
            $db->prepare('INSERT INTO users (username, name, pass, role, created) VALUES (?, ?, ?, ?, ?)')
               ->execute([$u, mb_substr(trim((string)($b['name'] ?? '')), 0, 80), hash_pw($pw), $role, time() * 1000]);
            out(201, ['ok' => true]);
        }
        if ($r === 'user' && !empty($_GET['id'])) {
            $id = (int)$_GET['id'];
            $st = $db->prepare('SELECT * FROM users WHERE id = ?'); $st->execute([$id]);
            $t = $st->fetch();
            if (!$t) fail(404, 'Utente non trovato');
            $isLastAdmin = $t['role'] === 'admin' && $t['active'] && $activeAdmins() <= 1;

            if ($method === 'POST') {
                $b = input();
                if (array_key_exists('password', $b)) {
                    $pw = (string)$b['password'];
                    if (strlen($pw) < 8) fail(400, 'La password deve avere almeno 8 caratteri');
                    $h = hash_pw($pw);
                    $db->prepare('UPDATE users SET pass = ? WHERE id = ?')->execute([$h, $id]);
                    if ($id === $user['id']) login_user(['id' => $id, 'pass' => $h]);
                }
                if (array_key_exists('name', $b)) $db->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([mb_substr(trim((string)$b['name']), 0, 80), $id]);
                if (array_key_exists('role', $b)) {
                    if (!in_array($b['role'], ['staff', 'admin'], true)) fail(400, 'Ruolo non valido');
                    if ($isLastAdmin && $b['role'] !== 'admin') fail(400, 'Serve almeno un amministratore attivo');
                    $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$b['role'], $id]);
                }
                if (array_key_exists('active', $b)) {
                    if ($isLastAdmin && !$b['active']) fail(400, 'Serve almeno un amministratore attivo');
                    if ($id === $user['id'] && !$b['active']) fail(400, 'Non puoi bloccare il tuo utente');
                    $db->prepare('UPDATE users SET active = ? WHERE id = ?')->execute([$b['active'] ? 1 : 0, $id]);
                }
                $st = $db->prepare('SELECT * FROM users WHERE id = ?'); $st->execute([$id]);
                out(200, public_user($st->fetch()));
            }
            if ($method === 'DELETE') {
                if ($id === $user['id']) fail(400, 'Non puoi eliminare il tuo utente');
                if ($isLastAdmin) fail(400, 'Serve almeno un amministratore attivo');
                $db->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
                out(200, ['ok' => true]);
            }
        }
    }

    fail(404, 'Non trovato');
} catch (Throwable $err) {
    error_log('[dvor] ' . $err->getMessage());
    fail(500, 'Errore del server');
}
