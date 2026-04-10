#!/bin/bash

# ─────────────────────────────────────────────────────────────────────────────
# PM Surya Ghar — Production Sync Script
# Domain  : andleebsurya.in
# Server  : 92.249.46.36
# Port    : 65002
# User    : u335000182
# Web Root: ~/domains/andleebsurya.in/public_html/
# ─────────────────────────────────────────────────────────────────────────────

set -e

# ── Configuration ─────────────────────────────────────────────────────────────
SSH_HOST="92.249.46.36"
SSH_PORT="65002"
SSH_USER="u335000182"
SSH_PASS="Sugen@9313"

LOCAL_PROJECT="/Users/computergallery/Documents/pmsuryaghar"
REMOTE_WEB_ROOT="/home/u335000182/domains/andleebsurya.in/public_html"
REMOTE_BACKEND="${REMOTE_WEB_ROOT}/backend"
REMOTE_FRONTEND="${REMOTE_WEB_ROOT}"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

log()  { echo -e "${GREEN}[DEPLOY]${NC} $1"; }
warn() { echo -e "${YELLOW}[WARN]${NC}  $1"; }
err()  { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
info() { echo -e "${CYAN}[INFO]${NC}  $1"; }

SSH_CMD="/Users/computergallery/Documents/pmsuryaghar/_agents/scripts/expect_wrapper.exp '${SSH_PASS}' ssh -p ${SSH_PORT} -o StrictHostKeyChecking=no"
RSYNC_BASE="/Users/computergallery/Documents/pmsuryaghar/_agents/scripts/expect_wrapper.exp '${SSH_PASS}' rsync -avz --progress -e 'ssh -p ${SSH_PORT} -o StrictHostKeyChecking=no'"

RSYNC_BACKEND="${RSYNC_BASE} \
  --exclude '.git' \
  --exclude 'node_modules' \
  --exclude 'vendor' \
  --exclude '.env' \
  --exclude '*.log' \
  --exclude 'storage/logs/*' \
  --exclude 'storage/framework/cache/*' \
  --exclude 'storage/framework/sessions/*' \
  --exclude 'storage/framework/views/*' \
  --exclude 'bootstrap/cache/*'"

RSYNC_FRONTEND="${RSYNC_BASE}"

# ── Pre-flight Checks ─────────────────────────────────────────────────────────
echo ""
echo "  ╔══════════════════════════════════════════════╗"
echo "  ║     PM Surya Ghar — Production Deployment    ║"
echo "  ║     Domain: andleebsurya.in                  ║"
echo "  ╚══════════════════════════════════════════════╝"
echo ""

chmod +x /Users/computergallery/Documents/pmsuryaghar/_agents/scripts/expect_wrapper.exp
# command -v sshpass &>/dev/null || err "sshpass not found. Install via: brew install sshpass"
command -v rsync   &>/dev/null || err "rsync not found."

# Test connection
info "Testing SSH connection to production server..."
eval "${SSH_CMD} ${SSH_USER}@${SSH_HOST} 'echo \"Connection OK\"'" || err "SSH connection failed. Check credentials."
log "✅ SSH connection verified."

# ── Step 1: Sync Backend ──────────────────────────────────────────────────────
log "Syncing backend PHP files..."
eval "${RSYNC_BACKEND} \
  '${LOCAL_PROJECT}/backend/' \
  '${SSH_USER}@${SSH_HOST}:${REMOTE_BACKEND}/'"
log "✅ Backend synced."

# ── Step 2: Sync Frontend Build ───────────────────────────────────────────────
if [ -d "${LOCAL_PROJECT}/frontend/dist" ]; then
  log "Syncing frontend dist (JS/CSS/HTML)..."
  eval "${RSYNC_FRONTEND} \
    --exclude 'backend' \
    --exclude 'storage' \
    --exclude '.env' \
    '${LOCAL_PROJECT}/frontend/dist/' \
    '${SSH_USER}@${SSH_HOST}:${REMOTE_FRONTEND}/'"
  log "✅ Frontend dist synced."
else
  warn "frontend/dist not found! Run 'npm run build' first, then re-run this script."
fi

# ── Step 3: Set Permissions ───────────────────────────────────────────────────
log "Setting server storage permissions..."
eval "${SSH_CMD} ${SSH_USER}@${SSH_HOST} \
  'chmod -R 755 ${REMOTE_BACKEND}/storage && \
   chmod -R 755 ${REMOTE_BACKEND}/bootstrap/cache && \
   echo \"Permissions OK\"'"
log "✅ Permissions set."

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
log "🚀 Sync Complete!"
info "Next: Run post-deploy commands (migrations, cache clear) using the /deploy workflow."
echo ""
