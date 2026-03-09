#!/bin/bash
###############################################################################
# Naruay MLM — Server Setup Script (run once on fresh server)
# Ubuntu + DirectAdmin + PHP 8.3
#
# Usage: sudo ./setup-server.sh
###############################################################################
set -euo pipefail

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; NC='\033[0m'

log()  { echo -e "${GREEN}[✓]${NC} $1"; }
info() { echo -e "${CYAN}[→]${NC} $1"; }
warn() { echo -e "${YELLOW}[!]${NC} $1"; }

echo ""
echo -e "${CYAN}══════════════════════════════════════${NC}"
echo -e "${CYAN}  Naruay MLM — Server Setup${NC}"
echo -e "${CYAN}══════════════════════════════════════${NC}"
echo ""

# Check root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root: sudo ./setup-server.sh${NC}"
    exit 1
fi

# ─── 1. System Updates ──────────────────────────────────────────────────────
info "Updating system packages..."
apt update -qq && apt upgrade -y -qq
log "System updated"

# ─── 2. Install Redis ───────────────────────────────────────────────────────
if ! command -v redis-server &>/dev/null; then
    info "Installing Redis..."
    apt install -y redis-server
    systemctl enable redis-server
    systemctl start redis-server

    # Secure Redis
    sed -i 's/^# maxmemory .*/maxmemory 256mb/' /etc/redis/redis.conf
    sed -i 's/^# maxmemory-policy .*/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
    systemctl restart redis-server
    log "Redis installed & configured"
else
    log "Redis already installed"
fi

# ─── 3. Install Supervisor ──────────────────────────────────────────────────
if ! command -v supervisorctl &>/dev/null; then
    info "Installing Supervisor..."
    apt install -y supervisor
    systemctl enable supervisor
    systemctl start supervisor
    log "Supervisor installed"
else
    log "Supervisor already installed"
fi

# ─── 4. Install Node.js (for building assets) ───────────────────────────────
if ! command -v node &>/dev/null; then
    info "Installing Node.js 20 LTS..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt install -y nodejs
    log "Node.js $(node -v) installed"
else
    log "Node.js $(node -v) already installed"
fi

# ─── 5. PHP Extensions Check ────────────────────────────────────────────────
info "Checking PHP 8.3 extensions..."
PHP_BIN="/usr/local/bin/php83"

if [ ! -f "$PHP_BIN" ]; then
    # Try alternative paths
    for p in /usr/bin/php8.3 /usr/local/php83/bin/php /opt/alt/php83/usr/bin/php; do
        if [ -f "$p" ]; then
            PHP_BIN="$p"
            break
        fi
    done
fi

if [ -f "$PHP_BIN" ]; then
    INSTALLED=$($PHP_BIN -m 2>/dev/null)
    REQUIRED="pdo_mysql redis mbstring openssl tokenizer xml ctype json bcmath gd curl zip"

    for ext in $REQUIRED; do
        if echo "$INSTALLED" | grep -qi "^$ext$"; then
            log "  PHP ext: $ext ✓"
        else
            warn "  PHP ext: $ext ✗ — install via DirectAdmin → PHP Extensions"
        fi
    done

    # Install PHP Redis extension if missing
    if ! echo "$INSTALLED" | grep -qi "^redis$"; then
        warn "PHP Redis extension missing!"
        warn "Install via DirectAdmin → CustomBuild → PHP Extensions → redis"
        warn "Or: /usr/local/directadmin/custombuild/build php_extensions"
    fi
else
    warn "PHP 8.3 binary not found — check DirectAdmin PHP installation"
fi

# ─── 6. Git Config ──────────────────────────────────────────────────────────
if ! command -v git &>/dev/null; then
    info "Installing git..."
    apt install -y git
    log "Git installed"
fi

# ─── 7. Composer ─────────────────────────────────────────────────────────────
if ! command -v /usr/local/bin/composer &>/dev/null; then
    info "Installing Composer..."
    curl -sS https://getcomposer.org/installer | $PHP_BIN -- --install-dir=/usr/local/bin --filename=composer
    log "Composer installed"
else
    log "Composer already installed"
fi

# ─── 8. Create MySQL Database ───────────────────────────────────────────────
echo ""
warn "Remember to create database via DirectAdmin:"
echo "   1. DirectAdmin → MySQL Management → Create Database"
echo "   2. Database name: naruaynew"
echo "   3. Create user with full privileges"
echo "   4. Update .env with credentials"
echo ""

# ─── 9. Firewall ────────────────────────────────────────────────────────────
if command -v ufw &>/dev/null; then
    info "Configuring firewall..."
    ufw allow 22/tcp    # SSH
    ufw allow 80/tcp    # HTTP
    ufw allow 443/tcp   # HTTPS
    ufw allow 2222/tcp  # DirectAdmin
    # Redis should NOT be exposed externally
    ufw deny 6379/tcp
    log "Firewall configured"
fi

# ─── Summary ─────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════${NC}"
echo -e "${GREEN}  ✅ Server Setup Complete${NC}"
echo -e "${GREEN}══════════════════════════════════════${NC}"
echo ""
echo "Next steps:"
echo "  1. Create MySQL database via DirectAdmin"
echo "  2. Install PHP redis extension via DirectAdmin CustomBuild"
echo "  3. Run deploy: ./deploy.sh --fresh"
echo ""
