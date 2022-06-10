# Debugging

## Xdebug:
* Setup:
    * Enable Xdebug by `ddev xdebug on`
    * Disable by typing `ddev xdebug off` (default)

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
        * Open debug panel, then select "Listen for XDebug" and click green arrow to start. Add breakpoints as needed and reload Drupal page in browser. VSCode and XDebug settings have been preconfigured for ddev.

Detailed instructions for many IDEs can be found in [the ddev documentation](https://ddev.readthedocs.io/en/stable/users/step-debugging/).

* Browser:
    * Open index.php and set a test breakpoint on the first line ($autoloader)
    * Go to https://va-gov-cms.ddev.site/ in your browser (no extension needed) and it should trigger an "incoming connection" in your IDE.
* CLI/Drush:
    * In PhpStorm open Settings > Languages & Frameworks > PHP > Servers and change the server name to "appserver"
    * Set a test breakpoint on /docroot/vendor/drush/drush/drush
    * Run `ddev drush status` and it should trigger the breakpoint

## External Database Conntection in PHPStorm

* Run `ddev describe` to get the port.  The output will be similar to the following:

```
-> % ddev describe
┌───────────────────────────────────────────────────────────────────────────────────────────────────────┐
│ Project: va-gov-cms ~/Code/github.com/department-of-veterans-affairs/va.gov-cms https://va-gov-cms.dd │
│ ev.site                                                                                               │
│ Docker environment: docker 20.10.13                                                                   │
├────────────┬──────┬────────────────────────────────────────────────────────────┬──────────────────────┤
│ SERVICE    │ STAT │ URL/PORT                                                   │ INFO                 │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ web        │ OK   │ https://va-gov-cms.ddev.site                               │ drupal9 PHP7.4       │
│            │      │ InDocker: ddev-va-gov-cms-web:443,80,8025                  │ nginx-fpm            │
│            │      │ Host: localhost:53975,53976                                │ docroot:'docroot'    │
│            │      │                                                            │ Mutagen enabled (ok) │
│            │      │                                                            │ NodeJS:16            │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ db         │ OK   │ InDocker: ddev-va-gov-cms-db:3306                          │ mariadb:10.3         │
│            │      │ Host: localhost:55005                                      │ User/Pass: 'db/db'   │
│            │      │                                                            │ or 'root/root'       │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ PHPMyAdmin │ OK   │ https://va-gov-cms.ddev.site:8037                          │                      │
│            │      │ InDocker: ddev-va-gov-cms-dba:80,80                        │                      │
│            │      │ `ddev launch -p`                                           │                      │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ memcached  │ OK   │ InDocker: ddev-va-gov-cms-memcached:11211,11211            │                      │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ Mailhog    │      │ MailHog: https://va-gov-cms.ddev.site:8026                 │                      │
│            │      │ `ddev launch -m`                                           │                      │
├────────────┼──────┼────────────────────────────────────────────────────────────┼──────────────────────┤
│ All URLs   │      │ https://va-gov-cms.ddev.site,                              │                      │
│            │      │ https://va-gov-storybook.ddev.site,                        │                      │
│            │      │ https://127.0.0.1:53975, http://va-gov-cms.ddev.site,      │                      │
│            │      │ http://va-gov-storybook.ddev.site, http://127.0.0.1:53976  │                      │
└────────────┴──────┴────────────────────────────────────────────────────────────┴──────────────────────┘
```

* Add a new database connection using `mariadb` as the datasource.  Use the information from the `ddev describe` from the `db` connection section.

## Custom PHP.INI settings.

To use a custom `php.ini` setting, a file needs to be added to `./.ddev/php/.zzzz-php-local.ini`.  Below is an example where I had to override `xdebug` settings to get it to work with phpstorm.

```
[PHP]
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

## Xdebug in ddev
Keep xdebug off when not in use, it has notable performance implications and can slow down response times by as much as 2-3x sometimes. 
Try running `ddev xdebug on`. If that doesn't work for you out of the box, copy your php.ini override from the
steps above (`.zzzz-php-local.ini`) into the `.ddev/php` directory. Files in this directory are loaded during `ddev start`.

`ddev xdebug on` / `ddev xdebug off` to easily toggle on and off during development. Happy debugging!

[Table of Contents](../README.md)
