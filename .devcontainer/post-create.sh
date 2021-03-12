#!/bin/bash

# Download lando https://docs.lando.dev/basics/installation.html#linux
wget -O /tmp/lando-stable.deb https://files.devwithlando.io/lando-stable.deb

# Install lando https://docs.lando.dev/basics/installation.html#docker-ce
sudo dpkg -i --ignore-depends=docker-ce /tmp/lando-stable.deb

# Remove lando package.
rm /tmp/lando-stable.deb

# Add upstream git remote.
git remote add upstream https://github.com/department-of-veterans-affairs/va.gov-cms.git

# Provide php symlink for vscode extensions.
if command -v /opt/php/lts/bin/php; then sudo ln -s /opt/php/lts/bin/php /usr/bin; fi

# Start lando.
lando start

# Import the database.
./scripts/sync-db.sh
./scripts/sync-files.sh
