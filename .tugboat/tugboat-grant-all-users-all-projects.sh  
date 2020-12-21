#!/usr/bin/env bash

projects=$(tugboat ls projects --quiet)
user_ids=$(tugboat ls keys children=false --quiet | tr "[:space:]" "," | sed 's/,*$//g')

for project in $projects
do
  tugboat grant "$project" users="$user_ids"
done
