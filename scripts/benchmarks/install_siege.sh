#!/usr/bin/env bash

# This script installs Siege by compiling it from source.  This does not (as of
# April 2021) require any additional packages or other tools to be installed
# on the Build-Release-Deploy environments.
set -e;
cd /tmp;
siege_version="4.0.9";
wget http://download.joedog.org/siege/siege-${siege_version}.tar.gz;
tar -zxvf siege-${siege_version}.tar.gz;
cd siege-${siege_version};
./configure;
make && make install;
