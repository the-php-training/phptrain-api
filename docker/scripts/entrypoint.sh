#!/bin/sh

set -e

WWWUSER=${WWWUSER:-1000}
WWWGROUP=${WWWGROUP:-1000}

# create group if it doesn't exist
if ! getent group "$WWWGROUP" >/dev/null; then
    addgroup -g "$WWWGROUP" appgroup
fi

# create user if it doesn't exist
if ! id -u "$WWWUSER" >/dev/null 2>&1; then
    adduser -u "$WWWUSER" -G appgroup -s /bin/sh -D appuser
fi

# fallback command
CMD="php /var/www/bin/hyperf.php start"

# run the swoole server (or any other provided command)
if [ "$1" = "start-server" ]; then
    exec gosu "$WWWUSER" sh -c "$CMD"
else
    exec gosu "$WWWUSER" "$@"
fi
