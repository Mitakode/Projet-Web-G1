# On part d'un Linux Debian
FROM debian:bookworm

# On dit à Linux de ne pas nous poser de questions pendant l'installation
ENV DEBIAN_FRONTEND=noninteractive

# On installe TOUT : Apache, PHP, MariaDB (MySQL), et les prérequis de Composer
RUN apt-get update && apt-get install -y \
    apache2 \
    php \
    libapache2-mod-php \
    php-mysql \
    curl \
    git \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# On copie notre script de démarrage dans le conteneur et on le rend exécutable
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# On ouvre les ports pour le web (80) et la base de données (3306)
EXPOSE 80 3306

# Au lancement du conteneur, on exécute notre script
CMD ["/usr/local/bin/start.sh"]