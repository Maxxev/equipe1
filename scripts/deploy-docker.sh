#!/usr/bin/env bash
# Script de déploiement - variante Docker (staging)
# A executer depuis le clone dédié (ex: /var/www/laravel-docker), par l'utilisateur deploy.
#
# Usage: ./scripts/deploy-docker.sh [sha-ou-branche]
# Sans argument, deploie origin/main.
#
# Equivalent de scripts/deploy.sh, mais pour docker-compose.yml +
# docker-compose.staging.yml. Contrairement à la fiche 4 d'origine, on ne
# rebuild pas une image immuable à chaque déploiement : le code est monté
# dans le conteneur (bind-mount, comme en dev), donc "déployer" = mettre à
# jour le code sur l'hôte, réinstaller les dépendances DANS le conteneur,
# puis relancer les caches/migrations. Voir personnel/deploiement-continu/
# 04-DEPLOIEMENT-DOCKER.md §0bis pour le détail de cette adaptation.

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/laravel-docker}"
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.staging.yml"
SERVICES="app nginx mysql"
TARGET="${1:-origin/main}"
LOCKFILE="/tmp/deploy-laravel-docker.lock"

# Empeche deux deploiements en meme temps
exec 200>"$LOCKFILE"
if ! flock -n 200; then
  echo "Un deploiement est deja en cours, on arrete."
  exit 1
fi

cd "$APP_DIR"

SHA_AVANT=$(git rev-parse HEAD)
echo "Version actuelle: ${SHA_AVANT:0:8}"

echo "Recuperation du code..."
git fetch --all --prune
git reset --hard "$TARGET"
SHA_APRES=$(git rev-parse HEAD)
echo "Nouvelle version: ${SHA_APRES:0:8}"

echo "Construction/mise a jour des images (app, nginx)..."
$COMPOSE build app nginx

echo "Demarrage des services de staging (sans le conteneur node)..."
$COMPOSE up -d --remove-orphans $SERVICES

echo "Attente que mysql soit pret..."
for tentative in $(seq 1 20); do
  if $COMPOSE exec -T mysql mysqladmin ping -h localhost --silent >/dev/null 2>&1; then
    echo "  mysql repond."
    break
  fi
  echo "  ... tentative $tentative"
  sleep 3
done

echo "Installation des dependances PHP (dans le conteneur app)..."
$COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction

echo "Compilation des assets (via le conteneur node)..."
$COMPOSE run --rm --entrypoint "" node npm ci --no-audit --no-fund
$COMPOSE run --rm --entrypoint "" node npm run build

echo "Migrations..."
$COMPOSE exec -T app php artisan migrate --force

echo "Reconstruction des caches..."
$COMPOSE exec -T app php artisan config:clear
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan view:cache
$COMPOSE exec -T app php artisan storage:link || true

# Permissions : composer/npm ci-dessus tournent en root dans le conteneur,
# storage/ et bootstrap/cache doivent rester ecrivables par www-data (php-fpm).
$COMPOSE exec -T -u root app sh -c \
  'chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache'

echo "$SHA_APRES" | $COMPOSE exec -T app sh -c 'cat > VERSION'
date -u +"%Y-%m-%dT%H:%M:%SZ" | $COMPOSE exec -T app sh -c 'cat > DEPLOYED_AT'

echo "Verification de /health..."
CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 http://127.0.0.1:8001/health || echo "000")
if [ "$CODE" = "200" ]; then
  echo "OK: /health repond 200"
else
  echo "Attention: /health a repondu $CODE"
  $COMPOSE logs --tail=60 app
fi

echo "Nettoyage des images inutilisees..."
docker image prune -f --filter "until=168h" >/dev/null 2>&1 || true

echo "Deploiement Docker termine: ${SHA_AVANT:0:8} -> ${SHA_APRES:0:8}"
echo "Pour revenir en arriere: ./scripts/deploy-docker.sh $SHA_AVANT"
