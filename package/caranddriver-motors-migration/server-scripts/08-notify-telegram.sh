#!/usr/bin/env bash
# ============================================================
# Уведомление в Telegram о завершении этапа (опционально).
# Переменные окружения: TG_TOKEN, TG_CHAT_ID, MSG
# Пример: TG_TOKEN=*** TG_CHAT_ID=*** MSG="Шаг 3 выполнен" bash 08-notify-telegram.sh
# ============================================================
set -euo pipefail
TG_TOKEN="${TG_TOKEN:?задайте TG_TOKEN}"
TG_CHAT_ID="${TG_CHAT_ID:?задайте TG_CHAT_ID}"
MSG="${MSG:-Этап миграции выполнен}"

curl -s -o /dev/null -w "%{http_code}\n" \
    -X POST "https://api.telegram.org/bot${TG_TOKEN}/sendMessage" \
    -d chat_id="${TG_CHAT_ID}" \
    -d text="${MSG}" \
    -d disable_web_page_preview=true
