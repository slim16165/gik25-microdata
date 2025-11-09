#!/bin/bash
# Script per aggiornare composer.lock
# Eseguire questo script sul server o in un ambiente con PHP/Composer

echo "🔄 Aggiornamento composer.lock..."

# Verifica che composer sia disponibile
if ! command -v composer &> /dev/null; then
    echo "❌ Composer non trovato. Installare Composer prima di continuare."
    exit 1
fi

# Valida composer.json
echo "✅ Validazione composer.json..."
composer validate --strict

# Aggiorna composer.lock
echo "🔄 Aggiornamento dipendenze..."
composer update --no-interaction --prefer-dist

# Verifica che tutto sia sincronizzato
echo "✅ Verifica sincronizzazione..."
composer validate --strict

echo "✅ composer.lock aggiornato con successo!"
echo "📝 Eseeguire: git add composer.lock && git commit -m 'Update composer.lock' && git push"

