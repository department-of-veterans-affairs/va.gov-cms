#!/bin/bash

## Description: Run lint tests
## Usage: test-lint
find \
  docroot/modules/custom \
  docroot/themes \
  \( \
    -name '*.inc' \
    -o -name '*.php' \
    -o -name '*.module' \
    -o -name '*.install' \
  \) \
  -print0 \
  | xargs \
    -0 \
    -n1 php \
    -l \
    2>&1
