FROM php:8.2-apache

   # Install MySQL extension
   RUN docker-php-ext-install pdo pdo_mysql

   # Enable Apache rewrite module (for Laravel)
   RUN a2enmod rewrite
