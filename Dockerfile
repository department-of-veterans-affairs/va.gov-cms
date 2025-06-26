FROM drupal:10.4.8-php8.3-apache-bookworm

RUN apt-get update && apt-get install -y \
  curl \
  wget \
  unzip \
  git \
  vim \
  nano \
  less \
  procps \
  libmemcached-tools \
  python3 \
  python3-pip \
  nodejs \
  npm \
  pigz \

# install the PHP extensions and dependencies we need
RUN set -eux; \
  \
  if command -v a2enmod; then \
  a2enmod rewrite; \
  fi; \
  \
  savedAptMark="$(apt-mark showmanual)"; \
  \
  apt-get update; \
  apt-get install -y --no-install-recommends \
  libfreetype6-dev \
  libjpeg-dev \
  libpng-dev \
  libpq-dev \
  libwebp-dev \
  libzip-dev \
  libssl-dev \
  zlib1g-dev \
  git \
  ; \
  \
  pecl install memcached \
  && docker-php-ext-enable memcached; \
  \
  docker-php-ext-configure gd \
  --with-freetype \
  --with-jpeg=/usr \
  --with-webp \
  ; \
  \
  docker-php-ext-install -j "$(nproc)" \
  gd \
  opcache \
  pdo_mysql \
  pdo_pgsql \
  zip \
  ; \
  \
  rm -rf /var/lib/apt/lists/*

# set recommended PHP.ini settings
# see https://secure.php.net/manual/en/opcache.installation.php
RUN { \
  echo 'opcache.memory_consumption=128'; \
  echo 'opcache.interned_strings_buffer=8'; \
  echo 'opcache.max_accelerated_files=4000'; \
  echo 'opcache.revalidate_freq=60'; \
  } > /usr/local/etc/php/conf.d/opcache-recommended.ini

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini

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

RUN chown -R www-data:www-data /opt/drupal/docroot
RUN find /opt/drupal -type d -exec chmod g+ws {} +
RUN find /opt/drupal -type f -exec chmod g+w {} +


ENV PATH=${PATH}:/opt/drupal/vendor/bin:/opt/drupal/docroot/core/scripts:/opt/drupal/bin

# vim:set ft=dockerfile:
