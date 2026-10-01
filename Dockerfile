# syntax=docker/dockerfile:1
# Apache + PHP image for the legacy Gibson store.
# The repository root IS the web root: index.php lives here and the pages live
# in php/, admin/ and html/, so the source is copied straight into /var/www/html.

# ---------------------------------------------------------------------------
# assets stage - pre-optimize the product images.
#
# The repository ships ~264 MB of full-resolution images under img/ (several
# PNGs are 5-7 MB), which bloats the built image and every served page. This
# stage downscales any image whose longest side exceeds 1600px (never
# upscaling), quantizes PNGs and recompresses JPEGs while keeping every
# filename and extension IDENTICAL: the database stores these exact paths, so
# no file may be renamed or converted. The optimization tooling lives only in
# this stage and is never copied into the final image.
# ---------------------------------------------------------------------------
FROM alpine:3.20 AS assets

RUN apk add --no-cache imagemagick pngquant jpegoptim findutils

COPY img/ /assets/img/

# 1) Downscale only images larger than 1600px on their longest side, then strip
#    metadata. 2) Quantize PNGs (ignore "output larger than input" skips so the
#    build never fails on an already-optimal file). 3) Recompress JPEGs.
RUN find /assets/img -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' \) \
        -exec mogrify -resize '1600x1600>' -strip {} + \
    && find /assets/img -type f -iname '*.png' \
        -exec sh -c 'pngquant --force --skip-if-larger --strip --quality=65-90 --ext .png "$@" || true' sh {} + \
    && find /assets/img -type f \( -iname '*.jpg' -o -iname '*.jpeg' \) \
        -exec jpegoptim --strip-all --all-progressive --max=85 {} +

FROM php:8.2-apache

# mysqli is the only extension the application itself needs. mbstring and iconv
# (used by the vendored PHPMailer and FPDF) are already compiled into this base
# image, and PHPMailer is vendored in-tree so there is no Composer step.
RUN docker-php-ext-install mysqli

# Upload / runtime limits for the admin image upload (admin/create_product.php
# and admin/update_images.php accept up to 6 files per request; the repository
# holds product PNGs of several MB). Session files are stored in a writable
# directory so $_SESSION works out of the box.
RUN { \
        echo 'file_uploads = On'; \
        echo 'upload_max_filesize = 16M'; \
        echo 'post_max_size = 64M'; \
        echo 'max_file_uploads = 20'; \
        echo 'memory_limit = 256M'; \
        echo 'max_execution_time = 120'; \
        echo 'session.save_path = /tmp'; \
    } > /usr/local/etc/php/conf.d/zz-gibson.ini

# Silence Apache's "Could not reliably determine the server's FQDN" notice.
RUN echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

COPY . /var/www/html/

# Swap the raw repository images for the lighter copies built by the assets
# stage. Same names, same extensions - only the bytes change.
RUN rm -rf /var/www/html/img
COPY --from=assets /assets/img/ /var/www/html/img/

# The admin panel writes uploaded product images into img/. Make that directory
# writable by the Apache user.
RUN chown -R www-data:www-data /var/www/html/img

EXPOSE 80
