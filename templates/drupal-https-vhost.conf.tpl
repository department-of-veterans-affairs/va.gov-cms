AddType application/x-httpd-php .php

<VirtualHost  127.0.0.1:443  _default_:443>
  ServerAlias *
  SSLCertificateFile "/opt/bitnami/apache/conf/bitnami/certs/server.crt"
  SSLCertificateKeyFile "/opt/bitnami/apache/conf/bitnami/certs/server.key"
  DocumentRoot /opt/bitnami/drupal/docroot
    <Directory /opt/bitnami/drupal/docroot>
      Options -Indexes +FollowSymLinks -MultiViews
      AllowOverride All
      Require all granted
      DirectoryIndex index.html index.php

    </Directory>
</VirtualHost>
