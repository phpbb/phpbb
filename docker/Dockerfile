# Debian version
FROM ubuntu:22.04
ENV TZ=America/New-York
ENV DEBIAN_FRONTEND=noninteractive

LABEL org.opencontainers.image.authors="sasa@karanovic.ca"
LABEL "com.example.vendor"="phpBB"
LABEL version="1.0.0"
LABEL description="Default phpBB Development Image"

USER root
WORKDIR /workspaces

# Install PHP
RUN apt-get -y update && apt-get -y install software-properties-common 
RUN add-apt-repository ppa:ondrej/php
RUN apt-get -y update
RUN apt-get -y install php8.4 php8.4-xml php8.4-mbstring php8.4-curl php8.4-zip php8.4-xdebug curl rsync

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install MySQL
RUN apt-get -y install mysql-server php8.4-mysql

# Xdebug
ADD xdebug.ini /etc/php/8.4/apache2/conf.d/xdebug.ini

# Configure Apache
RUN echo "Listen 8080" >> /etc/apache2/ports.conf && \
    a2enmod rewrite

# Remove /var/www/html folder
RUN rm -rf /var/www/html/
RUN mkdir -p /var/www/html

# Expose Apache, mySQL and Xdebug ports
EXPOSE 80/tcp
EXPOSE 8080/tcp
EXPOSE 3306/tcp
EXPOSE 9003/tcp

# Run entrypoint
ENTRYPOINT ["/var/phpbb/setup.sh"]
