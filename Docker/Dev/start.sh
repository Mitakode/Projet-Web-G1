#!/bin/bash

# 1. CRUCIAL : Créer le dossier pour le socket et donner les droits
mkdir -p /run/mysqld
chown -R mysql:mysql /run/mysqld
chown -R mysql:mysql /var/lib/mysql

# 2. INITIALISATION : Si la base est vide, on installe les tables système
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initialisation des tables système MariaDB..."
    mariadb-install-db --user=mysql --datadir=/var/lib/mysql
fi

# 3. LANCEMENT : On lance MariaDB
mysqld_safe --datadir='/var/lib/mysql' &

# 4. ATTENTE
echo "En attente du démarrage de MariaDB..."
until mariadb-admin ping >/dev/null 2>&1; do
    echo -n "."
    sleep 1
done
echo " MariaDB est prêt !"

# 5. CONFIGURATION
mariadb -e "CREATE DATABASE IF NOT EXISTS mon_projet;"
mariadb -e "GRANT ALL PRIVILEGES ON *.* TO 'dev'@'%' IDENTIFIED BY 'devpass';"
mariadb -e "FLUSH PRIVILEGES;"

# 6. APACHE
echo "Serveur LAMP prêt ! Apache et MySQL tournent."
exec apache2ctl -D FOREGROUND
