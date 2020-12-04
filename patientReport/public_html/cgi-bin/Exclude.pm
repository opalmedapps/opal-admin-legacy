#!C:\Strawberry\perl\bin\perl.exe
#
#	Simple package defines name exclusions from the reporting service (Test patients)
#	KAgnew Mar 2019
package Exclude;

use strict;
use warnings;

use base 'Exporter';
our @EXPORT = qw[$nameList];
#Add any additional names to this list in the same format. Change nothing else
our $nameList = "('TEST','QA_OPAL','Demo','TRANSITION','TelNum')";

1;