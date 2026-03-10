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
#   Naruay MLM Platform — One-Command Installer
#   https://github.com/xjanova/naruaynew
#
###############################################################################
set -euo pipefail

# ─── Colors ──────────────────────────────────────────────────────────────────
R='\033[0;31m'; G='\033[0;32m'; Y='\033[1;33m'
B='\033[0;34m'; C='\033[0;36m'; M='\033[0;35m'
W='\033[1;37m'; D='\033[0;90m'; NC='\033[0m'

ok()   { echo -e "  ${G}✓${NC} $1"; }
warn() { echo -e "  ${Y}!${NC} $1"; }
fail() { echo -e "  ${R}✗${NC} $1"; exit 1; }
ask()  { echo -e "  ${C}?${NC} $1"; }
step() { echo -e "\n${W}━━━ $1 ━━━${NC}"; }

# ─── Banner ──────────────────────────────────────────────────────────────────
clear 2>/dev/null || true
echo ""
echo -e "${M}  ╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${M}  ║${NC}                                                   ${M}║${NC}"
echo -e "${M}  ║${NC}   ${W}XMAN STUDIO${NC} ${D}presents${NC}                            ${M}║${NC}"
echo -e "${M}  ║${NC}   ${C}Naruay MLM Platform${NC} — Installer               ${M}║${NC}"
echo -e "${M}  ║${NC}                                                   ${M}║${NC}"
echo -e "${M}  ╚═══════════════════════════════════════════════════╝${NC}"
echo ""

# ─── Detect PHP ──────────────────────────────────────────────────────────────
step "Checking Requirements"

PHP_BIN=""
for candidate in php83 php8.3 php /usr/local/bin/php83 /usr/bin/php; do
    if command -v "$candidate" >/dev/null 2>&1; then
        VER=$("$candidate" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "0")
        if [[ "$VER" > "8.1" ]]; then
            PHP_BIN="$candidate"
            break
        fi
    fi
done
[ -z "$PHP_BIN" ] && fail "PHP 8.2+ not found. Install PHP first."
ok "PHP $($PHP_BIN -r 'echo phpversion();') ($PHP_BIN)"

# Detect Composer
COMPOSER_BIN=""
for candidate in composer /usr/local/bin/composer "$HOME/.composer/vendor/bin/composer"; do
    if command -v "$candidate" >/dev/null 2>&1 || [ -f "$candidate" ]; then
        COMPOSER_BIN="$candidate"
        break
    fi
done
[ -z "$COMPOSER_BIN" ] && fail "Composer not found. Install: https://getcomposer.org"
ok "Composer $($COMPOSER_BIN --version 2>/dev/null | grep -oP '[\d.]+' | head -1)"

# Check PHP extensions
MISSING_EXT=()
for ext in pdo_mysql mbstring openssl tokenizer xml ctype json bcmath curl; do
    if ! $PHP_BIN -m 2>/dev/null | grep -qi "^$ext$"; then
        MISSING_EXT+=("$ext")
    fi
