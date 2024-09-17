Here is some documentation for how the dockerfile for drupal has been created. I want to try to explain my method and reasoning for the code. 

First and foremost I wanted to explain the reason that I chose to make a dockerfile in the first place. The reason for not using a off the shelf image (which there are a few), is because I believe that this is going to be the best choice for security reasons. We will own the image that the cms is built on , because I was not able to find a way to get the drupal first party image to work, I believe that using a third party image could be dangerous. Using a third party image leaves us open to a state were the third party does not keep the image up to date, which is my primary concern. We also have to consider updates and upgrades. I reason that because we already apply updates in this repo, it is better to containerize our code rather then relying on a 3rd party one. 

Now lets get to the code

`FROM ubuntu:22.04
RUN apt-get update `

First I use ubuntu 22.04 as the base image. I do this mostly because it is easy to install new packages and helped me test the capabilities of the image. We should, in the end state of the container image, use alpine linux or perhaps amazon linux 2. I then run an update in order to make sure we have the latest security packages


`
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
`

I then install all the tools and software we need in order to run druapl. Because we use drupal 10 I install tzdata because some php modules need it. I also install php 8.1 because we need this version for our drush instant. I install curl and unzip to allow composer to download packages and dependencies. I also install a variety of php modules. I choose those modules from the set that we install in tugboat as well as what composer complained about when attempting to start the server. I install composer because our app use composer and I chose to start the server with composer

`

COPY . /tmp/cms
WORKDIR /tmp/cms
RUN composer install
RUN ./scripts/install-nvm.sh
RUN . ~/.profile
RUN ./scripts/vets-web-setup.sh
RUN composer va:theme:compile
RUN composer va:web:install
ENV CMS_MARIADB_DATABASE=db
ENV CMS_MARIADB_USERNAME=db
ENV CMS_MARIADB_PASSWORD=db
ENV CMS_MARIADB_HOST=127.0.0.1

CMD ["composer", "drush rs"]`

I copy our entire project to a temp directory inside the container. I go there and run a composer install. I have attempted to run `composer update` but it seems to fail with a variety of errors. I do not know how to get past those errors. I think it is a good idea to introduce a composer update as part of the image build but lack the capabilities of doing so. I then followed the local install guide and the steps in tugboat that allow us to install the cms app by setting up vets-website, compiling the va theme and website. 

Finally, I set some environment variables that we need for the cms. Drupal looks for a database at those set variables. If nothing is set it will use defaults. Currently, I set the host to localhost and assume that the drupal container is on the same network as the database with the other options set for it. 

Also worth remembering is the fact that this container relies on memcached. I modify the settings.php to set the server location also to localhost and assume there is going to be a memcached server running on the same network as the cms container. I do not know how to pass that in as an environment variable so have just relied on changing the code in settings.php. 