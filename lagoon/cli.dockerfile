FROM uselagoon/php-8.1-cli-drupal:latest

COPY composer.* /app/
RUN composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/docroot/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=docroot
