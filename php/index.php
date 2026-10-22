<?php
// Preventivi Dvor – the app itself, only for logged-in staff.
declare(strict_types=1);
require __DIR__ . '/lib.php';
security_headers();

$user = current_user();
if (!$user) { header('Location: login.php'); exit; }

$quotes = array_map(fn($x) => json_decode($x['data'], true), db()->query('SELECT data FROM quotes ORDER BY updated DESC')->fetchAll());
$boot = json_encode(['user' => $user, 'quotes' => $quotes, 'letters' => setting('letters', 'PV')],
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

// app.php is the app page with a first line that stops it being opened directly
$html = file_get_contents(__DIR__ . '/app.php');
$html = substr($html, strpos($html, "\n") + 1);
$inject = '<script>window.DVOR_SERVER=true;window.DVOR_DATA=' . $boot . ';</script>';
$pos = stripos($html, '<body>');
echo $pos === false ? $inject . $html : substr($html, 0, $pos + 6) . $inject . substr($html, $pos + 6);
