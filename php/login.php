<?php
// Preventivi Dvor – staff login, and on first run the creation of the first administrator.
declare(strict_types=1);
require __DIR__ . '/lib.php';
security_headers();
if (current_user()) { header('Location: index.php'); exit; }
$setup = user_count() === 0;
?><!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Accesso · Preventivi Dvor</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"></head>
<body><div class="wrap"><div class="card login">
<img src="logo.png" alt="Osteria Dvor">
<?php if ($setup): ?>
  <h1>Primo accesso</h1>
  <p>Crea l'utente amministratore. Potrai poi aggiungere i dipendenti da "Utenti e account".</p>
<?php else: ?>
  <h1>Preventivi</h1>
  <p>Accesso riservato al personale.</p>
<?php endif; ?>
<form id="f">
  <?php if ($setup): ?><label for="name">Nome e cognome</label><input id="name" autocomplete="name"><?php endif; ?>
  <label for="username">Nome utente</label><input id="username" autocomplete="username" required autofocus>
  <label for="password">Password<?= $setup ? ' (almeno 8 caratteri)' : '' ?></label><input id="password" type="password" autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>" required>
  <button class="full"><?= $setup ? 'Crea amministratore' : 'Accedi' ?></button>
  <p class="msg err" id="msg" role="alert"></p>
</form>
</div></div>
<script>
document.getElementById("f").onsubmit = async e => {
  e.preventDefault();
  const v = id => (document.getElementById(id) || {}).value || "";
  const msg = document.getElementById("msg"), btn = e.target.querySelector("button");
  btn.disabled = true; msg.textContent = "";
  try {
    const res = await fetch("api.php?r=<?= $setup ? 'setup' : 'login' ?>", { method: "POST", credentials: "same-origin",
      headers: { "Content-Type": "application/json", "X-Dvor": "1" },
      body: JSON.stringify({ name: v("name"), username: v("username"), password: v("password") }) });
    const d = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(d.error || "Errore del server");
    location.href = "index.php";
  } catch (err) { msg.textContent = err.message; btn.disabled = false; }
};
</script>
</body></html>
