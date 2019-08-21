FROM php:7-fpm

RUN apt-get update && docker-php-ext-install mysqli pdo pdo_mysql sockets bcmath json hash
##
RUN apt-get -y install libpng-dev libjpeg-dev libjpeg62-turbo-dev libfreetype6-dev libvpx-dev libwebp-dev libxpm-dev

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/

RUN docker-php-ext-install gd


RUN pecl install -o -f redis \
    &&  rm -rf /tmp/pear \
    &&  docker-php-ext-enable redis

RUN version=$(php -r "echo PHP_MAJOR_VERSION.PHP_MINOR_VERSION;") \
    && curl -A "Docker" -o /tmp/blackfire-probe.tar.gz -D - -L -s https://blackfire.io/api/v1/releases/probe/php/linux/amd64/$version \
    && mkdir -p /tmp/blackfire \
    && tar zxpf /tmp/blackfire-probe.tar.gz -C /tmp/blackfire \
    && mv /tmp/blackfire/blackfire-*.so $(php -r "echo ini_get('extension_dir');")/blackfire.so \
    && printf "extension=blackfire.so\nblackfire.agent_socket=tcp://blackfire:8707\n" > $PHP_INI_DIR/conf.d/blackfire.ini \
    && rm -rf /tmp/blackfire /tmp/blackfire-probe.tar.gz

 #RUN bcmath=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.-bcmath;")\
#	apt-get install -y $bcmath/testing