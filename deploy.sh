#!/bin/bash
###############################################################################
# Naruay MLM — Production Deployment Script
# Server: Ubuntu + DirectAdmin + PHP 8.3 + MySQL 8 + Redis
#
# Usage:
#   First deploy : ./deploy.sh --fresh
#   Update       : ./deploy.sh
#   Rollback     : ./deploy.sh --rollback
#
# Configuration: Edit the variables below before first run
###############################################################################
set -euo pipefail
IFS=$'\n\t'

# ─── Configuration ──────────────────────────────────────────────────────────
APP_NAME="naruaynew"
DEPLOY_USER="${DEPLOY_USER:-admin}"                         # DirectAdmin user
DEPLOY_PATH="${DEPLOY_PATH:-/home/${DEPLOY_USER}/domains/example.com/laravel}"
PUBLIC_HTML="${PUBLIC_HTML:-/home/${DEPLOY_USER}/domains/example.com/public_html}"
REPO_URL="${REPO_URL:-https://github.com/xjanova/naruaynew.git}"
BRANCH="${BRANCH:-master}"
PHP_BIN="${PHP_BIN:-/usr/local/bin/php83}"                  # DirectAdmin PHP path
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"
NODE_BIN="${NODE_BIN:-/usr/bin/node}"
NPM_BIN="${NPM_BIN:-/usr/bin/npm}"
REDIS_ENABLED=true
QUEUE_WORKER=true
KEEP_RELEASES=5

# ─── Colors ──────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; NC='\033[0m'

log()   { echo -e "${GREEN}[✓]${NC} $1"; }
warn()  { echo -e "${YELLOW}[!]${NC} $1"; }
error() { echo -e "${RED}[✗]${NC} $1"; exit 1; }
info()  { echo -e "${CYAN}[→]${NC} $1"; }

# ─── Parse Arguments ────────────────────────────────────────────────────────
FRESH=false
ROLLBACK=false
SKIP_BUILD=false
MIGRATE=true

for arg in "$@"; do
    case $arg in
        --fresh)      FRESH=true ;;
        --rollback)   ROLLBACK=true ;;
        --skip-build) SKIP_BUILD=true ;;
        --no-migrate) MIGRATE=false ;;
        --help)
            echo "Usage: ./deploy.sh [OPTIONS]"
            echo "  --fresh       First-time setup (clone, install, migrate:fresh)"
            echo "  --rollback    Rollback to previous release"
            echo "  --skip-build  Skip npm build step"
            echo "  --no-migrate  Skip database migrations"
            exit 0
            ;;
    esac
done

# ─── Timer ───────────────────────────────────────────────────────────────────
SECONDS=0

echo ""
echo -e "${BLUE}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║${NC}    ${CYAN}Naruay MLM — Production Deployment${NC}            ${BLUE}║${NC}"
echo -e "${BLUE}╚══════════════════════════════════════════════════╝${NC}"
echo ""

