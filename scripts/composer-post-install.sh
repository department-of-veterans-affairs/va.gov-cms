#!/usr/bin/env bash

# Composer post-install-cmd hook
# See also:
# - ./composer-post-update.sh
# - ./composer-pre-install.sh
# - ./composer-pre-update.sh
repo_root="$(git rev-parse --show-toplevel)";
pushd "${repo_root}" > /dev/null;

cp -r ./hooks/git/. ./.git/hooks/;
chmod 0777 .git/hooks/pre-commit;

ln -snf docroot/vendor/simplesamlphp/simplesamlphp/ simplesamlphp;
ln -snf docroot/vendor/simplesamlphp/saml2/ ./saml2;
cp -r \
  simplesamlphp-config-metadata/config \
  simplesamlphp-config-metadata/metadata \
  docroot/vendor/simplesamlphp/simplesamlphp/;
git update-index --skip-worktree samlsessiondb.sq2;

composer va:remove-git-dirs;

rm -r vendor;

popd > /dev/null;
