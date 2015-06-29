FROM ubuntu:14.04

MAINTAINER Johann Saunier <johann_27@hotmail.fr>

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update && apt-get upgrade -y
RUN apt-get install -y php5-common php5-cli php5-fpm php5-mcrypt php5-mysql php5-apcu php5-gd php5-imagick php5-curl php5-intl php5-redis php5-imap php5-json
RUN apt-get install -y supervisor

RUN sudo service php5-fpm stop

ADD php.ini /etc/php5/fpm/conf.d/php.ini
ADD php.ini /etc/php5/cli/conf.d/notification.ini
ADD notification.pool.conf /etc/php5/fpm/pool.d/notification.pool.conf

ADD supervisord/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
ADD supervisord/php.conf /etc/supervisor/conf.d/php.conf

RUN mkdir -p /var/log/php/
RUN mkdir -p /var/log/supervisor/
RUN chmod 777 -R /var/log/php

CMD ["/usr/bin/supervisord"]

EXPOSE 9000 9001