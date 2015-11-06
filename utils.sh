#!/bin/bash

CODE_PATH="/var/www/notification"

if [[ $1 == 'install' ]]; then
    sudo chmod 777 -Rf app/cache app/logs infra/data infra/logs

    cd infra
    docker-compose up -d
    docker-compose ps
    cd ../

    #setup database
    docker exec -it $(docker ps --filter name=notification_mysql -q) sh -c "mysql -u admin -pazerty -e \"CREATE DATABASE IF NOT EXISTS notification\""
    docker exec -it $(docker ps --filter name=notification_mysql -q) sh -c "mysql -u admin -pazerty notification < /var/sql/sessions_table.sql"

    #composer install
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH  && composer install"

    #permission write
    sudo chmod 777 -Rf app/cache app/logs

    rm -r src/web/bundles

    #assets
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console assetic:dump"
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console assets:install"
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console cache:warmup -e=dev"

    #permission write
    sudo chmod 777 -Rf app/cache app/logs
fi

if [[ $1 == 'start' ]]; then
    cd infra
    docker-compose up -d
    docker-compose ps
fi

if [[ $1 == 'stop' ]]; then
    cd infra
    docker-compose stop
fi

if [[ $1 == 'composer' ]]; then
    shift
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && composer $@"
fi

if [[ $1 == 'sf' ]]; then
    shift
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console $@"
fi

if [[ $1 == 'perm' ]]; then
    sudo chmod 777 -Rf app/cache/ app/logs/
fi

if [[ $1 == 'rebuild' ]]; then
    cd infra
    docker-compose stop
    docker-compose rm -f
    docker-compose build
    docker-compose up -d
fi

if [[ $1 == 'inspect' ]]; then
    docker exec -ti $(docker ps --filter name=notification_$2 -q) bash
fi
