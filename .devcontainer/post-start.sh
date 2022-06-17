#!/bin/bash

## https://code.visualstudio.com/docs/remote/devcontainerjson-reference#_lifecycle-scripts

# Start ddev if it's not already running.
if ! docker ps | grep ddev > /dev/null; then
  ddev start
fi
