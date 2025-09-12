FROM drupal:php8.4-fpm-bookworm

ARG RSYSLOG_CONF_FILE=/etc/rsyslog.conf
ARG RSYSLOG_CONF_DIR=/etc/rsyslog.d

RUN apt update && apt install -y \
  apt-utils \
  awscli \
  build-essential \
  chromium \
  default-mysql-client \
  gconf2 \
  git \
  gnupg \
  htop \
  inetutils-tools \
  jq \
  less \
  libasound2 \
  libasound2-plugins \
  libgtk-3-dev \
  libgtk2.0-dev \
  libnotify-dev \
  libnss3 \
  libxss1 \
  lsb-release \
  nano \
  net-tools \
  netcat-traditional \
  nodejs \
  npm \
  parallel \
  pigz \
  procps \
  pv \
  python3 \
  python3-pip \
  rsync \
  rsyslog \
  unzip \
  vim \
  wget \
  xvfb

RUN set -eux; \
# allow running as an arbitrary user (https://github.com/docker-library/php/issues/743)
	mkdir -p /var/www/html; \
	chown www-data:www-data /var/www/html; \
	chmod 1777 /var/www/html

ENV APACHE_CONFDIR=/etc/apache2
ENV APACHE_ENVVARS=$APACHE_CONFDIR/envvars
ENV APACHE_LOG_DIR=/var/log/apache2

RUN set -eux; \
	apt-get update; \
	apt-get install -y --no-install-recommends apache2 libapache2-mod-fcgid apache2-bin; \
	rm -rf /var/lib/apt/lists/*; \
	\
# generically convert lines like
#   export APACHE_RUN_USER=www-data
# into
#   : ${APACHE_RUN_USER:=www-data}
#   export APACHE_RUN_USER
# so that they can be overridden at runtime ("-e APACHE_RUN_USER=...")
	sed -ri 's/^export ([^=]+)=(.*)$/: ${\1:=\2}\nexport \1/' "$APACHE_ENVVARS"; \
	\
# setup directories and permissions
	. "$APACHE_ENVVARS"; \
	for dir in \
		"$APACHE_LOCK_DIR" \
		"$APACHE_RUN_DIR" \
		"$APACHE_LOG_DIR" \
# https://salsa.debian.org/apache-team/apache2/-/commit/b97ca8714890ead1ba6c095699dde752e8433205
		"$APACHE_RUN_DIR/socks" \
	; do \
		rm -rvf "$dir"; \
		mkdir -p "$dir"; \
		chown "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$dir"; \
# allow running as an arbitrary user (https://github.com/docker-library/php/issues/743)
		chmod 1777 "$dir"; \
	done; \
	\
# delete the "index.html" that installing Apache drops in here
	rm -rvf /var/www/html/*; \
	\
# logs should go to stdout / stderr
	ln -sfT /dev/stderr "$APACHE_LOG_DIR/error.log"; \
	ln -sfT /dev/stdout "$APACHE_LOG_DIR/access.log"; \
	ln -sfT /dev/stdout "$APACHE_LOG_DIR/other_vhosts_access.log"; \
	chown -R --no-dereference "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$APACHE_LOG_DIR"
# Enable Apache modules
RUN a2enmod proxy proxy_fcgi rewrite mpm_event status cache cache_disk headers expires

# Create cache directory
RUN mkdir -p /var/cache/apache2/mod_cache_disk && \
    chown www-data:www-data /var/cache/apache2/mod_cache_disk && \
    chmod 755 /var/cache/apache2/mod_cache_disk

RUN { \
    echo '<FilesMatch \.php$>'; \
    echo '\tSetHandler "proxy:fcgi://127.0.0.1:9000"'; \
    echo '</FilesMatch>'; \
    echo; \
    echo '<Proxy "fcgi://127.0.0.1/" enablereuse=on max=50>'; \
    echo '\tProxySet timeout=600'; \
    echo '\tProxySet retry=30'; \
    echo '\tProxySet connectiontimeout=30'; \
    echo '</Proxy>'; \
    echo; \
    echo 'DirectoryIndex disabled'; \
    echo 'DirectoryIndex index.php index.html'; \
    echo; \
    echo '<Directory /var/www/>'; \
    echo '\tOptions -Indexes'; \
    echo '\tAllowOverride All'; \
    echo '</Directory>'; \
    echo 'ErrorLog /proc/1/fd/2'; \
    echo 'CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo; \
    echo '# Health check endpoint'; \
    echo '<Location "/pod-health">'; \
    echo '\tSetHandler server-status'; \
    echo '\tRequire ip 127.0.0.1'; \
    echo '\tRequire ip 10.0.0.0/8'; \
    echo '\tRequire ip 172.16.0.0/12'; \
    echo '\tRequire ip 192.168.0.0/16'; \
    echo '</Location>'; \
    echo; \
    echo '# Health check endpoint'; \
    echo '<Location "/pod-health">'; \
    echo '\tSetHandler server-status'; \
    echo '\tRequire ip 127.0.0.1'; \
    echo '\tRequire ip 10.0.0.0/8'; \
    echo '\tRequire ip 172.16.0.0/12'; \
    echo '\tRequire ip 192.168.0.0/16'; \
    echo '</Location>'; \
    echo; \
    echo '# Timeout settings - reduced for stability'; \
    echo 'ProxyTimeout 600'; \
    echo 'Timeout 600'; \
    echo; \
    echo '# KeepAlive settings'; \
    echo 'KeepAlive On'; \
    echo 'KeepAliveTimeout 5'; \
    echo 'MaxKeepAliveRequests 100'; \
    echo; \
    echo '# Conservative cache configuration'; \
    echo 'CacheRoot /var/cache/apache2/mod_cache_disk'; \
    echo 'CacheEnable disk /sites/default/files'; \
    echo 'CacheDirLevels 1'; \
    echo 'CacheDirLength 2'; \
    echo 'CacheMaxFileSize 33554432'; \
    echo 'CacheMinFileSize 1'; \
    echo 'CacheDefaultExpire 300'; \
    echo 'CacheMaxExpire 3600'; \
    echo 'CacheIgnoreHeaders Set-Cookie Cookie Authorization'; \
    echo; \
    echo '# Only cache specific static files'; \
    echo '<LocationMatch "^/sites/default/files/.*\.(css|js|png|jpg|jpeg|gif|ico|svg)$">'; \
    echo '\tExpiresActive On'; \
    echo '\tExpiresDefault "access plus 1 hour"'; \
    echo '\tHeader append Cache-Control "public"'; \
    echo '</LocationMatch>'; \
    echo; \
    echo '# Explicitly disable caching for everything else'; \
    echo '<LocationMatch "\.php$">'; \
    echo '\tCacheDisable'; \
    echo '\tHeader set Cache-Control "no-cache, no-store, must-revalidate"'; \
    echo '</LocationMatch>'; \
    echo; \
    echo '# KeepAlive settings'; \
    echo 'KeepAlive On'; \
    echo 'KeepAliveTimeout 4'; \
    echo 'MaxKeepAliveRequests 1000'; \
    echo; \
    echo '# Cache configuration'; \
    echo 'CacheRoot /var/cache/apache2/mod_cache_disk'; \
    echo 'CacheEnable disk /'; \
    echo 'CacheDirLevels 2'; \
    echo 'CacheDirLength 1'; \
    echo 'CacheMaxFileSize 1000000'; \
    echo 'CacheMinFileSize 1'; \
    echo 'CacheDefaultExpire 3600'; \
    echo 'CacheMaxExpire 86400'; \
    echo 'CacheIgnoreHeaders Set-Cookie'; \
    echo; \
    echo '# Cache static files'; \
    echo '<LocationMatch "\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$">'; \
    echo '\tExpiresActive On'; \
    echo '\tExpiresDefault "access plus 1 month"'; \
    echo '\tHeader append Cache-Control "public"'; \
    echo '</LocationMatch>'; \
    echo; \
    echo '# Dont cache PHP files'; \
    echo '<LocationMatch "\.php$">'; \
    echo '\tCacheDisable on'; \
    echo '</LocationMatch>'; \
  } | tee "$APACHE_CONFDIR/conf-available/docker-php.conf" \
  && a2enconf docker-php

RUN { \
    echo 'ServerLimit 20'; \
    echo 'MaxRequestWorkers 400'; \
    echo 'ThreadsPerChild 20'; \
    echo 'ThreadLimit 20'; \
    echo 'StartServers 5'; \
    echo 'MinSpareThreads 40'; \
    echo 'MaxSpareThreads 80'; \
    echo 'MaxConnectionsPerChild 10000'; \
    echo 'MaxRequestsPerChild 1000'; \
    echo 'AsyncRequestWorkerFactor 2'; \
   } | tee "$APACHE_CONFDIR/conf-available/mpm-event.conf" \
   && a2enconf mpm-event

COPY ./docker-conf-files/apache2-foreground /usr/local/bin/
RUN chmod uga+x /usr/local/bin/apache2-foreground

# Add the install-va-certs script to install VA certificates
ADD --chmod=0755 ./docker-conf-files/install-va-certs.sh /usr/local/bin/
RUN install-va-certs.sh
RUN apt update && apt install -y ca-certificates
RUN update-ca-certificates

# Install and configure prometheus
RUN mkdir -p /opt/prometheus && \
    cd /opt/prometheus && \
    wget https://github.com/prometheus/prometheus/releases/download/v2.47.0/prometheus-2.47.0.linux-amd64.tar.gz && \
    tar -xvf prometheus-2.47.0.linux-amd64.tar.gz --strip-components=1 && \
    rm prometheus-2.47.0.linux-amd64.tar.gz

# Create Prometheus config
RUN mkdir -p /etc/prometheus
COPY ./docker-conf-files/prometheus.yml /etc/prometheus/prometheus.yml

# Configure rsyslog
COPY ./docker-conf-files/drupal.conf ${RSYSLOG_CONF_DIR}/drupal.conf
RUN sed -i -e '/imklog/s/^/#/' -e '/ActionFileDefaultTemplate/s/^/#/' ${RSYSLOG_CONF_FILE} \
    && echo "local6.* /var/log/drupal.log" >> ${RSYSLOG_CONF_FILE} \
    && ln -sfT /proc/1/fd/1 /var/log/drupal.log \
    && unlink ${APACHE_LOG_DIR}/access.log \
    && unlink ${APACHE_LOG_DIR}/other_vhosts_access.log

# Configure PHP-FPM
RUN sed -i 's/;listen.allowed_clients = 127.0.0.1/listen.allowed_clients = 127.0.0.1/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.max_children = 5/pm.max_children = 100/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.start_servers = 2/pm.start_servers = 20/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.min_spare_servers = 1/pm.min_spare_servers = 10/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/pm.max_spare_servers = 3/pm.max_spare_servers = 30/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;pm.max_requests = 500/pm.max_requests = 1000/g' /usr/local/etc/php-fpm.d/www.conf && \
    sed -i 's/;clear_env = no/clear_env = no/g' /usr/local/etc/php-fpm.d/www.conf

# Helper from this repo: https://github.com/mlocati/docker-php-extension-installer
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions apcu bcmath bz2 calendar csv ddtrace event exif gettext igbinary intl ldap memcached msgpack pcntl shmop sockets sysvmsg sysvsem sysvshm uploadprogress xsl
# Install the PHP extensions for Datadog APM
RUN php $(curl -w "%{filename_effective}" -LO $(curl -s https://api.github.com/repos/DataDog/dd-trace-php/releases | grep browser_download_url | grep 'setup[.]php' | head -n 1 | cut -d '"' -f 4)) --enable-profiling --php-bin=$(basename $(realpath $(which php)))

RUN sh -c "$(curl --location https://taskfile.dev/install.sh)" -- -d

RUN { \
    echo '[global]'; \
    echo 'error_log = /proc/1/fd/2'; \
    echo 'daemonize = no'; \
    echo '[www]'; \
    echo 'access.log = /proc/1/fd/1'; \
    echo 'slowlog = /proc/1/fd/2'; \
    echo 'request_slowlog_timeout = 10s'; \
    echo 'request_terminate_timeout = 600s'; \
    echo 'pm.status_path = /fpm-status'; \
    echo 'ping.path = /fpm-ping'; \
    echo 'pm.max_requests = 1000'; \
    echo 'rlimit_files = 131072'; \
    echo 'rlimit_core = unlimited'; \
} >> /usr/local/etc/php-fpm.d/kubernetes.conf

# set recommended opcache settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
  echo 'opcache.memory_consumption=1024'; \
  echo 'opcache.interned_strings_buffer=16'; \
  echo 'opcache.max_accelerated_files=42000'; \
  echo 'opcache.revalidate_freq=0'; \
  echo 'opcache.fast_shutdown=1'; \
  echo 'opcache.validate_timestamps=0'; \
  } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# set recommended opcache settings
RUN { \
    echo 'zend_extension=opcache'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
} > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# set recommended apcu settings
RUN { \
    echo 'extension=apcu.so'; \
    echo 'apc.enable_cli=1'; \
    echo 'apc.shm_size=256M'; \
    echo 'apc.rfc1867=1'; \
    echo 'apc.ttl=7200'; \
    echo 'apc.user_ttl=7200'; \
    echo 'apc.gc_ttl=3600'; \
} > /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/
# set recommended PHP.ini settings
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/;max_input_vars = 1000/max_input_vars = 10000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 4G/g' /usr/local/etc/php/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 32M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_execution_time = 30/max_execution_time = 1800/g' /usr/local/etc/php/php.ini

RUN rm -rf /opt/drupal

ENV BUILD_ENV=eks

EXPOSE 80 9090

# Create startup script to run both Apache and PHP-FPM
RUN echo '#!/bin/bash\n\
php-fpm -D\n\
# Start rsyslog\n\
rsyslogd\n\
# Start Prometheus\n\
/opt/prometheus/prometheus --config.file=/etc/prometheus/prometheus.yml &\n\
# Start Apache in the foreground\n\
apache2-foreground' > /usr/local/bin/start-services.sh && \
chmod +x /usr/local/bin/start-services.sh

ENV PATH=${PATH}:/opt/drupal/vendor/bin:/opt/drupal/docroot/core/scripts:/opt/drupal/bin
# vim:set ft=dockerfile:
CMD ["/usr/local/bin/start-services.sh"]
