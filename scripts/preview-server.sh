#!/bin/sh

cd "${TUGBOAT_ROOT}/web"
NODE_ENV=production yarn preview --buildtype=vagovprod
