#!/bin/bash

if [ -z $SNAPCHAT_USERNAME ] || [ -z $SNAPCHAT_PASSWORD ]; then
  echo "Can't run without a SNAPCHAT_USERNAME and SNAPCHAT_PASSWORD"
  exit 1
fi

cd /app
# composer install
echo "starting snap story downloader"
php index.php stories:log $SNAPCHAT_USERNAME $SNAPCHAT_PASSWORD "/app/stories.json" "/app/$(date +%Y%m%d)"
