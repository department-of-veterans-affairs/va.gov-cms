#!/usr/bin/env bash

# Source from https://gist.github.com/tylerssn/8923149702d4a796c5e103412c2370c3

if [ ! -z "${BLACKFIRE_CLIENT_ID}" ]; then 

    # Configure Blackfire Repository
    wget -q -O - https://packages.blackfire.io/gpg.key | apt-key add -
    echo "deb http://packages.blackfire.io/debian any main" | tee /etc/apt/sources.list.d/blackfire.list
    apt-get update

    # Install Blackfire Agent
    apt-get install blackfire-agent -y
    printf "%s\n" $BLACKFIRE_CLIENT_ID $BLACKFIRE_CLIENT_TOKEN | blackfire config

    # Start Blackfire.
    /etc/init.d/blackfire-agent restart

    # Install Blackfire Probe
    version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;")
    curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version
    tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp
    mv /tmp/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so

    # Enable Blackfire Probe
    docker-php-ext-enable blackfire
    /etc/init.d/apache2 reload

fi
