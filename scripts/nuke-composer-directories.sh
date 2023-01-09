#!/usr/bin/env bash

rm -rf $(composer config bin-dir)
rm -rf $(composer config cache-dir)
rm -rf $(composer config data-dir)
rm -rf $(composer config vendor-dir)

installer_paths=( $(cat composer.json | jq '.extra["installer-paths"] | keys[]' -r) )
for i in "${installer_paths[@]}"; do
  this_path="${i%%\{*}"
  rm -rf "${this_path}";
done
