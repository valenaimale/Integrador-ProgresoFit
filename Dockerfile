FROM php:8.3-cli

WORKDIR /app

RUN apt-get update && apt-get install -y unzip curl git \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN mkdir -p logs && chmod 777 logs

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public/"]
