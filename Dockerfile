
FROM webdevops/php-apache:8.2

RUN apt-get update     && apt-get install -y --no-install-recommends smbclient     && rm -rf /var/lib/apt/lists/*

COPY apache-config.conf /opt/docker/etc/httpd/conf.d/99-apache-config.conf

