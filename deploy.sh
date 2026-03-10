#!/bin/bash
###############################################################################
#
#   ██╗  ██╗███╗   ███╗ █████╗ ███╗   ██╗
#   ╚██╗██╔╝████╗ ████║██╔══██╗████╗  ██║
#    ╚███╔╝ ██╔████╔██║███████║██╔██╗ ██║
#    ██╔██╗ ██║╚██╔╝██║██╔══██║██║╚██╗██║
#   ██╔╝ ██╗██║ ╚═╝ ██║██║  ██║██║ ╚████║
#   ╚═╝  ╚═╝╚═╝     ╚═╝╚═╝  ╚═╝╚═╝  ╚═══╝
#                    S T U D I O
#
#   Zero-Downtime Deployment — Naruay MLM Platform
#   Server: Ubuntu + DirectAdmin + PHP 8.3 + MySQL 8 + Redis
#
#   Usage:
#     First deploy : ./deploy.sh --fresh
#     Update       : ./deploy.sh
#     Rollback     : ./deploy.sh --rollback
#     Quick update : ./deploy.sh --skip-build
#
###############################################################################
set -euo pipefail
IFS=$'\n\t'

# ─── Configuration ──────────────────────────────────────────────────────────
APP_NAME="naruaynew"
DEPLOY_USER="${DEPLOY_USER:-admin}"
DEPLOY_PATH="${DEPLOY_PATH:-/home/${DEPLOY_USER}/domains/example.com/laravel}"
PUBLIC_HTML="${PUBLIC_HTML:-/home/${DEPLOY_USER}/domains/example.com/public_html}"
REPO_URL="${REPO_URL:-https://github.com/xjanova/naruaynew.git}"
BRANCH="${BRANCH:-master}"
PHP_BIN="${PHP_BIN:-/usr/local/bin/php83}"
COMPOSER_BIN="${COMPOSER_BIN:-/usr/local/bin/composer}"
NPM_BIN="${NPM_BIN:-/usr/bin/npm}"
REDIS_ENABLED=true
QUEUE_WORKER=true
KEEP_RELEASES=5

# ─── Colors & Helpers ───────────────────────────────────────────────────────
R='\033[0;31m'; G='\033[0;32m'; Y='\033[1;33m'
B='\033[0;34m'; C='\033[0;36m'; M='\033[0;35m'
W='\033[1;37m'; D='\033[0;90m'; NC='\033[0m'

ok()   { echo -e "  ${G}✓${NC} $1"; }
warn() { echo -e "  ${Y}!${NC} $1"; }
fail() { echo -e "  ${R}✗${NC} $1"; exit 1; }
info() { echo -e "  ${C}→${NC} $1"; }
step() { echo -e "\n${M}━━━${NC} ${W}$1${NC} ${M}━━━${NC}"; }

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
        --help|-h)
            echo ""
            echo -e "  ${W}XMAN STUDIO${NC} — Deploy Script"
            echo ""
            echo "  Usage: ./deploy.sh [OPTIONS]"
            echo ""
            echo "  Options:"
            echo "    --fresh       First-time setup (full clone, migrate, seed)"
            echo "    --rollback    Rollback to previous release"
            echo "    --skip-build  Skip npm build (use existing assets)"
            echo "    --no-migrate  Skip database migrations"
            echo "    --help        Show this help"
            echo ""
            echo "  Environment overrides:"
            echo "    DEPLOY_USER   DirectAdmin username  (default: admin)"
            echo "    DEPLOY_PATH   Laravel install path"
            echo "    PUBLIC_HTML   Public HTML path"
            echo "    PHP_BIN       PHP binary path       (default: /usr/local/bin/php83)"
            echo "    BRANCH        Git branch            (default: master)"
            echo ""
            exit 0
            ;;
    esac
done

# ─── Banner ──────────────────────────────────────────────────────────────────
SECONDS=0
clear 2>/dev/null || true
echo ""
echo -e "${M}  ╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${M}  ║${NC}                                                   ${M}║${NC}"
echo -e "${M}  ║${NC}   ${W}XMAN STUDIO${NC}  ${D}deployment engine${NC}                  ${M}║${NC}"
echo -e "${M}  ║${NC}   ${C}Naruay MLM Platform${NC}                             ${M}║${NC}"
echo -e "${M}  ║${NC}                                                   ${M}║${NC}"
echo -e "${M}  ╚═══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${D}Branch: ${W}$BRANCH${NC}  ${D}Server: ${W}$DEPLOY_USER${NC}  ${D}PHP: ${W}$PHP_BIN${NC}"
echo ""

