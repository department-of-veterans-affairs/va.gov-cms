#!/bin/bash

timeout 30m bash -c -- 'until $(curl --output /dev/null --silent --head --fail http://localhost:80); do echo site is not up yet ; sleep 30 ; done'
