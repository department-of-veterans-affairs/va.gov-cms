# Debugging

## Xdebug:
* Setup:
    * Enable Xdebug by uncommenting the `xdebug: true` line in .lando.yml
    * Run `lando rebuild`
    * Configure PHPStorm: Go to Settings > Languages & Frameworks > PHP > Debug
    * Check "allow connections" and ensure max connections is 2
    * Enable "Start listening for PHP debug connections"
* Browser:
    * Open index.php and set a test breakpoint on the first line ($autoloader)
    * Go to vagovcms.lndo.site in your browser (no extension needed) and it should trigger an "incoming connection" in PHPStorm, accept it
* CLI:
    * Open Settings > Languages & Frameworks > PHP > Servers and change the server name to "appserver"
    * Set a test breakpoint on /docroot/vendor/drush/drush/drush
    * Run `lando drush status` and it should trigger the breakpoint


[Table of Contents](../README.md)
