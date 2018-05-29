#!/bin/bash

# This script will make a local copy of the default config file
# If copies already exist, it will prompt the user to confirm overwrite.

# Flag
JSON_CONFIG=0

# JSON
if [ -f config.json ]; then
    JSON_CONFIG=1
    echo "config.json file already exists"
    while true; do
        read -p "Are you sure you want to overwrite config.json? [y/n]: " RESPONSE
        case $RESPONSE in
            [Yy]* ) cp ./default-config.json ./config.json; echo "Overwritten."; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi
if [ $JSON_CONFIG = 0 ]; then 
    cp ./default-config.json ./config.json
    echo "Copied JSON config file."
fi

echo "DONE."