done
if [ ${#MISSING_EXT[@]} -gt 0 ]; then
    warn "Missing PHP extensions: ${MISSING_EXT[*]}"
    warn "Install: sudo apt install php8.3-{${MISSING_EXT[*]// /,}}"
else
    ok "All required PHP extensions present"
fi

# Check MySQL/MariaDB
if command -v mysql >/dev/null 2>&1; then
    MYSQL_VER=$(mysql --version 2>/dev/null | grep -oP '[\d.]+' | head -1)
    ok "MySQL/MariaDB $MYSQL_VER"
else
    warn "MySQL/MariaDB client not found (might be on remote server)"
fi

# Check Redis
if command -v redis-cli >/dev/null 2>&1; then
    if redis-cli ping >/dev/null 2>&1; then
        ok "Redis $(redis-cli --version 2>/dev/null | grep -oP '[\d.]+' | head -1) — running"
    else
        warn "Redis installed but not running"
    fi
else
    warn "Redis not found — will use database for cache/session/queue"
fi

# Check Node.js
NPM_BIN=""
if command -v npm >/dev/null 2>&1; then
    NPM_BIN="npm"
    NODE_VER=$(node --version 2>/dev/null || echo "?")
    ok "Node.js $NODE_VER / npm $(npm --version 2>/dev/null)"
elif command -v /usr/bin/npm >/dev/null 2>&1; then
    NPM_BIN="/usr/bin/npm"
    ok "Node.js $(node --version 2>/dev/null || echo '?')"
else
    warn "Node.js/npm not found — frontend build will be skipped"
    warn "Install: curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - && sudo apt install -y nodejs"
fi

# ─── Environment Setup ──────────────────────────────────────────────────────
step "Environment Configuration"

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"
ok "Working directory: $APP_DIR"

if [ ! -f .env ]; then
    cp .env.example .env
    ok "Created .env from .env.example"

    echo ""
    ask "Configure database connection:"
    echo ""

    read -p "    DB Host [127.0.0.1]: " DB_HOST
    DB_HOST=${DB_HOST:-127.0.0.1}

    read -p "    DB Port [3306]: " DB_PORT
    DB_PORT=${DB_PORT:-3306}

    read -p "    DB Name [naruaynew]: " DB_NAME
    DB_NAME=${DB_NAME:-naruaynew}

    read -p "    DB Username [root]: " DB_USER
    DB_USER=${DB_USER:-root}

    read -sp "    DB Password: " DB_PASS
    echo ""

    # Update .env
    sed -i "s|^APP_NAME=.*|APP_NAME=\"Naruay MLM\"|" .env
    sed -i "s|^APP_ENV=.*|APP_ENV=production|" .env
    sed -i "s|^APP_DEBUG=.*|APP_DEBUG=false|" .env
    sed -i "s|^APP_TIMEZONE=.*|APP_TIMEZONE=Asia/Bangkok|" .env 2>/dev/null || echo "APP_TIMEZONE=Asia/Bangkok" >> .env
    sed -i "s|^DB_CONNECTION=.*|DB_CONNECTION=mysql|" .env
    sed -i "s|^# DB_HOST=.*|DB_HOST=$DB_HOST|" .env
    sed -i "s|^DB_HOST=.*|DB_HOST=$DB_HOST|" .env
    sed -i "s|^# DB_PORT=.*|DB_PORT=$DB_PORT|" .env
    sed -i "s|^DB_PORT=.*|DB_PORT=$DB_PORT|" .env
    sed -i "s|^# DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|^# DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|^# DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env

    # Check if Redis is available
    if redis-cli ping >/dev/null 2>&1; then
        sed -i "s|^SESSION_DRIVER=.*|SESSION_DRIVER=redis|" .env
        sed -i "s|^QUEUE_CONNECTION=.*|QUEUE_CONNECTION=redis|" .env
        sed -i "s|^CACHE_STORE=.*|CACHE_STORE=redis|" .env
        sed -i "s|^REDIS_CLIENT=.*|REDIS_CLIENT=predis|" .env
        ok "Redis detected — configured for session/cache/queue"
    fi

    ok "Database configured: $DB_USER@$DB_HOST:$DB_PORT/$DB_NAME"
else
    ok ".env already exists — skipping"
fi

# ─── Composer Install ────────────────────────────────────────────────────────
step "Installing Dependencies"

$PHP_BIN $COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader 2>&1 | tail -3
ok "PHP dependencies installed"

# ─── App Key ─────────────────────────────────────────────────────────────────
APP_KEY_CHECK=$(grep "^APP_KEY=" .env | cut -d= -f2)
if [ -z "$APP_KEY_CHECK" ] || [ "$APP_KEY_CHECK" = "" ]; then
    $PHP_BIN artisan key:generate --force >/dev/null 2>&1
    ok "Application key generated"
else
    ok "Application key exists"
fi

# ─── NPM Build ──────────────────────────────────────────────────────────────
if [ -n "$NPM_BIN" ]; then
    step "Building Frontend"

    $NPM_BIN ci --legacy-peer-deps 2>&1 | tail -3
    ok "Node dependencies installed"

    $NPM_BIN run build 2>&1 | tail -3
    ok "Frontend assets built"
else
    warn "Skipping frontend build (npm not available)"
fi

# ─── Database ────────────────────────────────────────────────────────────────
step "Database Setup"

echo ""
ask "How would you like to set up the database?"
echo ""
echo "    1) Run migrations + seed (fresh install)"
echo "    2) Run migrations only (keep existing data)"
echo "    3) Skip database setup"
echo ""
read -p "    Choose [1]: " DB_CHOICE
DB_CHOICE=${DB_CHOICE:-1}

case $DB_CHOICE in
    1)
        $PHP_BIN artisan migrate --force 2>&1 | tail -5
        ok "Migrations completed"
        $PHP_BIN artisan db:seed --force 2>&1 | tail -3
        ok "Database seeded (admin, ranks, products, settings)"
        ;;
    2)
        $PHP_BIN artisan migrate --force 2>&1 | tail -5
        ok "Migrations completed"
        ;;
    3)
        ok "Database setup skipped"
        ;;
esac

# ─── Permissions ─────────────────────────────────────────────────────────────
step "Finalizing"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true
$PHP_BIN artisan storage:link 2>/dev/null || true
ok "Storage linked & permissions set"

# ─── Optimize ────────────────────────────────────────────────────────────────
$PHP_BIN artisan config:cache >/dev/null 2>&1 || true
$PHP_BIN artisan route:cache >/dev/null 2>&1 || true
$PHP_BIN artisan view:cache >/dev/null 2>&1 || true
ok "Laravel optimized (config/route/view cached)"

# ─── Done ────────────────────────────────────────────────────────────────────
echo ""
echo -e "${G}  ╔═══════════════════════════════════════════════════╗${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ║${NC}   ${W}✅ Installation Complete!${NC}                       ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ╠═══════════════════════════════════════════════════╣${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ║${NC}   ${C}Next Steps:${NC}                                     ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ║${NC}   1. Visit your site in browser                   ${G}║${NC}"
echo -e "${G}  ║${NC}   2. First visit → Admin Setup Wizard             ${G}║${NC}"
echo -e "${G}  ║${NC}   3. Set your admin credentials                   ${G}║${NC}"
echo -e "${G}  ║${NC}   4. Start using Naruay MLM!                      ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ║${NC}   ${D}Default admin (if seeded):${NC}                      ${G}║${NC}"
echo -e "${G}  ║${NC}   ${D}Email: admin@naruay.com${NC}                          ${G}║${NC}"
echo -e "${G}  ║${NC}   ${D}Pass:  Admin@2024${NC}                                ${G}║${NC}"
echo -e "${G}  ║${NC}                                                   ${G}║${NC}"
echo -e "${G}  ╚═══════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${D}Powered by XMAN STUDIO${NC}"
echo ""
