#!/bin/bash

# download lando https://docs.lando.dev/basics/installation.html#linux
wget -O ./tmp/lando-stable.deb https://files.devwithlando.io/lando-stable.deb

# install lando https://docs.lando.dev/basics/installation.html#docker-ce
sudo dpkg -i --ignore-depends=docker-ce ./tmp/lando-stable.deb

# Add upstream git remote.
cd ~/workspace/va.gov-cms && git remote add upstream https://github.com/department-of-veterans-affairs/va.gov-cms.git
