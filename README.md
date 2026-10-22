# Preventivi Dvor

Web app per i preventivi degli eventi privati di Osteria Dvor (Horeca FVG Srl): menu, vini, torta, totali, acconto 50%, termini e condizioni, PDF pronto da inviare in italiano o inglese.

## Cosa c'è nella repository

| Cartella / file | A cosa serve |
|---|---|
| `php/` | La versione da mettere online: login per i dipendenti, utenti gestiti dall'amministratore, preventivi salvati sul server e condivisi tra tutti. |
| `Preventivi_Dvor.html` | La stessa app in un solo file, da aprire nel browser senza server. I preventivi restano solo in quel browser. |

## Numero preventivo

Il numero è formato da data del preventivo + due lettere + numero crescente, per esempio `22102026PV001`.

- Il numero crescente è unico per tutti i dipendenti (sul server) e non riparte mai da capo.
- Se si cambia la data del preventivo, cambia anche la data nel numero.
- Le due lettere (predefinite `PV`) si cambiano nel campo "Lettere del numero".
- Il pulsante ↻ assegna il numero successivo.

## Installazione su Virtualmin (preventivi.profumidistria.com)

1. In Virtualmin crea il sottodominio `preventivi.profumidistria.com`, con PHP 8 attivo.
2. In "Server Configuration → SSL Certificate" richiedi il certificato Let's Encrypt.
3. Carica il **contenuto** della cartella `php/` dentro `public_html` del sottodominio, con File Manager o SFTP. Includi i file nascosti `.htaccess` e `data/.htaccess`.
4. Controlla che PHP abbia l'estensione **pdo_sqlite** (in Virtualmin: "System Settings → PHP Modules", oppure `php -m | grep sqlite`). Di solito è già presente.
5. Apri `https://preventivi.profumidistria.com`. Alla prima apertura la pagina chiede di creare l'**amministratore**.
6. Da "Utenti e account" l'amministratore aggiunge i dipendenti, cambia le password, li blocca o li elimina.

Il database (`dvor.sqlite`) viene creato da solo nella cartella `dvor-data`, accanto a `public_html` e fuori dalla parte pubblica del sito. Se il server non lo permette, viene usata `public_html/data`, protetta da `.htaccess`. **Per il backup basta copiare quella cartella.**

### Sicurezza

- Le password sono salvate cifrate (`password_hash`).
- Dopo 5 tentativi sbagliati l'accesso si blocca per 5 minuti.
- La sessione dura 30 giorni.
- Se un utente viene bloccato, eliminato o gli viene cambiata la password, i suoi accessi aperti si chiudono subito.
- Il sito funziona solo in HTTPS.

## Aggiornare il sito

1. Scarica le modifiche: `git pull`.
2. Ricarica su `public_html` i file di `php/`. Il database non viene toccato.
