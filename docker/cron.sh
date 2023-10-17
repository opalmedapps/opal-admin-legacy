#!/bin/sh

echo 'version:' > VERSION
git describe >> VERSION
echo 'branch:' >> VERSION
git rev-parse --abbrev-ref HEAD >> VERSION

set -eu

exec busybox crond -f -l 8 -L /dev/stdout
