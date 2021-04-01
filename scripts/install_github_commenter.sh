#!/bin/sh
set -e;
destination="${1:-./bin}";
version="${2:-0.8.0}";
executable_path="${destination}/github-commenter";
curl --location "https://github.com/cloudposse/github-commenter/releases/download/${version}/github-commenter_linux_amd64" --output "${executable_path}";
chmod +x "${executable_path}";
