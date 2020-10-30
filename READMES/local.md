# [Environments](environments): Local

## Lando:
  The local development environment uses Lando to create and manage the Drupal CMS.
  * [Lando Docs](https://docs.lando.dev/)
  * [Lando](https://github.com/lando/lando)


    ### Lando Commands (commonly used ones.  See Lando docs for more.)
    * `lando start -y`
    * `lando rebuild -y`
    * `lando composer nuke`  Destroy the vendor directory so it can be rebuilt
        properly.
    * `lando xdebug-on` | `lando xdebug-off` Turns [xdebug](debugging.md) on or off.

    ### Troubleshooting:
    * Sometimes after initial setup or `lando start`, Drush is not found. Running `lando rebuild -y` once or twice usually cures, if not, see: https://github.com/lando/lando/issues/580#issuecomment-354490298

    ### EXPERIMENTAL: Mac OS performance improvements
    * The osxfs file system server has known performance issues. ([ref](https://docs.docker.com/docker-for-mac/osxfs/#performance-issues-solutions-and-roadmap), [ref](https://www.jeffgeerling.com/blog/2020/revisiting-docker-macs-performance-nfs-volumes)) These issues are exacerbated by the very large number of files present in the application. One workaround is to use an [nfs](https://en.wikipedia.org/wiki/Network_File_System) mount instead. To use nfs in your local environment:
      * First, obtain your user account's uid:

        ```
        id -u
        ```

      * Then, edit the ```/etc/exports``` file (requires root access) and add the following line, replacing '{uid}' with your numeric uid:

        ```
        /System/Volumes/Data -alldirs -mapall={uid}:20 localhost
        ```

      * Then, edit the ```/etc/nfs.conf``` file (requires root access) and add the following line:

        ```
        nfs.server.mount.require_resv_port = 0
        ```

      * Next, restart the nfs server:

        ```
        sudo nfsd restart
        ```

      * Now, you will need to update your lando configuration. Edit your ```.lando.local.yml``` file (create it if it doesn't exist) and add the following lines:

        ```
        services:
          appserver:
            overrides:
              volumes:
                - "${LANDO_VOLUME}:/app"

        compose:
          - .lando.compose.yml
        ```

      * Now, you will need to create your local docker compose file. Create the ```.lando.compose.yml``` file and add these lines:

        ```
        version: '3'
        volumes:
          nfsmount:
            driver: local
            driver_opts:
              type: nfs
              o: addr=host.docker.internal,rw,nolock,hard,nointr,nfsvers=3
              device: ":${PWD}"
        ```

      * Finally, rebuild the app: ```export LANDO_VOLUME='nfsmount' && lando rebuild``` (It's worth adding the variable export to your shell config so that you don't have to remember to use it in the future)
      * If there are no errors, verify that your app is using nfs: run ```lando ssh``` and then ```df -h /app```. You should see something like ```:/Users/username/src/va.gov-cms``` in the Filesystem column instead of ```osxfs```.

## Git

### Troubleshooting

#### File permission errors on git pull

Sometimes, you will see this output when running git pull:

```error: unable to unlink old 'docroot/sites/default/settings.php': Permission denied```

This occurs because Drupal checks and hardens file permissions under the settings directory in [system_requirements()](https://api.drupal.org/api/drupal/core%21modules%21system%21system.install/function/system_requirements/8.8.x). To override this default behavior, create (or edit) `docroot/sites/default/settings/settings.local.php` and add this line:

```$settings['skip_permissions_hardening'] = TRUE;```

## Scripts
There are some scripts created to help with managing the Drupal site locally.

### Shell

1. **Copy the database from PROD:** `./scripts/sync-db.sh` - This script obtains a
recent copy of the PROD database that has been sanitized to protect user data
and imports it into the local Drupal site.
The db export appears here  `.dumps/cms-db-latest.sql`.
1. **Copy the files from PROD:** `./scripts/sync-files.sh` - This copies
the `/sites/default/files/*` from PROD down to your local environment


### Composer

There are a number of helpful composer "scripts" available, located in the [composer.json](composer.json) file, in the `scripts` section. These scripts get loaded in as composer commands.

Change to the CMS repositiory directory and run `composer` to list all commands, both built in and ones from this repo.

The VA.gov project has the following custom commands.

1. `composer set-path`

    Use `composer set-path` command to print out the needed PATH variable to allow running any command in the `./bin` directory just by it's name.

    For example:

    ```bash
    $  composer set-path
    > # Run the command output below to set your current terminal PATH variable.
    > # This will allow you to run any command in the ./bin directory without a path.
    > echo "export PATH=${PATH}"
    export PATH=/Users/VaDeveloper/Projects/VA/va.gov-cms/bin:/usr/local/bin:/usr/local/sbin:/usr/bin:/usr/sbin
    ```

    Then, copy the last line (with all of the paths) and paste it into your desired terminal, and hit ENTER.

    Once the path is set, you can run any of the commands listed in the [bin directory](bin) directly:

    ```bash
    $ phpcs --version
    PHP_CodeSniffer version 2.9.2 (stable) by Squiz (http://www.squiz.net)
    ```

    The path will remain in place as you change directories.


2. `composer va:proxy:socks` or `composer va:proxy:socks&`

    Simply runs the "socks proxy" command which is needed to connect to the VA.gov network. Add the `&` character to run it as a background process.

3. `composer va:proxy:test`

    Test the proxy when it is running.

4. `composer nuke`

    Removes all composer installed directories, useful when you manually
    made changes to any files inside a composer managed directory. e.g.
    docroot/core, docroot/vendor.


@TODO: Document all of the custom composer commands.

See https://getcomposer.org/doc/articles/scripts.md for more information on how to create and manage scripts.

### Drush
  All Drush commands are run with a Lando prefix. (examples)
  * `lando drush uli`
  * `lando drush cr`
  * `lando drush sqlq "show tables"`

### Testing
See [testing](testing.md).

## Lando Certificates

## HTTPS testing (locally/Lando)
You can't test with the VA cert locally using Lando but you can use Lando's self-signed cert. If you need to test the actual cert locally contact the DevOps team to help you setup the vagrant build system to get HTTPS working with VA CA.

To test with Lando's self-signed cert you need to tell your system to trust the Lando Certificate Authority. Instructions are here > https://docs.devwithlando.io/config/security.html

TODO, create upstream PR with `sudo trust anchor --store ~/.lando/certs/lndo.site.pem` for Arch Linux

Note: I had to still import that same CA into Chrome.
Go to chrome://settings/certificates?search=https
Click "Authorities"
Import `.lando\certs\lndo.site.pem`


[Table of Contents](../README.md)
