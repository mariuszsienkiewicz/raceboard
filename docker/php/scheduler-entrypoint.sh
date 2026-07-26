#!/bin/sh
while true; do
  php bin/console messenger:consume scheduler_import \
    --time-limit=3600 \
    --memory-limit=192M

  sleep 1
done
