#!/bin/bash
# Script per installare il pre-commit hook Git
# Utilizzo: ./scripts/install-pre-commit-hook.sh

set -e

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
HOOK_SOURCE="$REPO_ROOT/scripts/hooks/pre-commit"
HOOK_TARGET="$REPO_ROOT/.git/hooks/pre-commit"

if [ ! -d "$REPO_ROOT/.git" ]; then
    echo "❌ Errore: questa directory non è un repository Git"
    exit 1
fi

if [ ! -f "$HOOK_SOURCE" ]; then
    echo "❌ Errore: file hook non trovato: $HOOK_SOURCE"
    exit 1
fi

# Copia l'hook
cp "$HOOK_SOURCE" "$HOOK_TARGET"
chmod +x "$HOOK_TARGET"

echo "✅ Pre-commit hook installato correttamente in $HOOK_TARGET"
echo ""
echo "Il hook validerà automaticamente la sintassi PHP prima di ogni commit."
echo "Per disinstallare: rm $HOOK_TARGET"

