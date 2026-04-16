#!/usr/bin/env bash

# Composer post-update-cmd hook
# See also:
# - ./composer-post-install.sh
# - ./composer-pre-update.sh
# - ./composer-pre-install.sh
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

git config core.hooksPath hooks/git
chmod 0755 ./hooks/git/pre-commit

# Remove the stale .git/hooks copy now that core.hooksPath is used.
rm -f .git/hooks/pre-commit

rm -rf ./vendor

popd > /dev/null
