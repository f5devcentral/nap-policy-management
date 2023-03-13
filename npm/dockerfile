FROM php:7.4-apache
RUN apt-get update && apt-get install -y apt-transport-https lsb-release ca-certificates nano

# Install python 3.9
RUN apt install software-properties-common -y
RUN add-apt-repository ppa:deadsnakes/ppa
RUN apt install python3.9 -y

# Install pip
RUN apt install python3-pip -y
RUN python3 -m pip install pyyaml

RUN mkdir /etc/fpm/
RUN chgrp -R www-data /etc/fpm
RUN chown -R www-data /etc/fpm

RUN cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/conf.d/php.ini

WORKDIR /var/www/html

COPY html/ .
EXPOSE 80


