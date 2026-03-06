FROM debian:bookworm


ENV DEBIAN_FRONTEND=noninteractive


RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    libapache2-mod-php \
    php-mysql \
    mariadb-server \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*


COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh


EXPOSE 80 3306

CMD ["/usr/local/bin/start.sh"]
