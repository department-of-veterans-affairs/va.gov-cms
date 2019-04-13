#!/bin/bash

HTTP_CODE_STATUS='200'
HTTP_PORT="80"
SLEEP_TIME="20"

while true
do
  STATUS=$(curl -s -o /dev/null -w '%{http_code}' http://localhost:$HTTP_PORT)
  if [ $STATUS -eq $HTTP_CODE_STATUS ]; then
    echo "Got 200! CMS service is up!"
    break
  else
    echo "Got $STATUS : CMS Service is not up yet, retrying..."
  fi
  sleep $SLEEP_TIME
done