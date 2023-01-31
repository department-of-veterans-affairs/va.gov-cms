FROM uselagoon/php-8.1-cli-drupal:latest

# So we can j2 .lagoon/.env.j2 .env in composer.json
RUN apk add --no-cache py3-jinja2
COPY composer.* /app/
RUN composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/docroot/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=docroot
