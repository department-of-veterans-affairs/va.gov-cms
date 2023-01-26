FROM uselagoon/php-8.1-cli-drupal:latest

#COPY composer.* /app/
# Temporary because composer install on build wants to copy .env.example and it doesn't exist. So we just are copying everything for now to get the PoC working and to fend off any more surprises.
COPY . /app
RUN composer install --no-dev
COPY . /app
RUN mkdir -p -v -m775 /app/docroot/sites/default/files

# Define where the Drupal Root is located
ENV WEBROOT=docroot
