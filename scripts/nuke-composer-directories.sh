#!/usr/bin/env bash

# The OG `composer nuke` command.
repo_root="$(git rev-parse --show-toplevel)"
pushd "${repo_root}" > /dev/null

rm -rf $(composer config bin-dir)
rm -rf $(composer config cache-dir)
rm -rf $(composer config data-dir)
rm -rf $(composer config vendor-dir)

installer_paths=( $(cat composer.json | jq '.extra["installer-paths"] | keys[]' -r) )
for i in "${installer_paths[@]}"; do
  this_path="${i%%\{*}"
  rm -rf "${this_path}"
done

popd > /dev/null
