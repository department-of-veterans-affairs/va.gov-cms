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
        * If you have issues connecting see the bottom of this page where it talks about setting up a custom `php.ini`.
        * Still having issues after adding custom `php.ini`? Verify that PHP CLI interpreter has successfully identified Debugger extention:
            * Go to Settings > Languages & Frameworks > PHP. Press "..." button next to CLI interpreter to open interpreter settings.
            * If "Additional > Debugger extension" field is empty, add a path to debugger extension manually. Press `i` (Show phpinfo) button next to PHP executable to find debugger extension path. Look for `extension_dir` variable. Add `xdebug.so` and save this value in "Debugger extension" field. E.g. `/usr/local/lib/php/extensions/no-debug-non-zts-20160303/xdebug.so`.
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
                "port": 9000,
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

## Configuration using custom lando files

A file name `.lando.local.yml` can be added to customize lando settings for your local.  This file is ignored by git.  Here is an example `.lando.local.yml` file which sets a static external database port and uses a custom `php.ini` file.

```
services:
  appserver:
    config:
      php: ./lando/.zzzz-php-local.ini
  database:
    portforward: 33242
```

## Custom PHP.INI settings.

To use a custom `php.ini` setting, first a custom `.lando.local.yml` will need to be setup as described above.  Then a file needs to be added to `./lando/.zzzz-php-local.ini`.  Below is an example where I had to override `xdebug` settings to get it to work with phpstorm.

```
[PHP]
; File is named zzz-lando-my-custom.ini because Lando renames on copy to /usr/local/etc/php/conf.d/

; Default is 90, this is higher because /graphql requests timeout locally for WEB builds otherwise.
max_execution_time = 190
; Xdebug
xdebug.max_nesting_level = 256
xdebug.show_exception_trace = 0
; Extra custom Xdebug setting for debug to work in VSCode.
xdebug.remote_enable = 1
xdebug.remote_autostart = 1
xdebug.remote_log = /tmp/xdebug.log

xdebug.remote_port=9001
xdebug.collect_assignments=1
xdebug.collect_includes=1
xdebug.collect_vars=1
xdebug.force_display_errors=1
xdebug.force_error_reporting=1
xdebug.collect_params=4
```


[Table of Contents](../README.md)
