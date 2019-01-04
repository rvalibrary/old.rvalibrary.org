# base image
FROM wordpress:php5.6-apache

RUN touch /usr/local/etc/php/conf.d/uploads.ini \
        && echo "upload_max_filesize = 256M;" >> /usr/local/etc/php/conf.d/uploads.ini
