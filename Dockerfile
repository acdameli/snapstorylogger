FROM ubuntu:14.04
MAINTAINER Craig D'Amelio <craig+snapchatphp@damelio.ca>

RUN apt-get update -y
RUN apt-get install -y git curl mcrypt php5 php5-mcrypt php5-curl

RUN php5enmod mcrypt

RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

CMD /docker/start.sh

ADD ./src /app
ADD ./docker /docker

RUN composer install -n --working-dir="/app"
