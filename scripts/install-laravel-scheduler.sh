#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PHP_BIN="${PHP_BIN:-php}"
MARKER="# Madaba MFI Laravel Scheduler"
CRON_LINE="* * * * * cd ${PROJECT_DIR} && ${PHP_BIN} artisan schedule:run >> ${PROJECT_DIR}/storage/logs/scheduler.log 2>&1"

if crontab -l 2>/dev/null | grep -Fq "${MARKER}"; then
    echo "Laravel scheduler cron entry is already installed."
    exit 0
fi

{
    crontab -l 2>/dev/null || true
    echo "${MARKER}"
    echo "${CRON_LINE}"
} | crontab -

echo "Installed Laravel scheduler cron entry:"
echo "  ${CRON_LINE}"
