#!/bin/bash

# assumes socks is running on port 2001

# add to ~/.bash_profile or other shell:

# export JENKINS_USERNAME=your-name
# export JENKINS_API_KEY=yourapikey

# bash ./jenkins-collect-metrics.sh


LOG=$(curl -X POST -L \
    --socks5-hostname localhost:2001 \
    --user $JENKINS_USERNAME:$JENKINS_API_KEY \
    http://jenkins.vfs.va.gov/job/builds/job/vets-website-content-vagovprod/1216/consoleText)

echo $LOG