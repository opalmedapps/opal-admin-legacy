#!/usr/bin/perl

# SPDX-FileCopyrightText: Copyright (C) 2020 Opal Health Informatics Group at the Research Institute of the McGill University Health Centre <john.kildea@mcgill.ca>
#
# SPDX-License-Identifier: AGPL-3.0-or-later

use Cwd 'abs_path';

# chdir("/var/www/html/ymo/opalAdmin/publisher/modules/") or die "cannot change: $!\n";

use Time::Piece;
use POSIX;
use Storable qw(dclone);
use File::Basename;
use File::Spec;
use JSON;
use MIME::Lite;
use Data::Dumper;
use Net::Address::IP::Local;

# # Get directory path of this file
# my $path_name = abs_path($0);

use lib dirname($0) . ''; # specify where are modules are -- $0 = this script's location
use Configs;
use Database;
use PushNotification; # Our custom push notification module

# # PushNotification::sendPushNotification($patientser, $ser, 'NewLabResult');
PushNotification::sendPushNotification($ARGV[0], $ARGV[1], $ARGV[2]);

# Exit smoothly
1;