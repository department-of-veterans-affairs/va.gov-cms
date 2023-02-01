FROM uselagoon/php-8.1-cli-drupal:latest

COPY .lagoon/ /app/.lagoon/
COPY .git/ /app/.git/
COPY patches/ /app/patches/
COPY hooks/ /app/hooks/
COPY simplesamlphp-config-metadata/ /app/simplesamlphp-config-metadata/
COPY composer.* /app/
RUN composer install --no-dev

COPY . /app
RUN mkdir --parents --verbose --mode=775 /app/docroot/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=docroot
