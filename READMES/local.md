# [Environments](environments): Local

## DDEV

We use ddev to power our local sandboxes.
### Prerequisites

- [Homebrew](https://brew.sh/)  (Mac only)
- [Docker](https://docs.docker.com/get-docker/)

### Getting started

1. Follow the ddev installation documentation.
2. If installing on Windows with WSL2, skip step 12.
3. Make sure you run mkcert -install to get the ddev certificate root installed. You only need to do this the first time you bring install/run ddev.
4. Run ddev config global --mutagen-enabled if you’re on a Mac.
5. Make sure the va.gov-cms repo is cloned to a folder under ~
6. Run ddev start in the root of the va.gov-cms repo.

### Day-to-day commands
You can look in `.ddev/commands/web` for a list of the commands that are available. To run any of those scripts, you can run ddev [scriptname]  (for example, ddev phpstan to run the phpstan checks). You can also run ddev help to list all of the available commands, including some basic descriptions about all of the custom commands. Note that the custom commands may not show up in that list until after you’ve run ddev start the first time.
- auth - A collection of authentication commands
- behat - Run behat test located in ./tests/behat (shell web container command)
- blackfire - Enable or disable blackfire.io profiling (global shell web container command)
- composer - Executes a composer command within the web container
- config - Create or modify a ddev project configuration in the current directory
- debug - - A collection of debugging commands
- delete - Remove all project information (including database) for an existing project
- describe -  Get a detailed description of a running ddev project.
- drush - - Run drush CLI inside the web container (global shell web container command)
- exec - Execute a shell command in the container for a service. Uses the web service by default.
- export-db - Dump a database to a file or to stdout
- help - Help about any command
- hostname - Manage your hostfile entries.
- import-db - Import a sql file into the project.
- import-files - Pull the uploaded files directory of an existing project to the default public upload directory of your project.
- launch - Launch a browser with the current site (shell host container command)
- list - List projects
- logs - Get the logs from your running services.
- migrate-sync - Copy migration ymls from va_gov_migrate to config/sync and run config import. Always edit in va_gov_migrate. (shell web container command)
- mutagen - Commands for mutagen status and sync, etc.
- mysql - run mysql client in db container (shell db container command)
- npm - Run npm commands (shell web container command)
- pause - uses 'docker stop' to pause/stop the containers belonging to a project.
- phpstan - Run PHPStan static analysis on custom code (shell web container command)
- phpunit - Run VA PHPUnit test found in va/tests/phpunit (shell web container command)
- phpunit-run - Run specific PHPUnit tests (shell web container command)
- post-update -  Run updates after pulling new code (shell web container command)
- poweroff - Completely stop all projects and containers
- pre-commit - Run pre-commit checks (shell web container command)
- pull - Pull files and database using a configured provider plugin.
- push - push files and database using a configured provider plugin.
- restart - Restart a project or several projects.
- share - Share project on the internet via ngrok.
- snapshot - Create a database snapshot for one or more projects.
- ssh - Starts a shell session in the container for a service. Uses web service by default.
- start - Start a ddev project.
- stop - Stop and remove the containers of a project. Does not lose or harm anything unless you add --remove-data.
- task - Run taskfile target (shell web container command)
- test - Run all VA.gov tests, as defined in tests.yml. Add arguments to run subsets of test. For example, "ddev test web" will run all of the "va/web/*" tests. (shell web container command)
- test-performance - Run performance tests (shell web container command)
- version - print ddev version and component versions
- web-build - Build the web frontend (shell web container command)
- xdebug - Enable or disable xdebug (shell web container command)
- xhprof - Enable or disable xhprof (global shell web container command)
- yarn - Run yarn inside the web container in the root of the project (Use --cwd for another directory) (global shell host container command)

## Toggling xdebug

`ddev xdebug on` or `ddev xdebug off` . This does not restart your container. ddev has built-in support for xdebug + [comprehensive documentation](https://ddev.readthedocs.io/en/stable/users/step-debugging/) for how to use it.

Currently, if you are debugging drush commands, it is recommended that you enable xdebug, ddev ssh to shell into your container, and run drush commands there via `drush <command>`.

## Toggling Blackfire

See the [ddev Blackfire documentation](https://ddev.readthedocs.io/en/stable/users/blackfire-profiling/) for setup and use instructions.
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


## Pulling DB from Prod
Either method pulls a public copy of prod that has been sanitized to protect
user data. The db export appears here  `.dumps/cms-db-latest.sql`.

- **DDEV** `ddev pull va --skip-files`
- **Shell** `./scripts/sync-db.sh`


## Pulling Files
This is rarely needed and can take a long time.  Don't run unless you need to.
This copies the `/sites/default/files/*` from PROD down to your local environment.
- **DDEV** `ddev pull va --skip-db`
- **Shell** `./scripts/sync-files.sh`

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
