#!/usr/bin/env bash

export FAILURE=0
for filename in config/sync/core.entity_form_display.node.*.default.yml
do
  if grep --quiet --after-context=1 '\- moderation_state' ${filename}
  then
    if ! grep --after-context=1 '\- moderation_state' ${filename}|grep --quiet '\- revision_log'
    then
      export FAILURE=1
      echo "The revision_log field was not found in ${filename}."
    fi
  fi
done
if [ ${FAILURE} -eq 1 ]
then
  echo "To fix this test, ensure that all content types with the moderation_state field"
  echo "also have the revision_log field directly below it."
  exit 1
fi

exit 0
