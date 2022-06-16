#!/bin/bash

# Start ddev if it's not already running.
if ! docker ps | grep ddev > /dev/null; then
  ddev start
fi
