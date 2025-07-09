FROM drupal:10.4.8-php8.3-apache-bookworm

RUN apt-get update && apt-get install -y \
  apt-utils \
  build-essential \
  wget \
  unzip \
  git \
  vim \
  nano \
  less \
  procps \
  python3 \
  python3-pip \
  nodejs \
  npm \
  pigz \
  gnupg \
  lsb-release \
  inetutils-tools \
  pv \
  netcat-traditional \
  chromium \
  xvfb \
  libgtk2.0-dev \
  libgtk-3-dev \
  libnotify-dev \
  gconf2 \
  libnss3 \
  libxss1 \
  libasound2 \
  libasound2-plugins \
  default-mysql-client \
  parallel

#RUN wget https://dev.mysql.com/get/mysql-apt-config_0.8.24-1_all.deb
#RUN DEBIAN_FRONTEND=noninteractive dpkg -i mysql-apt-config_0.8.24-1_all.deb
#RUN apt-key adv --keyserver pgp.mit.edu --recv-keys A8D3785C
#RUN apt-get update
#RUN apt install -y mysql-client

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions apcu bcmath csv exif gd intl mbstring memcached opcache pcntl pdo_mysql pdo_pgsql redis sockets uploadprogress xml xmlreader xmlwriter xsl zip

RUN sh -c "$(curl --location https://taskfile.dev/install.sh)" -- -d

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
  echo 'opcache.memory_consumption=128'; \
  echo 'opcache.interned_strings_buffer=8'; \
  echo 'opcache.max_accelerated_files=4000'; \
  echo 'opcache.revalidate_freq=60'; \
  } > /usr/local/etc/php/conf.d/opcache-recommended.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 10M/g' /usr/local/etc/php/php.ini && \
    sed -i 's/;max_input_vars = 1000/max_input_vars = 10000/g' /usr/local/etc/php/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 4G/g' /usr/local/etc/php/php.ini

# https://www.drupal.org/node/3060/release
ENV DRUPAL_VERSION=10.4.8

RUN rm -rf /opt/drupal
RUN mkdir -p /opt/drupal
# Copy in our files to start with.
COPY . /opt/drupal
WORKDIR /opt/drupal

RUN set -eux; \
  export COMPOSER_HOME="$(mktemp -d)"; \
  composer install --dev; \
  rm -rf /var/www/html; \
  ln -sf /opt/drupal/docroot /var/www/html; \
  chmod +x docroot/core/scripts/drupal; \
  # delete composer cache
  rm -rf "$COMPOSER_HOME"

RUN ./scripts/install-nvm.sh
RUN ./scripts/install_task_runner.sh
RUN ./scripts/install_github_status_updater.sh
RUN ./scripts/install_github_commenter.sh
RUN composer va:theme:compile
RUN composer va:web:install

RUN chown -R www-data:www-data samlsessiondb.sq3
RUN chmod 0664 samlsessiondb.sq3

RUN chown -R www-data:www-data /opt/drupal/docroot
RUN chown -R www-data:www-data /opt/drupal/scripts/composer
RUN find /opt/drupal -type d -exec chmod g+ws {} +
RUN find /opt/drupal -type f -exec chmod g+w {} +

RUN rm /opt/drupal/.env

ENV PATH=${PATH}:/opt/drupal/vendor/bin:/opt/drupal/docroot/core/scripts:/opt/drupal/bin
# vim:set ft=dockerfile:
