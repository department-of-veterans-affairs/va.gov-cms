#!/bin/bash

eval $(ssh-agent -s)
# `ssh` cannot use file descriptors/pipes/process substitution, create files as workaround.
# @see https://unix.stackexchange.com/q/500044/27902
cat  <(echo "$VA_SOCKS_PROXY_PRIVATE_KEY") > /tmp/va-socks-proxy-private-key
cat  <(echo "$VA_SOCKS_PROXY_SSH_CONFIG") > /tmp/va-socks-proxy-ssh-config

ssh-add <(echo "$VA_SOCKS_PROXY_PRIVATE_KEY")
autossh -v \
  -M 0 \
  -4 \
  -o StrictHostKeyChecking=no \
  -F /tmp/va-socks-proxy-ssh-config \
  -i /tmp/va-socks-proxy-private-key socks \
  -D "0.0.0.0:2001" \
  -N
