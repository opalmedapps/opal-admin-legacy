#!/bin/bash

# SPDX-FileCopyrightText: Copyright (C) 2023 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

set -euo pipefail

# export
# exit 1

# don't expose that PHP is used on server
# https://php.net/expose-php
sed -ri -e 's!expose_php =.*!expose_php = Off!g' /usr/local/etc/php/php.ini
# default timezone to the one Montreal falls in
# https://php.net/date.timezone
sed -ri -e 's!;date.timezone =!date.timezone = America/Toronto!g' /usr/local/etc/php/php.ini
# include environment variables $_ENV as available variables
# https://php.net/variables-order
sed -ri -e 's!variables_order = "GPCS"!variables_order = "EGPCS"!g' /usr/local/etc/php/php.ini
# increase max execution time due to size of labs
# https://php.net/max-execution-time
sed -ri -e 's!max_execution_time =.*!max_execution_time = 60!g' /usr/local/etc/php/php.ini
# allow more time to parse request data for labs
# https://php.net/max-input-time
sed -ri -e 's!max_input_time =.*!max_input_time = 120!g' /usr/local/etc/php/php.ini
# avoid labs being cut off
# https://php.net/max-input-vars
sed -ri -e 's!max_input_vars =.*!max_input_vars = 1000000!g' /usr/local/etc/php/php.ini
# increase memory limit due to large data being received
# https://php.net/memory-limit
sed -ri -e 's!memory_limit =.*!memory_limit = 4096M!g' /usr/local/etc/php/php.ini
# increase since educational material data can be quite large
# https://php.net/post-max-size
sed -ri -e 's!post_max_size =.*!post_max_size = 128M!g' /usr/local/etc/php/php.ini

if [ "$PHP_ENV" == "production" ]; then
    sed -i 's/error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT/error_reporting = E_ALL \& ~E_DEPRECATED \& ~E_STRICT \& ~E_WARNING/' /usr/local/etc/php/php.ini
fi