#!/bin/bash

# Install ddev
curl https://raw.githubusercontent.com/drud/ddev/master/scripts/install_ddev.sh | bash

# Add upstream git remote.
git remote add upstream https://github.com/department-of-veterans-affairs/va.gov-cms.git

# Provide php symlink for vscode extensions.
if command -v /opt/php/lts/bin/php; then sudo ln -s /opt/php/lts/bin/php /usr/bin; fi

# Start ddev.
ddev start

# Import the database.
ddev pull va

echo
echo "All done! Welcome to the VA.gov CMS :-)"
