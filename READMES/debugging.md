# Debugging

## Xdebug:
* Setup:
    * Enable Xdebug by `lando xdebug-on`
    * Disable by typing `lando xdebug-off`

* IDEs
    * PhpStorm
        * Configure PhpStorm: Go to Settings > Languages & Frameworks > PHP > Debug
        * Check "allow connections" and ensure max connections is 2 or more (more is useful for debugging requests in parallel, for side by side testing)
        * Enable "Start listening for PHP debug connections"
    * VS Code
        * Details are here on [lando docs](https://docs.lando.dev/guides/lando-with-vscode.html#getting-started), however, all the php.ini extras have already been added.  The only thing needed is to add the necessary config to .vscode/launch.json

        ```json
            {
            "version": "0.2.0",
            "configurations": [
                {
                "name": "Listen for Xdebug",
                "type": "php",
                "request": "launch",
                "port": 9000
                "log": true,
                "pathMappings": {
                    "/app/": "${workspaceFolder}/",
                }
                },
                {
                "name": "Launch currently open script",
                "type": "php",
                "request": "launch",
                "program": "${file}",
                "cwd": "${fileDirname}",
                "port": 9000
                }
            ]
            }

        ```
        This file is git ignored so can be additionally modified without affecting others.

* Browser:
    * Open index.php and set a test breakpoint on the first line ($autoloader)
    * Go to http://va-gov-cms.lndo.site/ in your browser (no extension needed) and it should trigger an "incoming connection" in your IDE.
* CLI/Drush:
    * In PhpStorm open Settings > Languages & Frameworks > PHP > Servers and change the server name to "appserver"
    * Set a test breakpoint on /docroot/vendor/drush/drush/drush
    * Run `lando drush status` and it should trigger the breakpoint

## External Database Conntection in PHPStorm

* Run `lando info` to get the port.  The output will be similar to the following:

```
-> % lando info
[
  {
    service: 'appserver',
    urls: [
      'https://localhost:32779',
      'http://localhost:32780',
      'http://va-gov-cms.lndo.site',
      'https://va-gov-cms.lndo.site'
    ],
    type: 'php',
    via: 'apache',
    webroot: 'docroot',
    config: {
      php: '.lando/zzz-lando-my-custom.ini'
    },
    version: '7.2',
    meUser: 'www-data',
    hostnames: [
      'appserver.vagovcms.internal'
    ]
  },
  {
    service: 'database',
    urls: [],
    type: 'mysql',
    internal_connection: {
      host: 'database',
      port: '3306'
    },
    external_connection: {
      host: 'localhost',
      port: '32778'
    },
    creds: {
      database: 'drupal8',
      password: 'drupal8',
      user: 'drupal8'
    },
    config: {
      database: '/Users/indytechcook/.lando/config/drupal8/mysql.cnf'
    },
    version: '5.7',
    meUser: 'www-data',
    hostnames: [
      'database.vagovcms.internal'
    ]
  }
]
```
* Make note of the `database.external_connection.port` and `database.creds`.
* Add a new database connection using `mariadb` as the datasource.  Use the information from the `lando info` from the `external_connection` connection section.


[Table of Contents](../README.md)
