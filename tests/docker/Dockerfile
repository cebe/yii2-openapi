ARG BUILD_PHP_VERSION
#FROM php:7.1-cli
FROM php:$BUILD_PHP_VERSION-cli
# ARG is lost after FROM, so we need to specify it again https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG BUILD_PHP_VERSION

ENV DEBIAN_FRONTEND=noninteractive

RUN echo "force-unsafe-io" > /etc/dpkg/dpkg.cfg.d/02apt-speedup && \
    echo "Acquire::http {No-Cache=True;};" > /etc/apt/apt.conf.d/no-cache
RUN apt-get update && \
    apt-get -y install \
        gnupg2 && \
    apt-key update && \
    apt-get update && \
    apt-get install -y --no-install-recommends \
            imagemagick \
            libmagickwand-dev libmagickcore-dev \
            libfreetype6-dev \
            libjpeg62-turbo-dev \
            libpng-dev \
            libwebp-dev \
            libicu-dev \
            libzip-dev \
            libpq-dev \
            libonig-dev \
            nano \
            git \
            unzip\
            libxml2-dev \
            curl \
            libcurl4-openssl-dev \
            libssl-dev \
        --no-install-recommends && \
        apt-get clean && \
        rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
        # https://xdebug.org/docs/compat \
        && if [ "$BUILD_PHP_VERSION" = "8.2" ] || [ "$BUILD_PHP_VERSION" = "8.3" ] ; then pecl install xdebug ; else pecl install xdebug-3.1.5 ; fi \
        && docker-php-ext-enable xdebug \
        && docker-php-ext-install \
                zip \
                curl \
                bcmath \
                exif \
                gd \
                iconv \
                intl \
                opcache \
                pdo_mysql \
                pdo_pgsql \
                mbstring

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_USER_ID=33 \
    PHP_ENABLE_XDEBUG=1 \
    COMPOSER_HOME=/root/.composer/ \
    PATH=/app:/app/vendor/bin:/root/.composer/vendor/bin:$PATH

RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
&& curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
# Make sure we're installing what we think we're installing!
&& php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }" \
&& php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
&& rm -f /tmp/composer-setup.*

# Enable Xdebug
ENV XDEBUGINI_PATH=/usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "xdebug.idekey=PHP_STORM" >> $XDEBUGINI_PATH && \
    echo "xdebug.mode=debug" >> $XDEBUGINI_PATH && \
    echo "xdebug.start_with_request=yes" >> $XDEBUGINI_PATH && \
    echo "xdebug.discover_client_host=1" >> $XDEBUGINI_PATH && \
    echo "xdebug.client_port=9003" >> $XDEBUGINI_PATH  && \
    echo "xdebug.client_host=host.docker.internal" >> $XDEBUGINI_PATH && \
    echo "xdebug.log_level=0" >> $XDEBUGINI_PATH
    # to get rid of lot of message `Xdebug: [Step Debug] Could not connect to debugging client. Tried: host.docker.internal:9003 (fallback through xdebug.client_host/xdebug.client_port).` in CLI during testing, above setting (log_level=0) is applied. Remove it when xdebug is used
    # reference: https://community.localwp.com/t/xdebug-step-debug-could-not-connect-to-debugging-client-flooding-logs/34550/4
    # scope to apply better solutions (https://stackoverflow.com/questions/64878376/xdebug-step-debug-could-not-connect-to-debugging-client)


WORKDIR /app
