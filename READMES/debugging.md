# Debugging

## Xdebug:
* Setup:
    * Enable Xdebug by `lando xdebug-on`
    * Disable by typing `lando xdebug-off`

* IDEs
    * phpstorm
        * Configure PHPStorm: Go to Settings > Languages & Frameworks > PHP > Debug
        * Check "allow connections" and ensure max connections is 2
        * Enable "Start listening for PHP debug connections"
    * VS Code
        * Details are here on [lando docs](https://docs.lando.dev/guides/lando-with-vscode.html#getting-started), however, all the php.ini extras have already been added.  The only thing needed is to add the necessary config to .vscode/launch.json

        ```json
            {
            "version": "0.2.0",
            "configurations": [
                {
                "name": "Listen for XDebug",
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
    * Go to vagovcms.lndo.site in your browser (no extension needed) and it should trigger an "incoming connection" in your IDE.
* CLI:
    * In phpStorm Open Settings > Languages & Frameworks > PHP > Servers and change the server name to "appserver"
    * Set a test breakpoint on /docroot/vendor/drush/drush/drush
    * Run `lando drush status` and it should trigger the breakpoint


[Table of Contents](../README.md)
