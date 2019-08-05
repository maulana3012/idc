FROM richarvey/nginx-php-fpm:latest

# php pgsql extension
RUN set -ex && \
    apk --no-cache add postgresql-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql && \
    apk del postgresql-dev && \
    apk add --upgrade postgresql --update-cache --repository http://dl-3.alpinelinux.org/alpine/edge/main/

VOLUME ["/var/www/html", "/var/www/_user_data", "/var/www/backup"]
ADD php.ini /usr/local/etc/php

# RUN mkdir -p /var/www/_user_data/pdf/indocore
# RUN chown -R nginx:nginx /var/www/_user_data

CMD ["/start.sh"]
