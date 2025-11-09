#!/bin/bash
# Script per validare la sintassi di tutti i file PHP nel plugin
# Utilizzo: ./scripts/validate-php-syntax.sh

set -e

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Directory del plugin (root)
PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
ERRORS=0
FILES_CHECKED=0

echo "🔍 Validazione sintassi PHP..."
echo "Directory: $PLUGIN_DIR"
echo ""

# Trova tutti i file PHP escludendo vendor e node_modules
while IFS= read -r -d '' file; do
    FILES_CHECKED=$((FILES_CHECKED + 1))
    
    # Valida sintassi PHP
    if php -l "$file" > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file"
        php -l "$file" 2>&1 | head -5
        ERRORS=$((ERRORS + 1))
    fi
done < <(find "$PLUGIN_DIR" -type f -name "*.php" ! -path "*/vendor/*" ! -path "*/node_modules/*" ! -path "*/.git/*" -print0)

echo ""
echo "─────────────────────────────────────"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓ Tutti i file PHP hanno sintassi valida${NC}"
    echo "File controllati: $FILES_CHECKED"
    exit 0
else
    echo -e "${RED}✗ Trovati $ERRORS errori di sintassi${NC}"
    echo "File controllati: $FILES_CHECKED"
    exit 1
fi

