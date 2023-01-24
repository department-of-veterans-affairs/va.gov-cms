#!/usr/bin/env bash

# Copy migration ymls from va_gov_migrate to config/sync and run config
# import. Always edit in va_gov_migrate.

repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

cp -r ./docroot/modules/custom/va_gov_migrate/config/install/* ./config/sync
drush config-import -y
drush config-export -y

popd > /dev/null
