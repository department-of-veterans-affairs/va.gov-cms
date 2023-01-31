FROM uselagoon/php-8.1-cli-drupal:latest

# Install Jinja2 for `j2 .lagoon/.env.j2 .env` in composer.json
RUN apk add --no-cache py3-pip
RUN pip install jinja2-cli
RUN ln --symbolic /usr/bin/jinja2 /usr/bin/j2

COPY .lagoon /app/.lagoon
COPY composer.* /app
RUN composer install --no-dev

COPY . /app
RUN mkdir --parents --verbose --mode=775 /app/docroot/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=docroot
