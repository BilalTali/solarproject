---
description: how to deploy the PM Surya Ghar platform to the production server
---

# PM Surya Ghar — Deployment Workflow

**Server**: `92.249.46.36`
**SSH Port**: `65002`
**SSH User**: `u335000182`
**Domain**: `andleebsurya.in`
**Web Root**: `~/domains/andleebsurya.in/public_html/`

---

## Step 1: Build Frontend Assets

Build the production-optimized frontend bundle.

```bash
cd /Users/computergallery/Documents/pmsuryaghar/frontend && npm run build
```

## Step 2: Sync Files to Server

Execute the sync script to transfer all updated backend and frontend files.

// turbo
```bash
bash /Users/computergallery/Documents/pmsuryaghar/_agents/scripts/sync-to-server.sh
```

## Step 3: Run Post-Deploy Commands on Server

Apply database migrations, install composer dependencies, and clear all caches.

```bash
sshpass -p 'Sugen@9313' ssh -p 65002 -o StrictHostKeyChecking=no u335000182@92.249.46.36 "\
  cd /home/u335000182/domains/andleebsurya.in/public_html/backend && \
  composer install --no-dev --optimize-autoloader --no-interaction && \
  php artisan migrate --force && \
  php artisan optimize:clear && \
  php artisan config:cache && \
  php artisan route:cache && \
  php artisan view:cache && \
  echo 'Post-deploy complete!'"
```

## Step 4: Verify Live Site

Check that the application is responding correctly.

// turbo
```bash
curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" https://andleebsurya.in && echo "✅ Site is live!"
```
