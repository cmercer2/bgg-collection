FROM php:8.2-apache

# Install libraries required for GD (image resizing) and clean up
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd \
    && rm -rf /var/lib/apt/lists/*

# Enable allow_url_fopen to allow remote file access from PHP (simplexml_load_file, file_get_contents)
RUN printf "; enable URL fopen for remote streams\nallow_url_fopen=On\n" > /usr/local/etc/php/conf.d/99-allow-url-fopen.ini

# Add a small entrypoint to ensure cache dir exists and has correct ownership
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
