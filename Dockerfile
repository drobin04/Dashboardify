# Use official PHP image with Apache
FROM php:8.2-apache

# Copy app files into container
COPY /src/html /var/www/html/Dashboardify

# Create data directory with correct permissions
RUN mkdir -p /var/www/html/Dashboardify/data \
    && chown -R www-data:www-data /var/www/html/Dashboardify/data

# Expose ports
EXPOSE 80
EXPOSE 443
