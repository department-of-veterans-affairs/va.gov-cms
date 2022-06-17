#!/bin/bash

## https://code.visualstudio.com/docs/remote/devcontainerjson-reference#_lifecycle-scripts

# Install ddev.
curl https://raw.githubusercontent.com/drud/ddev/master/scripts/install_ddev.sh | bash

# Add upstream git remote.
git remote add upstream https://github.com/department-of-veterans-affairs/va.gov-cms.git

# Provide php symlink for vscode extensions.
if command -v /opt/php/lts/bin/php; then sudo ln -s /opt/php/lts/bin/php /usr/bin; fi

# Populate a .env file for ddev and friends.
cp .env.example .env

# To avoid a blocking prompt. See https://ddev.readthedocs.io/en/stable/users/cli-usage/#opt-in-usage-information.
ddev config global --instrumentation-opt-in=true

# Start ddev.
ddev start

# Import the database.
ddev pull va --skip-files -y
