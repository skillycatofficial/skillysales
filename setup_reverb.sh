#!/bin/bash

# Append Reverb configuration to .env if not present
if ! grep -q "BROADCAST_CONNECTION=reverb" .env; then
    echo "" >> .env
    echo "BROADCAST_CONNECTION=reverb" >> .env
    echo "REVERB_APP_ID=automarket-app-id" >> .env
    echo "REVERB_APP_KEY=automarket-app-key" >> .env
    echo "REVERB_APP_SECRET=automarket-app-secret" >> .env
    echo "REVERB_HOST=localhost" >> .env
    echo "REVERB_PORT=8080" >> .env
    echo "REVERB_SCHEME=http" >> .env
    
    echo "✅ Added Reverb configuration to .env"
else
    echo "ℹ️ Reverb configuration already exists in .env"
fi

# Clear config cache
php artisan config:clear

echo "🚀 Configuration updated! Please restart your servers:"
echo "1. php artisan reverb:start"
echo "2. php artisan serve"
