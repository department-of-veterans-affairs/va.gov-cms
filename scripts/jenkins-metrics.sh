#!/bin/bash

# requirements

# socks proxy running on port 2001

# jq command must be installed

# add to ~/.bash_profile or other shell:
# export JENKINS_USERNAME=your-name
# export JENKINS_API_KEY=yourapikey


BUILD_INFO=`curl -s -X POST -L \
    --socks5-hostname localhost:2001 \
    --user $JENKINS_USERNAME:$JENKINS_API_KEY \
    http://jenkins.vfs.va.gov/job/builds/job/vets-website-content-vagovprod/lastSuccessfulBuild/api/json`

BUILD_NUMBER=`echo $BUILD_INFO | jq '.number'`
    
BUILD_DURATION=`echo $BUILD_INFO | jq '.duration'` # msec

BUILD_TIMESTAMP=`echo $BUILD_INFO | jq '.timestamp'`





BUILD_LOG=`curl -s -X POST -L \
    --socks5-hostname localhost:2001 \
    --user $JENKINS_USERNAME:$JENKINS_API_KEY \
    http://jenkins.vfs.va.gov/job/builds/job/vets-website-content-vagovprod/lastSuccessfulBuild/consoleText`



GQL_TIME=`echo $BUILD_LOG | grep -oP 'queries in \d+s' | grep -oP '\d+'` # seconds

GQL_PAGES=`echo $BUILD_LOG | grep -oP 'with \d+ pages' | grep -oP '\d+'`

BUILD_TIME=`echo $BUILD_LOG | grep -oP 'Done in \d+\.\d+s' | grep -oP '\d+\.\d+' | tail -1` # seconds


printf "build number: $BUILD_NUMBER
build duration (msec): $BUILD_DURATION
build timestamp: $BUILD_TIMESTAMP
gql query time (s): $GQL_TIME
gql pages: $GQL_PAGES
build time (s): $BUILD_TIME
"




