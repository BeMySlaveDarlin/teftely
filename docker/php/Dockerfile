FROM debian:buster

LABEL maintainer="BeMySlaveDarlin <bemyslavedarlin@gmail.com>"

ENV PHP_VERSION=7.4
ENV COMPOSER_VERSION=1.9.3

ENV EXPOSE_PORT=8000
ENV DEBIAN_FRONTEND=noninteractive
ENV PATH=/root/composer/vendor/bin:/app/vendor/bin:/app/bin:${PATH}
ENV FPM_CONF_PATH=/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf

RUN apt-get -y update \
    && apt-get install  -yq \
    --no-install-recommends \
        apt-utils autoconf dialog gcc g++ make zlib1g libc-dev zlib1g-dev pkg-config

RUN apt-get -y update && apt-get install \
    --no-install-suggests -yq \
        jq mc nano git wget curl zip unzip gnupg2 dirmngr apt-transport-https \
        lsb-release openssl re2c ca-certificates software-properties-common \
        libpcre3-dev libmagickwand-dev libjpeg-dev libpng-dev libssl-dev \
        libicu-dev libmcrypt-dev libcurl4-openssl-dev libxml2-dev libzip-dev

RUN wget -O /etc/apt/trusted.gpg.d/php.gpg https://packages.sury.org/php/apt.gpg \
    && echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list

RUN apt-get -y update && apt-get install -yq \
        php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-cli \
        php${PHP_VERSION}-common \
        php${PHP_VERSION}-dev

RUN apt-get -y update && apt-get install -yq \
        php${PHP_VERSION}-curl \
        php${PHP_VERSION}-gd \
        php${PHP_VERSION}-gettext \
        php${PHP_VERSION}-intl \
        php${PHP_VERSION}-json \
        php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-msgpack \
        php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-mysqli \
        php${PHP_VERSION}-pdo* \
        php${PHP_VERSION}-pgsql \
        php${PHP_VERSION}-xml \
        php${PHP_VERSION}-zip

RUN echo "#!/bin/sh\nexit 0" > /usr/sbin/policy-rc.d \
    && sed -i -e "s/^;clear_env = no$/clear_env = no/" ${FPM_CONF_PATH} \
    && sed -i -e "/listen = .*/c\listen = [::]:${EXPOSE_PORT}" ${FPM_CONF_PATH}

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
    && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
    && php /tmp/composer-setup.php \
        --no-ansi \
        --install-dir=/usr/local/bin \
        --filename=composer \
        --version=${COMPOSER_VERSION}

WORKDIR /app

RUN mkdir /app/logs && \
    chmod -R 777 /app/logs


EXPOSE ${EXPOSE_PORT}

CMD ["php-fpm7.4", "--nodaemonize", "--fpm-config=/etc/php/7.4/fpm/pool.d/www.conf"]
