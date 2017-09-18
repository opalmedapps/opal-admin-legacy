#!/bin/bash

# this script will make local copies of default config files
# for installation use. If copies already exist, it will 
# prompt the user to confirm overwrite.

JS_CONFIG=0
PHP_CONFIG=0
PERL_CONFIG=0

# JS
if [ -f js/config.js ]; then
    JS_CONFIG=1
    echo "js/config.js file already exists"
    while true; do
        read -p "Are you sure you want to overwrite js/config.js? [y/n]: " RESPONSE
        case $RESPONSE in
            [Yy]* ) cp ./js/default-config.js ./js/config.js; echo "Overwritten"; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi
if [ $JS_CONFIG = 0 ]; then 
    cp ./js/default-config.js ./js/config.js
    echo "Copied JS config file."
fi

# PHP
if [ -f php/config.php ]; then
    PHP_CONFIG=1
    echo "php/config.php file already exists"
    while true; do
        read -p "Are you sure you want to overwrite php/config.php? [y/n]: " RESPONSE
        case $RESPONSE in
            [Yy]* ) cp ./php/default-config.php ./php/config.php; echo "Overwritten"; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi
if [ $PHP_CONFIG = 0 ]; then 
    cp ./php/default-config.php ./php/config.php
    echo "Copied PHP config file."
fi

# PERL
if [ -f publisher/modules/Configs.pm ]; then
    PERL_CONFIG=1
    echo "publisher/modules/Configs.pm file already exists"
    while true; do
        read -p "Are you sure you want to overwrite publisher/modules/Configs.pm? [y/n]: " RESPONSE
        case $RESPONSE in
            [Yy]* ) cp ./publisher/modules/default-Configs.pm ./publisher/modules/Configs.pm; echo "Overwritten"; break;;
            [Nn]* ) break;;
            * ) echo "Please answer yes or no.";;
        esac
    done
fi
if [ $PERL_CONFIG = 0 ]; then 
    cp ./publisher/modules/default-Configs.pm ./publisher/modules/Configs.pm
    echo "Copied PERL config file."
fi

echo "DONE."



