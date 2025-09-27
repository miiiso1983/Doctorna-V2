#!/usr/bin/env bash
set -euo pipefail

# Cloudways DB import helper for Doctorna
# Usage:
#   sh database/import_cloudways.sh <DB_HOST> <DB_NAME> <DB_USER>
# It will prompt for the password securely.
#
# Example:
#   sh database/import_cloudways.sh 127.0.0.1 jzpuaphbvt your_db_user

if [ $# -lt 3 ]; then
  echo "Usage: $0 <DB_HOST> <DB_NAME> <DB_USER>" 1>&2
  exit 1
fi

DB_HOST="$1"
DB_NAME="$2"
DB_USER="$3"

# Create a temp file with utf8mb4 header and without CREATE DATABASE/USE lines
TMP_SQL="$(mktemp)"
# Add proper charset at top to avoid 1366 errors with Arabic text
printf "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n" > "$TMP_SQL"
# Strip CREATE DATABASE / USE lines if present
sed -E '/^CREATE DATABASE[[:space:]]/d; /^USE[[:space:]]/d' database/schema.sql >> "$TMP_SQL"
# Re-enable FK checks at the end
printf "\nSET FOREIGN_KEY_CHECKS=1;\n" >> "$TMP_SQL"

# Import into Cloudways DB
# --default-character-set=utf8mb4 is crucial for Arabic content
mysql --default-character-set=utf8mb4 -h "$DB_HOST" -u "$DB_USER" -p "$DB_NAME" < "$TMP_SQL"

rm -f "$TMP_SQL"

echo "Import completed successfully into $DB_NAME on $DB_HOST."
