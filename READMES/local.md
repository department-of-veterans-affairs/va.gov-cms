# [Environments](environments): Local

For ddev docs, please see the CMS Platform's [Confluence guide](https://vfs.atlassian.net/wiki/spaces/PCMS/pages/1956937732/ddev).

## Git

### Troubleshooting

#### File permission errors on git pull

Sometimes, you will see this output when running git pull:

```error: unable to unlink old 'docroot/sites/default/settings.php': Permission denied```

This occurs because Drupal checks and hardens file permissions under the settings directory in [system_requirements()](https://api.drupal.org/api/drupal/core%21modules%21system%21system.install/function/system_requirements/8.8.x). To override this default behavior, create (or edit) `docroot/sites/default/settings/settings.local.php` and add this line:

```$settings['skip_permissions_hardening'] = TRUE;```

## Local development settings
Common development settings shared for all local development are at `docroot/sites/default/settings/settings.local.php`. This is committed to the repo and shared for all engineers. It shouldn't be used for changes specific to an individual engineer.

Engineers can customize their individual local environment by creating a file at `docroot/sites/default/settings.local.php`. This file will be ignored by git. Drupal core provides some common local development settings at `docroot/sites/example.settings.local.php`.

Drupal core provides some optional development services at `docroot/sites/default/development.services.yml`. Custom services can be added at `docroot/sites/default/local.services.yml`. This file will be ignored by git. An example is provided at `docroot/sites/default/example.local.services.yml`.
## IDE plugins
There are plugins available to provide in-line style checking according to project standards.

### eslint
- [PhpStorm](https://www.jetbrains.com/help/phpstorm/eslint.html)
- [VS Code](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint)

### PHPCS
- [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-php-code-sniffer.html)
- [VS Code](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs)

### Stylelint
- [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-stylelint-code-quality-tool.html)
- [VS Code](https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint)

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

Change to the CMS repository directory and run `composer` to list all commands, both built in and ones from this repo.

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
  All Drush commands are run with a ddev prefix. (examples)
  * `ddev drush uli`
  * `ddev drush cr`
  * `ddev drush sqlq "show tables"`

#### Custom Drush Commands
  As noted above, these should normally be run with a `ddev` prefix.  Dashes may be substituted for colons.
  * `drush va-gov:get-deploy-mode` -- Indicates whether the CMS is currently in Deploy Mode, which is a precautionary measure used to prevent content changes while content is being deployed.
  * `drush va-gov:enable-deploy-mode` -- Sets the Deploy Mode flag to TRUE.  This is not normally necessary.
  * `drush va-gov:disable-deploy-mode` -- Sets the Deploy Mode flag to FALSE.  This is not normally necessary.
  * `drush va-gov:build-frontend` -- Adds a request to build the vets-website frontend and deploy content to the queue, but does not perform it.  If passed the `--dry-run` option, will output the commands that would be executed so they might be run interactively.
  * `drush va-gov:build-frontend:process-queue` -- Processes the frontend build queue, executing any currently enqueued jobs.
  * `drush va-gov:build-frontend:empty-queue` -- Removes all jobs currently in the frontend build queue.
  * `drush va-gov:build-frontend:delete-jobs` -- Delete all enqueued jobs.  Note that this use of "enqueued" refers specifically to the `advancedqueue`'s internal state.  A job is not necessarily in the "enqueued" state just because it's been added to the queue.  To completely empty the queue in all situations, use the `drush va-gov:build-frontend:empty-queue` command.
  * `drush va-gov:build-frontend:release-jobs` -- Release all enqueued jobs.  This refers to releasing the leases that mark a claim on the job so that the jobs might be claimed and executed by a new process.  This is not normally necessary.
  * `drush va-gov:build-frontend:list-jobs` -- Output a sparsely-formatted list of the current jobs in the queue.  This should indicate the job's ID, status, and the commands it would invoke during processing.

### Testing
See [testing](testing.md).

## Memory limit issues (e.g. MySQL Server has gone away)
Sometimes your local env may run out of memory when clicking around the Drupal environment. `ddev restart` will fix
this in the short term. If the problem persists, bumping up your docker ram to 4GB or higher should fix the issue.

[Table of Contents](../README.md)
