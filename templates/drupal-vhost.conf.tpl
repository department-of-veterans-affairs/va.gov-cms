AddType application/x-httpd-php .php

<VirtualHost  127.0.0.1:80  _default_:80>
  ServerAlias *
  DocumentRoot /opt/bitnami/drupal/docroot
    <Directory /opt/bitnami/drupal/docroot>
      Options -Indexes +FollowSymLinks -MultiViews
      AllowOverride All
      Require all granted
      DirectoryIndex index.html index.php

    </Directory>
</VirtualHost>
