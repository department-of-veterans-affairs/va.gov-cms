#!/bin/sh
set -e;

url="$1/openapi/va_gov";
tmp_name=$(mktemp)
wget --output-document="${tmp_name}" "${url}"
mv {$tmp_name} "./docroot/openapi.json"
