FROM ubuntu:22.04
RUN apt-get update 
#RUN apt install -y php-cli php-curl php-gd php-mbstring php-sqlite3 php-xml php-mysql
RUN apt-get install curl gpg gnupg2 software-properties-common ca-certificates apt-transport-https lsb-release -y
RUN add-apt-repository ppa:ondrej/php -y
RUN apt update -uy
RUN DEBIAN_FRONTEND=noninteractive TZ=Etc/UTC apt-get -y install tzdata. 
RUN apt-get install php8.1 -y
RUN apt-get install curl -y
RUN apt-get install git -y
RUN apt-get install php8.1-gd -y
RUN apt-get install php8.1-xml -y
RUN apt-get install php8.1-curl -y
RUN apt-get install php8.1-gd -y
RUN apt-get install php8.1-zip -y 
RUN apt-get install unzip -y
RUN apt-get install php8.1-cli -y
RUN apt-get install php8.1-common -y
RUN apt-get install php8.1-mysqlnd -y
RUN apt-get install php8.1-memcached -y
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer


COPY . /tmp/cms
WORKDIR /tmp/cms
RUN composer install
RUN ./scripts/install-nvm.sh
RUN . ~/.profile
RUN ./scripts/vets-web-setup.sh
RUN composer va:theme:compile
RUN composer va:web:install
RUN composer va:theme:compile
RUN composer va:web:install
ENV CMS_MARIADB_DATABASE=db
ENV CMS_MARIADB_USERNAME=db
ENV CMS_MARIADB_PASSWORD=db
ENV CMS_MARIADB_HOST=127.0.0.1

CMD ["composer", "drush rs"]