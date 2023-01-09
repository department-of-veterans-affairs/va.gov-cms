#!/usr/bin/env bash

repo_root="$(git rev-parse --show-toplevel)";
pushd "${repo_root}" > /dev/null;

ssh socks -D 2001 -N &
sudo -E sshuttle -r dsva@52.222.32.121 -e 'ssh -A ' 10.0.0.0/8;

popd > /dev/null;