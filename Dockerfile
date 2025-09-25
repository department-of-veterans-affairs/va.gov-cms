ARG BASE_IMAGE_TAG=1.0.38

FROM 008577686731.dkr.ecr.us-gov-west-1.amazonaws.com/dsva/cms-apache:${BASE_IMAGE_TAG}

# https://www.drupal.org/node/3060/release
ENV DRUPAL_VERSION=10.4.8

ARG DD_GIT_COMMIT_SHA
ENV DD_GIT_REPOSITORY_URL=https://github.com/department-of-veterans-affairs/va.gov-cms
ENV DD_GIT_COMMIT_SHA=${DD_GIT_COMMIT_SHA}
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

RUN cat > /usr/local/bin/drush-wrapper <<'EOF'
#!/usr/bin/env bash
exec php -d memory_limit=4G /opt/drupal/bin/drush "$@"
EOF

RUN chmod +x /usr/local/bin/drush-wrapper && \
    ln -sf /usr/local/bin/drush-wrapper /usr/local/bin/drush

RUN ./scripts/install-nvm.sh
RUN ./scripts/install_github_status_updater.sh
RUN ./scripts/install_github_commenter.sh
RUN composer va:theme:compile

RUN printf '%s\n' "<?php header('X-FPM-Pool: '.(getenv('PHP_FPM_POOL')?:'unknown')); ?>" > /opt/drupal/docroot/pool-tag.php

RUN chown -R www-data:www-data samlsessiondb.sq3
RUN chmod 0664 samlsessiondb.sq3

RUN chown -R www-data:www-data /opt/drupal/docroot
RUN chown -R www-data:www-data /opt/drupal/scripts/composer
RUN find /opt/drupal -type d -exec chmod g+ws {} +
RUN find /opt/drupal -type f -exec chmod g+w {} +

RUN rm /opt/drupal/.env

ENV PATH=${PATH}:/opt/drupal/vendor/bin:/opt/drupal/docroot/core/scripts:/opt/drupal/bin
# vim:set ft=dockerfile:
