#!/usr/bin/env bash
# Script de déploiement - serveur de staging
# A executer depuis /var/www/laravel, par l'utilisateur deploy.
#
# Usage: ./scripts/deploy.sh [sha-ou-branche]
# Sans argument, deploie origin/main.

set -euo pipefail

APP_DIR="/var/www/laravel"
PHP_FPM_SERVICE="php8.4-fpm"
QUEUE_SERVICE="laravel-queue.service"
TARGET="${1:-origin/main}"
LOCKFILE="/tmp/deploy-laravel.lock"

# Empeche deux deploiements en meme temps
exec 200>"$LOCKFILE"
if ! flock -n 200; then
  echo "Un deploiement est deja en cours, on arrete."
  exit 1
fi

cd "$APP_DIR"

# Si quelque chose plante, on remet le site en ligne quand meme
remettre_en_ligne() {
  code=$?
  php artisan up >/dev/null 2>&1 || true
  exit "$code"
}
trap remettre_en_ligne EXIT

SHA_AVANT=$(git rev-parse HEAD)
echo "Version actuelle: ${SHA_AVANT:0:8}"

echo "Mise en maintenance..."
php artisan down --retry=60 || true

echo "Recuperation du code..."
git fetch --all --prune
git reset --hard "$TARGET"
SHA_APRES=$(git rev-parse HEAD)
echo "Nouvelle version: ${SHA_APRES:0:8}"

echo "Installation des dependances PHP..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "Compilation des assets..."
npm ci --no-audit --no-fund
npm run build

echo "Migrations..."
php artisan migrate --force

echo "Reconstruction des caches..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link 2>/dev/null || true

echo "Redemarrage des services..."
php artisan queue:restart >/dev/null 2>&1 || true
sudo systemctl restart "$QUEUE_SERVICE" 2>/dev/null || true
sudo systemctl reload "$PHP_FPM_SERVICE"

git rev-parse HEAD > "$APP_DIR/VERSION"
date -u +"%Y-%m-%dT%H:%M:%SZ" > "$APP_DIR/DEPLOYED_AT"

echo "Sortie du mode maintenance..."
php artisan up

echo "Verification de /health..."
CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 http://127.0.0.1/health || echo "000")
if [ "$CODE" = "200" ]; then
  echo "OK: /health repond 200"
else
  echo "Attention: /health a repondu $CODE (le smoke test du pipeline verifiera)"
fi

echo "Deploiement termine: ${SHA_AVANT:0:8} -> ${SHA_APRES:0:8}"
echo "Pour revenir en arriere: ./scripts/deploy.sh $SHA_AVANT"

trap - EXIT
