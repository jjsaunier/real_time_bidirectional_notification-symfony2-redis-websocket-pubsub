# First build image

$ docker build -t notification/code build/code
$ docker build -t notification/php-fpm build/php
$ docker build -t notification/nginx build/nginx
$ docker build -t notification/mysql build/mysql

# run stack
docker-compose up -d

# show process
docker-compose ps

# list container 
docker images | grep "notification/"