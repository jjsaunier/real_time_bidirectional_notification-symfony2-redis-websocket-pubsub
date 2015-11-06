#!/bin/bash

CODE_PATH="/var/www/notification"

function permission(){
    if [ -d "app/logs" ]; then
        sudo chown $(whoami):$(whoami) -R app/logs
    fi

    if [ -d "app/cache" ]; then
        sudo chown $(whoami):$(whoami) -R app/cache
    fi

    if [ -d "infra/logs" ]; then
        sudo chown $(whoami):$(whoami) -R infra/logs
    fi

    if [ -f "composer.lock" ]; then
        sudo chown $(whoami):$(whoami) -R composer.lock
    fi

    if [ -d "infra/data" ]; then
        sudo chown $(whoami):$(whoami) -R infra/data
    fi

    if [ -d "web/bundles" ]; then
        sudo chown $(whoami):$(whoami) -R web/bundles
    fi

    if [ -f "app/config/parameters.yml" ]; then
        sudo chown $(whoami):$(whoami) app/config/parameters.yml
    fi

    if [ -d "bin" ]; then
        sudo chown -Rf $(whoami):$(whoami) bin
    fi

    if [ -f "app/bootstrap.php.cache" ]; then
         sudo chown $(whoami):$(whoami) app/bootstrap.php.cache
    fi
}

if [[ $1 == 'install' ]]; then
    permission

    cd infra
    docker-compose up -d
    docker-compose ps
    cd ../

    #setup database
    docker exec -it $(docker ps --filter name=notification_mysql -q) sh -c "mysql -u admin -pazerty -e \"CREATE DATABASE IF NOT EXISTS notification\""
    docker exec -it $(docker ps --filter name=notification_mysql -q) sh -c "mysql -u admin -pazerty notification < /var/sql/sessions_table.sql"

    #composer install
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH  && composer install"

    permission
    sudo rm -r web/bundles

    #assets
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console assets:install"
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console assetic:dump"
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console cache:warmup -e=dev"

    #permission write
    permission
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
    args=$@
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && composer $args"
fi

if [[ $1 == 'sf' ]]; then
    shift
    args=$@
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console $args"
fi

if [[ $1 == 'perm' ]]; then
    permission
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

if [[ $1 == 'docker' ]]; then
    cd infra
    shift
    args=$@
    docker-compose $args
fi

if [[ $1 == 'prod' ]]; then
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console cache:clear -e=prod"
    docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console cache:warmup -e=prod"
fi

if [[ $1 == 'websocket' ]]; then
    if [[ $2 == 'prod' ]]; then
        docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console gos:websocket:server -e=prod"
    else
        docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console gos:websocket:server -e=dev"
    fi
fi

if [[ $1 == 'notification' ]]; then
    if [[ $2 == 'prod' ]]; then
        docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console gos:notification:server -e=prod"
    else
        docker exec -it $(docker ps --filter name=notification_php -q) sh -c "cd $CODE_PATH /var/www/notification && app/console gos:notification:server -e=dev"
    fi
fi
