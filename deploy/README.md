# Naruay MLM — Deployment Guide

## Server Requirements
- Ubuntu 20.04+ with DirectAdmin
- PHP 8.3 + extensions: pdo_mysql, redis, mbstring, bcmath, gd, curl, zip
- MySQL 8.0+ or MariaDB 10.6+
- Redis 6+
- Node.js 20+ (for building assets)
- Supervisor (for queue workers)

## Quick Start

### 1. Server Setup (run once)
```bash
scp deploy/setup-server.sh user@server:/tmp/
ssh user@server 'sudo bash /tmp/setup-server.sh'
```

### 2. First Deploy
```bash
scp deploy.sh user@server:/home/DEPLOY_USER/
ssh user@server
chmod +x deploy.sh
./deploy.sh --fresh
```

### 3. Subsequent Deploys
```bash
ssh user@server './deploy.sh'
```

### 4. Rollback
```bash
ssh user@server './deploy.sh --rollback'
```

## Directory Structure on Server
```
/home/DEPLOY_USER/domains/example.com/
├── laravel/
│   ├── current -> releases/20260310_120000    (symlink to active release)
│   ├── releases/
│   │   ├── 20260310_120000/                   (release 1)
│   │   └── 20260310_130000/                   (release 2)
│   └── shared/
│       ├── .env                               (production config)
│       └── storage/                           (persistent storage)
│           ├── app/
│           ├── framework/
│           └── logs/
└── public_html -> laravel/current/public      (symlink)
```

## Import Legacy Data
```bash
cd /home/DEPLOY_USER/domains/example.com/laravel/current
php83 artisan import:legacy --source=admin_xmanshop --prefix=313_ --chunk=500
```

## Configuration Checklist
- [ ] Edit `.env` — DB credentials, APP_URL, APP_KEY
- [ ] Setup SSL via DirectAdmin
- [ ] Copy `deploy/supervisor.conf` → `/etc/supervisor/conf.d/`
- [ ] Add cron jobs from `deploy/cron.txt`
- [ ] Install PHP redis extension
- [ ] Create MySQL database + user
