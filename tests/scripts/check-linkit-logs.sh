#!/usr/bin/env bash

export FAILURE=0
for filename in config/sync/*.entity_form_display.*.*.default.yml
do
  if grep --quiet "\- link\b" ${filename}
  then
      export FAILURE=1
      echo "Link widget needs to be replaced with linkit widget in ${filename}"
  fi
done
if [ ${FAILURE} -eq 1 ]
then
  echo "To fix this test, ensure that all link fields use linkit widget"
  exit 1
fi

exit 0
