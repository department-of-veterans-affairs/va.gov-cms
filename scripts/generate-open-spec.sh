#!/bin/sh
set -e;

url="{$1}/jsonapi/vagov";
tmp_name=$(mktemp)
wget --output-document="${tmp_name}" "{$url}"
mv {$tmp_name} "./docroot/openapi.json"
