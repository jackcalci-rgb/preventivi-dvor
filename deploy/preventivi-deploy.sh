#!/bin/bash
# Aggiorna preventivi.profumidistria.com da GitHub (da lanciare come root, anche da cron)
set -e
# cron parte con un PATH ridotto: runuser e su stanno in /usr/sbin e /sbin
export PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

U=profumidistria
R=/home/$U/preventivi-dvor
W=/home/$U/domains/preventivi.profumidistria.com/public_html

# esegue un comando come utente del dominio, con quello che c'è sul server
as_user() {
  if [ "$(id -un)" = "$U" ]; then "$@"
  elif command -v runuser >/dev/null; then runuser -u "$U" -- "$@"
  elif command -v sudo >/dev/null; then sudo -u "$U" -- "$@"
  else su -s /bin/bash "$U" -c "$(printf '%q ' "$@")"
  fi
}

[ -d "$R" ] || as_user git clone -q https://github.com/jackcalci-rgb/preventivi-dvor.git "$R"
as_user git -C "$R" pull -q
as_user rsync -rlt --exclude data/ "$R/php/" "$W/"
