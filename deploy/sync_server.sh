#!/bin/bash
SERVER="ec2-user@13.219.182.176"
KEY="C:/Users/THIEN NHI/Downloads/key.pem"
LOCAL="C:/AppServ/www/tuanhuy_computer"
REMOTE="/var/www/html/tuanhuy_computer"

echo "Syncing to EC2..."
rsync -avz \
  --exclude 'uploads/' \
  --exclude '.git/' \
  --exclude 'node_modules/' \
  --exclude 'storage/' \
  -e "ssh -i '$KEY'" \
  "$LOCAL/" "$SERVER:$REMOTE/"

echo "Deploy done!"
