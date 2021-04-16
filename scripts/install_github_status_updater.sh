#!/bin/sh
set -e;
destination="${1:-./bin}";
version="${2:-0.5.0}";
executable_path="${destination}/github-status-updater";
curl --location "https://github.com/cloudposse/github-status-updater/releases/download/${version}/github-status-updater_linux_amd64" --output "${executable_path}";
chmod +x "${executable_path}";