# ─── Rollback ────────────────────────────────────────────────────────────────
if [ "$ROLLBACK" = true ]; then
    step "Rolling Back"
    PREV_RELEASE=$(ls -1dt ${DEPLOY_PATH}/releases/*/ 2>/dev/null | sed -n '2p')
    [ -z "$PREV_RELEASE" ] && fail "No previous release found."

    ln -sfn "$PREV_RELEASE" "${DEPLOY_PATH}/current"
    ln -sfn "${DEPLOY_PATH}/current/public" "$PUBLIC_HTML"

    if [ "$QUEUE_WORKER" = true ]; then
        cd "$PREV_RELEASE" && $PHP_BIN artisan queue:restart 2>/dev/null || true
    fi

    ok "Rolled back to: $(basename $PREV_RELEASE)"
    echo ""
    exit 0
fi

# ─── Pre-flight ──────────────────────────────────────────────────────────────
step "Pre-flight Checks"

command -v git >/dev/null 2>&1 || fail "git not found"
$PHP_BIN -v >/dev/null 2>&1 || fail "PHP not found at $PHP_BIN"
$COMPOSER_BIN -V >/dev/null 2>&1 || fail "Composer not found at $COMPOSER_BIN"

PHP_VERSION=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
[[ "$PHP_VERSION" < "8.2" ]] && fail "PHP 8.2+ required. Found: $PHP_VERSION"
ok "PHP $PHP_VERSION"

for ext in pdo_mysql redis mbstring openssl tokenizer xml ctype json bcmath; do
    $PHP_BIN -m 2>/dev/null | grep -qi "^$ext$" || warn "Missing ext: $ext"
done
ok "Extensions checked"

# ─── Directory Setup ────────────────────────────────────────────────────────
RELEASE_DIR="${DEPLOY_PATH}/releases/$(date +%Y%m%d_%H%M%S)"
SHARED_DIR="${DEPLOY_PATH}/shared"

if [ "$FRESH" = true ]; then
    step "First Deploy — Setup"
    mkdir -p "$DEPLOY_PATH"/{releases,shared}
    mkdir -p "$SHARED_DIR"/{storage/app/public,storage/framework/{cache/data,sessions,testing,views},storage/logs}
    ok "Directory structure created"

    if [ ! -f "$SHARED_DIR/.env" ]; then
        warn "No .env found — creating production template..."
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
        echo ""
        warn "Edit .env before continuing:"
        echo -e "  ${C}nano ${SHARED_DIR}/.env${NC}"
        echo ""
        read -p "  Press Enter after editing .env (Ctrl+C to cancel)..."
    fi
fi

# ─── Clone ───────────────────────────────────────────────────────────────────
step "Deploying Release"
mkdir -p "$RELEASE_DIR"

info "Cloning $BRANCH..."
git clone --depth 1 --branch "$BRANCH" "$REPO_URL" "$RELEASE_DIR" 2>&1 | tail -2
ok "Source code ready: $(basename $RELEASE_DIR)"

# ─── Shared Symlinks ────────────────────────────────────────────────────────
rm -rf "$RELEASE_DIR/storage"
ln -sfn "$SHARED_DIR/storage" "$RELEASE_DIR/storage"
ln -sfn "$SHARED_DIR/.env" "$RELEASE_DIR/.env"
ok "Linked shared storage + .env"

# ─── Composer ────────────────────────────────────────────────────────────────
step "Dependencies"
cd "$RELEASE_DIR"

$PHP_BIN $COMPOSER_BIN install \
    --no-dev --no-interaction --prefer-dist --optimize-autoloader 2>&1 | tail -3
ok "Composer install complete"

# ─── NPM Build ──────────────────────────────────────────────────────────────
if [ "$SKIP_BUILD" = false ]; then
    if command -v $NPM_BIN >/dev/null 2>&1; then
        info "Building frontend..."
        $NPM_BIN ci --legacy-peer-deps 2>&1 | tail -2
        $NPM_BIN run build 2>&1 | tail -3
        rm -rf node_modules
        ok "Frontend built + node_modules cleaned"
    else
        warn "npm not found — skipping frontend build"
    fi
else
    info "Skipping build (--skip-build)"
fi

# ─── App Key ─────────────────────────────────────────────────────────────────
if [ "$FRESH" = true ]; then
    APP_KEY_CHECK=$(grep "^APP_KEY=" "$SHARED_DIR/.env" | cut -d= -f2)
    if [ -z "$APP_KEY_CHECK" ] || [ "$APP_KEY_CHECK" = "" ]; then
        $PHP_BIN artisan key:generate --force >/dev/null 2>&1
        ok "App key generated"
    fi
fi

# ─── Laravel Cache ───────────────────────────────────────────────────────────
step "Optimizing"

$PHP_BIN artisan config:cache >/dev/null 2>&1
$PHP_BIN artisan route:cache >/dev/null 2>&1
$PHP_BIN artisan view:cache >/dev/null 2>&1
$PHP_BIN artisan event:cache >/dev/null 2>&1
ok "Config + routes + views + events cached"

[ ! -L "$RELEASE_DIR/public/storage" ] && $PHP_BIN artisan storage:link 2>/dev/null || true

# ─── Database ────────────────────────────────────────────────────────────────
if [ "$MIGRATE" = true ]; then
    step "Database"
    $PHP_BIN artisan migrate --force 2>&1 | tail -5
    ok "Migrations complete"

    if [ "$FRESH" = true ]; then
        $PHP_BIN artisan db:seed --force 2>&1 | tail -3
        ok "Database seeded"
    fi
fi

# ─── Activate ────────────────────────────────────────────────────────────────
step "Activating"

ln -sfn "$RELEASE_DIR" "${DEPLOY_PATH}/current"

if [ -d "$PUBLIC_HTML" ] && [ ! -L "$PUBLIC_HTML" ]; then
    mv "$PUBLIC_HTML" "${PUBLIC_HTML}_backup_$(date +%Y%m%d_%H%M%S)" 2>/dev/null || true
fi
ln -sfn "${DEPLOY_PATH}/current/public" "$PUBLIC_HTML"
ok "Release live → public_html linked"

# ─── Permissions ─────────────────────────────────────────────────────────────
chmod -R 755 "$SHARED_DIR/storage" 2>/dev/null || true
chmod -R 755 "${DEPLOY_PATH}/current/bootstrap/cache" 2>/dev/null || true
ok "Permissions set"

# ─── Queue & Cache ───────────────────────────────────────────────────────────
[ "$QUEUE_WORKER" = true ] && $PHP_BIN artisan queue:restart 2>/dev/null || true
[ "$REDIS_ENABLED" = true ] && $PHP_BIN artisan cache:clear 2>/dev/null || true
ok "Queue restarted + cache cleared"

# ─── Cleanup ─────────────────────────────────────────────────────────────────
cd "${DEPLOY_PATH}/releases"
ls -1dt */ 2>/dev/null | tail -n +$((KEEP_RELEASES + 1)) | xargs -r rm -rf
ok "Old releases cleaned (keeping $KEEP_RELEASES)"

# ─── Done ────────────────────────────────────────────────────────────────────
ELAPSED=$SECONDS
echo ""
echo -e "${G}  ╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ║${NC}   ${W}✅ Deploy Complete${NC}                               ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ╠═══════════════════════════════════════════════════╣${NC}"
echo -e "${G}  ║${NC}   Release : ${C}$(basename $RELEASE_DIR)${NC}               ${G}║${NC}"
echo -e "${G}  ║${NC}   Branch  : ${C}$BRANCH${NC}                                 ${G}║${NC}"
echo -e "${G}  ║${NC}   Time    : ${C}$((ELAPSED / 60))m $((ELAPSED % 60))s${NC}                                ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ╚═══════════════════════════════════════════════════╝${NC}"
echo ""

if [ "$FRESH" = true ]; then
    echo -e "  ${Y}Post-deploy:${NC}"
    echo ""
    echo -e "  ${D}1.${NC} Visit your site → Setup Wizard will appear"
    echo -e "  ${D}2.${NC} Create admin account via the wizard"
    echo -e "  ${D}3.${NC} Setup supervisor:"
    echo -e "     ${D}cp ${DEPLOY_PATH}/current/deploy/supervisor.conf /etc/supervisor/conf.d/naruay.conf${NC}"
    echo -e "     ${D}supervisorctl reread && supervisorctl update${NC}"
    echo -e "  ${D}4.${NC} Setup cron:"
    echo -e "     ${D}* * * * * cd ${DEPLOY_PATH}/current && $PHP_BIN artisan schedule:run >> /dev/null 2>&1${NC}"
    echo ""
fi

echo -e "  ${D}Powered by XMAN STUDIO${NC}"
echo ""
