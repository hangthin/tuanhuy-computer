#!/bin/bash
TOKEN="8723472812:AAHqJjAXt4jsKAYkY8X5lnfzhyNc6Fh0YJY"
SERVER="http://13.219.182.176/tuanhuy_computer"

echo "Setting webhook..."
curl "https://api.telegram.org/bot$TOKEN/setWebhook?url=$SERVER/telegram/webhook"
echo ""

echo "Webhook info:"
curl "https://api.telegram.org/bot$TOKEN/getWebhookInfo"
echo ""
echo "Done!"