# ─── Rollback ────────────────────────────────────────────────────────────────
if [ "$ROLLBACK" = true ]; then
    info "Rolling back to previous release..."
    PREV_RELEASE=$(ls -1dt ${DEPLOY_PATH}/releases/*/ 2>/dev/null | sed -n '2p')
    if [ -z "$PREV_RELEASE" ]; then
        error "No previous release found to rollback to."
    fi
    ln -sfn "$PREV_RELEASE" "${DEPLOY_PATH}/current"
    ln -sfn "${DEPLOY_PATH}/current/public" "$PUBLIC_HTML"
    log "Rolled back to: $(basename $PREV_RELEASE)"
    exit 0
fi

# ─── Pre-flight Checks ──────────────────────────────────────────────────────
info "Running pre-flight checks..."
command -v git  >/dev/null 2>&1 || error "git not found"
$PHP_BIN -v     >/dev/null 2>&1 || error "PHP not found at $PHP_BIN"
$COMPOSER_BIN -V >/dev/null 2>&1 || error "Composer not found at $COMPOSER_BIN"

PHP_VERSION=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
if [[ "$PHP_VERSION" < "8.2" ]]; then
    error "PHP 8.2+ required. Found: $PHP_VERSION"
fi
log "PHP $PHP_VERSION ✓"

# Check required extensions
for ext in pdo_mysql redis mbstring openssl tokenizer xml ctype json bcmath; do
    if ! $PHP_BIN -m 2>/dev/null | grep -qi "^$ext$"; then
        warn "PHP extension '$ext' not found — may be needed"
    fi
done

# ─── Directory Structure ────────────────────────────────────────────────────
RELEASE_DIR="${DEPLOY_PATH}/releases/$(date +%Y%m%d_%H%M%S)"
SHARED_DIR="${DEPLOY_PATH}/shared"

if [ "$FRESH" = true ]; then
    info "Fresh deploy — setting up directory structure..."
    mkdir -p "$DEPLOY_PATH"/{releases,shared}
    mkdir -p "$SHARED_DIR"/{storage/app/public,storage/framework/{cache/data,sessions,testing,views},storage/logs}

    # Create .env if not exists
    if [ ! -f "$SHARED_DIR/.env" ]; then
        warn ".env not found! Creating from template..."
        cat > "$SHARED_DIR/.env" << 'ENVEOF'
APP_NAME="Naruay MLM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Bangkok
APP_URL=https://your-domain.com

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=naruaynew
DB_USERNAME=naruaynew_user
DB_PASSWORD=CHANGE_ME

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

QUEUE_CONNECTION=redis
CACHE_STORE=redis
BROADCAST_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@your-domain.com"
MAIL_FROM_NAME="${APP_NAME}"
ENVEOF
        warn "⚠️  EDIT ${SHARED_DIR}/.env before continuing!"
        warn "   Set APP_KEY, DB credentials, APP_URL, MAIL settings"
        echo ""
        read -p "Press Enter after editing .env, or Ctrl+C to cancel..."
    fi
fi

# ─── Clone / Pull ───────────────────────────────────────────────────────────
info "Creating release: $(basename $RELEASE_DIR)"
mkdir -p "$RELEASE_DIR"

if [ "$FRESH" = true ]; then
    info "Cloning repository..."
    git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR"
else
    info "Cloning release from repository..."
    git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR"
fi
log "Source code ready"

# ─── Shared Symlinks ────────────────────────────────────────────────────────
info "Linking shared files..."
rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
ln -sfn "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
log "Shared storage + .env linked"

# ─── Composer Install ───────────────────────────────────────────────────────
info "Installing PHP dependencies (production)..."
cd "$RELEASE_DIR"
$PHP_BIN $COMPOSER_BIN install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    2>&1 | tail -3
log "Composer dependencies installed"

# ─── NPM Build ──────────────────────────────────────────────────────────────
if [ "$SKIP_BUILD" = false ]; then
    if command -v $NPM_BIN >/dev/null 2>&1; then
        info "Installing Node.js dependencies..."
        $NPM_BIN ci --legacy-peer-deps 2>&1 | tail -3

        info "Building frontend assets..."
        $NPM_BIN run build 2>&1 | tail -5

        # Clean node_modules after build (save disk space)
        rm -rf node_modules
        log "Frontend built & node_modules cleaned"
    else
        warn "npm not found — skipping frontend build"
        warn "Build assets locally and commit to repo instead"
    fi
else
    info "Skipping frontend build (--skip-build)"
fi

# ─── Generate App Key (first deploy) ────────────────────────────────────────
if [ "$FRESH" = true ]; then
    APP_KEY_CHECK=$(grep "^APP_KEY=" "$SHARED_DIR/.env" | cut -d= -f2)
    if [ -z "$APP_KEY_CHECK" ] || [ "$APP_KEY_CHECK" = "" ]; then
        info "Generating application key..."
        $PHP_BIN artisan key:generate --force
        log "App key generated"
    fi
fi

# ─── Laravel Optimizations ──────────────────────────────────────────────────
info "Optimizing Laravel..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache
log "Config, routes, views, events cached"

# ─── Storage Link ────────────────────────────────────────────────────────────
if [ ! -L "$RELEASE_DIR/public/storage" ]; then
    $PHP_BIN artisan storage:link 2>/dev/null || true
fi

# ─── Database Migration ─────────────────────────────────────────────────────
if [ "$MIGRATE" = true ]; then
    if [ "$FRESH" = true ]; then
        info "Running fresh migrations..."
        $PHP_BIN artisan migrate --force
    else
        info "Running migrations..."
        $PHP_BIN artisan migrate --force
    fi
    log "Database migrated"
else
    info "Skipping migrations (--no-migrate)"
fi

# ─── Activate Release ───────────────────────────────────────────────────────
info "Activating release..."
ln -sfn "$RELEASE_DIR" "${DEPLOY_PATH}/current"

# Link public directory to DirectAdmin public_html
# Remove existing public_html content and symlink
if [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
    # Backup existing public_html
    mv "$PUBLIC_HTML" "${PUBLIC_HTML}_backup_$(date +%Y%m%d_%H%M%S)" 2>/dev/null || true
fi
ln -sfn "${DEPLOY_PATH}/current/public" "$PUBLIC_HTML"
log "Release activated → public_html linked"

# ─── File Permissions ────────────────────────────────────────────────────────
info "Setting permissions..."
chmod -R 755 "$SHARED_DIR/storage"
chmod -R 755 "${DEPLOY_PATH}/current/bootstrap/cache"
log "Permissions set"

# ─── Queue Restart ───────────────────────────────────────────────────────────
if [ "$QUEUE_WORKER" = true ]; then
    info "Restarting queue workers..."
    $PHP_BIN artisan queue:restart 2>/dev/null || warn "Queue restart signal sent (ensure supervisor is running)"
fi

# ─── Cleanup Old Releases ───────────────────────────────────────────────────
info "Cleaning old releases (keeping last $KEEP_RELEASES)..."
cd "${DEPLOY_PATH}/releases"
ls -1dt */ | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
log "Old releases cleaned"

# ─── Redis Cache Clear ───────────────────────────────────────────────────────
if [ "$REDIS_ENABLED" = true ]; then
    $PHP_BIN artisan cache:clear 2>/dev/null || true
fi

# ─── Health Check ────────────────────────────────────────────────────────────
info "Running health check..."
HEALTH_STATUS=$($PHP_BIN artisan about --json 2>/dev/null | head -1 || echo "ok")
if [ -n "$HEALTH_STATUS" ]; then
    log "Application is healthy"
else
    warn "Could not verify application health"
fi

# ─── Summary ─────────────────────────────────────────────────────────────────
ELAPSED=$SECONDS
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║${NC}    ${CYAN}✅ Deployment Complete!${NC}                        ${GREEN}║${NC}"
echo -e "${GREEN}╠══════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║${NC}  Release  : $(basename $RELEASE_DIR)                ${GREEN}║${NC}"
echo -e "${GREEN}║${NC}  Branch   : $BRANCH                                ${GREEN}║${NC}"
echo -e "${GREEN}║${NC}  Time     : $((ELAPSED / 60))m $((ELAPSED % 60))s                               ${GREEN}║${NC}"
echo -e "${GREEN}║${NC}  Path     : ${DEPLOY_PATH}/current      ${GREEN}║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════╝${NC}"
echo ""

if [ "$FRESH" = true ]; then
    echo -e "${YELLOW}📋 Post-deploy checklist:${NC}"
    echo "   1. Verify .env settings (DB, Redis, Mail, APP_URL)"
    echo "   2. Setup SSL certificate (DirectAdmin → SSL Certificates)"
    echo "   3. Setup supervisor for queue worker:"
    echo "      cp ${DEPLOY_PATH}/current/deploy/supervisor.conf /etc/supervisor/conf.d/naruay-worker.conf"
    echo "      supervisorctl reread && supervisorctl update"
    echo "   4. Setup cron for Laravel scheduler:"
    echo "      * * * * * cd ${DEPLOY_PATH}/current && $PHP_BIN artisan schedule:run >> /dev/null 2>&1"
    echo "   5. Import legacy data:"
    echo "      cd ${DEPLOY_PATH}/current && $PHP_BIN artisan import:legacy --source=OLD_DB_NAME"
    echo ""
fi
