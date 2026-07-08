#!/bin/sh
while true; do
  php bin/console messenger:consume async \
    --time-limit=3600 \
    --memory-limit=192M \
    || exit 1

  sleep 1
done
