#!/usr/bin/env bash

# Composer post-install-cmd hook
# See also:
# - ./composer-post-update.sh
# - ./composer-pre-install.sh
# - ./composer-pre-update.sh
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

git config core.hooksPath hooks/git
chmod 0755 ./hooks/git/pre-commit

# Remove the stale .git/hooks copy now that core.hooksPath is used.
rm -f .git/hooks/pre-commit

ln -snf docroot/vendor/simplesamlphp/simplesamlphp/ simplesamlphp
ln -snf docroot/vendor/simplesamlphp/saml2/ ./saml2
cp -r \
  simplesamlphp-config-metadata/config \
  simplesamlphp-config-metadata/metadata \
  docroot/vendor/simplesamlphp/simplesamlphp/

if [ -f "samlsessiondb.sq2" ]; then
  git update-index --skip-worktree samlsessiondb.sq2
fi

if [ -f "samlsessiondb.sq3" ]; then
  git update-index --skip-worktree samlsessiondb.sq3
fi

rm -r vendor

popd > /dev/null
