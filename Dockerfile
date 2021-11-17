FROM php:7.3-apache AS cms_build

ENV CMS_REPO_ROOT="/var/www/cms"
ENV CMS_DOCROOT="$CMS_REPO_ROOT/docroot"
ENV PHP_VERSION="7.3"
ENV CMS_ENVIRONMENT_TYPE="eks"

# Copy Composer into container rather than installing it.
COPY --from=composer:2.1.8 /usr/bin/composer /usr/local/bin/composer

# php-extension-installer makes working with PHP modules a lot easier
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install VA internal self-signed certificate authority.
ADD http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer /usr/local/share/ca-certificates/
RUN openssl x509 -inform DER -in /usr/local/share/ca-certificates/VA-Internal-S2-RCA1-v1.cer -out /usr/local/share/ca-certificates/VA-Internal-S2-RCA1-v1.crt
RUN update-ca-certificates

# Use default production values.
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Inject PHP conf overrides.
COPY .lando/zzz-lando-my-custom.ini $PHP_INI_DIR/conf.d/my-php.ini

# Change docroot.
RUN sed -ri -e 's#/var/www/html#${CMS_DOCROOT}#g' \
    /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's#/var/www/#${CMS_DOCROOT}#g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Enable mod_rewrite.
RUN a2enmod rewrite

# Install dependencies
RUN apt-get update \
  && apt-get install -y \
    # Git (for Composer/etc modules)
    git \
    mariadb-client \
    # unzip (for unzipping Composer zip files)
    unzip

# Install PHP extensions.
RUN install-php-extensions \
  apcu \
  bcmath \
  bz2 \
  calendar \
  exif \
  gd \
  gettext \
  imagick \
  imap \
  intl \
  ldap \
  mbstring \
  memcached \
  oauth \
  pcntl \
  memcache \
  opcache \
  pdo_mysql \
  redis \
  soap \
  # NO STOP DIE DIE DIE
  # xdebug \
  # Drush 10 requirement
  zip

# Copy in the repository.
COPY . $CMS_REPO_ROOT
RUN chown -R www-data:www-data $CMS_REPO_ROOT/..

USER www-data

WORKDIR $CMS_REPO_ROOT

USER root

