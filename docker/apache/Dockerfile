FROM drupal:php8.4-fpm-bookworm

# Consolidate package installation into a single layer
RUN apt-get update && apt-get install -y --no-install-recommends \
  apt-utils \
  awscli \
  build-essential \
  ca-certificates \
  chromium \
  default-mysql-client \
  gconf2 \
  git \
  gnupg \
  htop \
  inetutils-tools \
  jq \
  less \
  libapache2-mod-fcgid \
  apache2 \
  apache2-bin \
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
  tini \
  unzip \
  vim \
  wget \
  xvfb \
  && rm -rf /var/lib/apt/lists/*

# Set up user, permissions, and clean default web content
RUN mkdir -p /var/www/html && \
    chown www-data:www-data /var/www/html && \
    chmod 1777 /var/www/html && \
    rm -rvf /var/www/html/*

# Configure Apache
ENV APACHE_CONFDIR=/etc/apache2
ENV APACHE_ENVVARS=$APACHE_CONFDIR/envvars
ENV APACHE_LOG_DIR=/var/log/apache2

# Configure Apache environment, directories, and logging
RUN sed -ri 's/^export ([^=]+)=(.*)$/: ${\1:=\2}\nexport \1/' "$APACHE_ENVVARS" && \
    . "$APACHE_ENVVARS" && \
    for dir in "$APACHE_LOCK_DIR" "$APACHE_RUN_DIR" "$APACHE_LOG_DIR" "$APACHE_RUN_DIR/socks"; do \
        rm -rvf "$dir"; \
        mkdir -p "$dir"; \
        chown "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$dir"; \
        chmod 1777 "$dir"; \
    done && \
    ln -sfT /dev/stderr "$APACHE_LOG_DIR/error.log" && \
    ln -sfT /dev/stdout "$APACHE_LOG_DIR/access.log" && \
    ln -sfT /dev/stdout "$APACHE_LOG_DIR/other_vhosts_access.log" && \
    chown -R --no-dereference "$APACHE_RUN_USER:$APACHE_RUN_GROUP" "$APACHE_LOG_DIR"

# Enable Apache modules
RUN a2enmod proxy proxy_fcgi rewrite mpm_event status cache headers expires

# Configure Apache proxy and directory settings
# Configure Apache proxy and directory settings
RUN { \
    echo '<FilesMatch \.php$>'; \
    echo '  SetHandler "proxy:fcgi://127.0.0.1:9000"'; \
    echo '</FilesMatch>'; \
    echo; \
    echo '<Proxy "fcgi://127.0.0.1:9000" enablereuse=on max=120>'; \
    echo '  ProxySet timeout=310'; \
    echo '</Proxy>'; \
    echo '<Proxy "fcgi://127.0.0.1:9001" enablereuse=on max=10>'; \
    echo '  ProxySet timeout=610'; \
    echo '</Proxy>'; \
    echo; \
    echo '<LocationMatch "^/admin(?:/.*)?$">'; \
    echo '  SetHandler "proxy:fcgi://127.0.0.1:9001"'; \
    echo '</LocationMatch>'; \
    echo; \
    echo 'DirectoryIndex disabled'; \
    echo 'DirectoryIndex index.php index.html'; \
    echo; \
    echo '<Directory /var/www/>'; \
    echo '  Options -Indexes'; \
    echo '  AllowOverride All'; \
    echo '</Directory>'; \
    echo; \
    echo 'ProxyTimeout 310'; \
    echo 'Timeout 310'; \
    echo 'KeepAlive On'; \
    echo 'KeepAliveTimeout 5'; \
    echo 'MaxKeepAliveRequests 100'; \
  } > "$APACHE_CONFDIR/conf-available/docker-php.conf" \
  && a2enconf docker-php

# Tune Apache MPM Event settings for 4 CPU / 16GB Pods
RUN { \
    echo '# Tuned for 12 CPU / 24GB Pods'; \
    echo 'ServerLimit 48'; \
    echo 'StartServers 9'; \
    echo 'MinSpareThreads 225'; \
    echo 'MaxSpareThreads 750'; \
    echo 'ThreadsPerChild 25'; \
    echo 'MaxRequestWorkers 1200'; \
    echo 'MaxConnectionsPerChild 10000'; \
   } > "$APACHE_CONFDIR/conf-available/mpm-event.conf"

COPY ./docker-conf-files/apache2-foreground /usr/local/bin/
RUN chmod uga+x /usr/local/bin/apache2-foreground

# Add the install-va-certs script to install VA certificates
ADD --chmod=0755 ./docker-conf-files/install-va-certs.sh /usr/local/bin/
RUN install-va-certs.sh && update-ca-certificates

# Install and configure Prometheus
RUN mkdir -p /opt/prometheus && \
    cd /opt/prometheus && \
    wget https://github.com/prometheus/prometheus/releases/download/v2.47.0/prometheus-2.47.0.linux-amd64.tar.gz && \
    tar -xvf prometheus-2.47.0.linux-amd64.tar.gz --strip-components=1 && \
    rm prometheus-2.47.0.linux-amd64.tar.gz
COPY ./docker-conf-files/prometheus.yml /etc/prometheus/prometheus.yml

# Configure rsyslog to forward logs to stdout
COPY ./docker-conf-files/drupal.conf /etc/rsyslog.d/drupal.conf
RUN sed -i -e '/imklog/s/^/#/' /etc/rsyslog.conf \
    && echo "local6.* /var/log/drupal.log" >> /etc/rsyslog.conf \
    && ln -sfT /proc/1/fd/1 /var/log/drupal.log \
    && unlink ${APACHE_LOG_DIR}/access.log \
    && unlink ${APACHE_LOG_DIR}/other_vhosts_access.log

# Helper to install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

# Install PHP extensions
RUN install-php-extensions apcu bcmath bz2 calendar csv event exif gettext igbinary intl ldap memcached msgpack pcntl shmop sockets sysvmsg sysvsem sysvshm uploadprogress xsl

# Install Datadog APM
RUN php $(curl -w "%{filename_effective}" -LO $(curl -s https://api.github.com/repos/DataDog/dd-trace-php/releases | grep browser_download_url | grep 'setup[.]php' | head -n 1 | cut -d '"' -f 4)) --enable-profiling --php-bin=$(basename $(realpath $(which php)))

# Install Taskfile
RUN sh -c "$(curl --location https://taskfile.dev/install.sh)" -- -d -b /usr/local/bin

# --- Consolidated and Tuned Configurations ---

RUN rm -f /usr/local/etc/php-fpm.d/www.conf.default && { \
    echo '[global]'; \
    echo 'error_log = /proc/1/fd/2'; \
    echo 'daemonize = no'; \
    echo; \
    echo '[www]'; \
    echo 'user = www-data'; \
    echo 'group = www-data'; \
    echo; \
    echo 'listen = 127.0.0.1:9000'; \
    echo 'listen.allowed_clients = 127.0.0.1'; \
    echo; \
    echo '; Use "dynamic" for variable loads in Kubernetes to optimize memory.'; \
    echo 'pm = dynamic'; \
    echo '; Calculation: (24GB RAM - 4GB buffer) / ~80MB per process = ~250.'; \
    echo 'pm.max_children = 250'; \
    echo 'pm.start_servers = 60'; \
    echo 'pm.min_spare_servers = 40'; \
    echo 'pm.max_spare_servers = 90'; \
    echo 'pm.max_requests = 1000'; \
    echo; \
    echo 'pm.status_path = /fpm-status'; \
    echo 'ping.path = /fpm-ping'; \
    echo; \
    echo 'access.log = /proc/1/fd/1'; \
    echo 'slowlog = /proc/1/fd/2'; \
    echo 'request_slowlog_timeout = 180s'; \
    echo 'request_terminate_timeout = 300s'; \
    echo; \
    echo 'clear_env = no'; \
    echo 'rlimit_files = 131072'; \
    echo 'rlimit_core = unlimited'; \
} > /usr/local/etc/php-fpm.d/www.conf

RUN { \
    echo '[heavy]'; \
    echo 'user = www-data'; \
    echo 'group = www-data'; \
    echo 'listen = 127.0.0.1:9001'; \
    echo 'listen.allowed_clients = 127.0.0.1'; \
    echo 'pm = static'; \
    echo 'pm.max_children = 4'; \
    echo 'pm.max_requests = 500'; \
    echo 'php_admin_value[memory_limit] = 4G'; \
    echo 'request_terminate_timeout = 600s'; \
    echo 'access.log = /proc/1/fd/1'; \
    echo 'slowlog = /proc/1/fd/2'; \
    echo 'request_slowlog_timeout = 300s'; \
    echo 'clear_env = no'; \
} > /usr/local/etc/php-fpm.d/heavy.conf

# 2. Consolidated PHP.ini settings, tuned for performance and stability
RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 512M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_execution_time = 30/max_execution_time = 300/g' /usr/local/etc/php/php.ini && \
    sed -i 's/max_input_time = 60/max_input_time = 300/g' /usr/local/etc/php/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 64M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/;max_input_vars = 1000/max_input_vars = 10000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/;realpath_cache_size = 4096k/realpath_cache_size = 32M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/;realpath_cache_ttl = 120/realpath_cache_ttl = 7200/g' /usr/local/etc/php/php.ini

# 3. Consolidated OPcache settings
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=512'; \
    echo 'opcache.interned_strings_buffer=64'; \
    echo 'opcache.max_accelerated_files=30000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
} > /usr/local/etc/php/conf.d/zz-opcache-custom.ini

# 4. Consolidated APCu settings
RUN { \
    echo 'extension=apcu.so'; \
    echo 'apc.enable_cli=1'; \
    echo 'apc.shm_size=256M'; \
    echo 'apc.rfc1867=1'; \
    echo 'apc.ttl=7200'; \
} > /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

# --- Application Setup ---

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/
RUN rm -rf /opt/drupal
ENV BUILD_ENV=eks
ENV PATH=${PATH}:/opt/drupal/vendor/bin:/opt/drupal/docroot/core/scripts:/opt/drupal/bin

EXPOSE 80 9090

# Create startup script to run services
RUN cat > /usr/local/bin/start-services.sh <<'EOF'
#!/usr/bin/env bash
set -Eeuo pipefail

# Trap and propagate termination to all children
pids=()
term() {
  echo "[entrypoint] Terminating children..."
  kill -TERM "${pids[@]}" 2>/dev/null || true
  wait
}
trap term SIGTERM SIGINT

# Start services in foreground mode
/usr/sbin/rsyslogd -n & pids+=("$!")
php-fpm -F & pids+=("$!")
/opt/prometheus/prometheus --config.file=/etc/prometheus/prometheus.yml --storage.tsdb.retention.time=15d & pids+=("$!")
/usr/local/bin/apache2-foreground & pids+=("$!")

# Exit if any child exits; then terminate the rest
set +e
wait -n
status=$?
set -e
echo "[entrypoint] A child exited with status ${status}, shutting down the rest..."
term
exit "${status}"
EOF

RUN chmod +x /usr/local/bin/start-services.sh

# vim:set ft=dockerfile:
CMD ["/usr/local/bin/start-services.sh"]
