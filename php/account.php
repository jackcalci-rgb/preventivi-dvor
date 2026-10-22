<?php
// Preventivi Dvor – own password for everyone; user management for administrators.
declare(strict_types=1);
require __DIR__ . '/lib.php';
security_headers();
$user = current_user();
if (!$user) { header('Location: login.php'); exit; }
$admin = $user['role'] === 'admin';
?><!doctype html>
<html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $admin ? 'Utenti e account' : 'Il mio account' ?> · Preventivi Dvor</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css"></head>
<body><div class="wrap">
<div class="top"><a href="index.php">← Torna ai preventivi</a><span>Accesso come <strong><?= e($user['name'] ?: $user['username']) ?></strong> · <button class="ghost" id="logout">Esci</button></span></div>
<h1><?= $admin ? 'Utenti e account' : 'Il mio account' ?></h1>

<div class="card">
  <h2 style="margin-top:0">Cambia la tua password</h2>
  <form id="pw" class="grid">
    <div><label for="cur">Password attuale</label><input id="cur" type="password" autocomplete="current-password" required></div>
    <div><label for="new1">Nuova password (min. 8)</label><input id="new1" type="password" autocomplete="new-password" required></div>
    <div><label for="new2">Ripeti la nuova password</label><input id="new2" type="password" autocomplete="new-password" required></div>
    <div><button>Salva password</button></div>
  </form>
  <p class="msg" id="pwmsg" role="status"></p>
</div>

<?php if ($admin): ?>
<h2>Dipendenti</h2>
<div class="card">
  <div class="scroll"><table><thead><tr><th>Utente</th><th>Nome</th><th>Ruolo</th><th>Stato</th><th></th></tr></thead><tbody id="list"></tbody></table></div>
  <p class="msg" id="listmsg" role="status"></p>
  <h2>Aggiungi un utente</h2>
  <form id="add" class="grid">
    <div><label for="a_user">Nome utente</label><input id="a_user" required autocomplete="off"></div>
    <div><label for="a_name">Nome e cognome</label><input id="a_name" autocomplete="off"></div>
    <div><label for="a_pw">Password (min. 8)</label><input id="a_pw" type="text" required autocomplete="off"></div>
    <div><label for="a_role">Ruolo</label><select id="a_role"><option value="staff">Dipendente</option><option value="admin">Amministratore</option></select></div>
    <div><button>Aggiungi</button></div>
  </form>
  <p class="msg" id="addmsg" role="status"></p>
</div>
<?php endif; ?>
</div>
<script>
const ME = <?= (int)$user['id'] ?>;
async function api(r, method = "GET", body){
  const res = await fetch("api.php?r=" + r, { method, credentials: "same-origin", headers: { "Content-Type": "application/json", "X-Dvor": "1" }, body: body ? JSON.stringify(body) : undefined });
  const d = await res.json().catch(() => ({}));
  if (res.status === 401) { location.href = "login.php"; throw new Error("Sessione scaduta"); }
  if (!res.ok) throw new Error(d.error || "Errore del server");
  return d;
}
const $ = id => document.getElementById(id);
function say(id, text, ok){ const m = $(id); m.textContent = text; m.className = "msg " + (ok ? "ok" : "err"); }
const esc = s => String(s).replace(/[&<>"']/g, c => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));

$("logout").onclick = async () => { await api("logout", "POST").catch(() => {}); location.href = "login.php"; };
$("pw").onsubmit = async e => {
  e.preventDefault();
  if ($("new1").value !== $("new2").value) return say("pwmsg", "Le due password non coincidono");
  try { await api("password", "POST", { current: $("cur").value, password: $("new1").value }); e.target.reset(); say("pwmsg", "Password cambiata", true); }
  catch (err) { say("pwmsg", err.message); }
};

if ($("list")) {
  let users = [];
  const render = () => {
    $("list").innerHTML = users.map(u => `<tr data-id="${u.id}">
      <td class="${u.active ? "" : "off"}">${esc(u.username)}${u.id === ME ? ' <span class="tag">tu</span>' : ""}</td>
      <td>${esc(u.name)}</td>
      <td><select data-a="role" aria-label="Ruolo di ${esc(u.username)}"><option value="staff" ${u.role === "staff" ? "selected" : ""}>Dipendente</option><option value="admin" ${u.role === "admin" ? "selected" : ""}>Amministratore</option></select></td>
      <td>${u.active ? "Attivo" : "Bloccato"}</td>
      <td><div class="acts">
        <button class="ghost" data-a="pw">Nuova password</button>
        ${u.id === ME ? "" : `<button class="ghost" data-a="active">${u.active ? "Blocca" : "Sblocca"}</button><button class="danger" data-a="del">Elimina</button>`}
      </div></td></tr>`).join("");
  };
  const load = async () => { try { users = await api("users"); render(); } catch (err) { say("listmsg", err.message); } };
  $("list").addEventListener("change", async e => {
    if (e.target.dataset.a !== "role") return;
    const id = +e.target.closest("tr").dataset.id;
    try { await api("user&id=" + id, "POST", { role: e.target.value }); say("listmsg", "Ruolo aggiornato", true); } catch (err) { say("listmsg", err.message); }
    load();
  });
  $("list").addEventListener("click", async e => {
    const b = e.target.closest("button[data-a]"); if (!b) return;
    const id = +b.closest("tr").dataset.id, u = users.find(x => x.id === id);
    try {
      if (b.dataset.a === "pw") {
        const pw = prompt(`Nuova password per ${u.username} (almeno 8 caratteri):`); if (!pw) return;
        await api("user&id=" + id, "POST", { password: pw }); say("listmsg", `Password di ${u.username} cambiata. Le sue sessioni aperte sono state chiuse.`, true);
      } else if (b.dataset.a === "active") {
        await api("user&id=" + id, "POST", { active: !u.active }); say("listmsg", `${u.username} ${u.active ? "bloccato" : "sbloccato"}`, true);
      } else if (b.dataset.a === "del") {
        if (!confirm(`Eliminare l'utente ${u.username}? I preventivi restano.`)) return;
        await api("user&id=" + id, "DELETE"); say("listmsg", `${u.username} eliminato`, true);
      }
    } catch (err) { say("listmsg", err.message); }
    load();
  });
  $("add").onsubmit = async e => {
    e.preventDefault();
    try { await api("user", "POST", { username: $("a_user").value, name: $("a_name").value, password: $("a_pw").value, role: $("a_role").value }); e.target.reset(); say("addmsg", "Utente aggiunto", true); load(); }
    catch (err) { say("addmsg", err.message); }
  };
  load();
}
</script>
</body></html>
