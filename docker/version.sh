#!/bin/sh
echo 'version:' > VERSION
git describe >> VERSION
echo 'branch:' >> VERSION
git rev-parse --abbrev-ref HEAD >> VERSION

# never exit
tail -f /dev/null