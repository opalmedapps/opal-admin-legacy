#!/bin/bash

# This script will make a local copy of the default config file
# If copies already exist, it will prompt the user to confirm overwrite.

# Flag
ENV_FILE=0

# env file
if [ -f .env ]; then
    $ENV_FILE=1
    echo ".env file already exists"
    while true; do
        read -p "Are you sure you want to overwrite .env? [y/n]: " RESPONSE
        case $RESPONSE in
            [Yy]* ) cp ./.env.sample ./.env; echo "Overwritten."; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi
if [ $ENV_FILE = 0 ]; then
    cp ./.env.sample ./.env
    echo "Copied .env file."
fi

echo "DONE."



