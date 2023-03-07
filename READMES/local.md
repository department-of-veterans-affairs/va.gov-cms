# [Environments](environments): Local

## DDEV

We use [DDEV](https://ddev.com/) to power our local sandboxes.  See [ddev.readthedocs.io](https://ddev.readthedocs.io/en/latest/) for general documentation.

### Prerequisites

- [Homebrew](https://brew.sh/) (Mac only)
- [Docker](https://docs.docker.com/get-docker/)

### Getting started

1. Follow the DDEV [installation documentation](https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/).
1. Make sure you run `mkcert -install` to get the ddev certificate root installed. You only need to do this the first time you bring install/run ddev.
1. Run `ddev config global --mutagen-enabled` if you’re on a Mac.
1. Make sure the `va.gov-cms` repo is cloned to a folder somewhere within your home directory.
1. Run `ddev start` in the root of the va.gov-cms repo.

### Commands

Run `ddev help` to list all of the available commands.

To run any of those scripts, you can run `ddev <script-name> [options]` (for example, `ddev blackfire on` to turn on Blackfire profiling).

## Local development settings

Common development settings shared for all local development are at `docroot/sites/default/settings/settings.local.php`. This is committed to the repo and shared for all engineers. It shouldn't be used for changes specific to an individual engineer.

Engineers can customize their individual local environment by creating a file at `docroot/sites/default/settings.local.php`. This file will be ignored by git. Drupal core provides some common local development settings at `docroot/sites/example.settings.local.php`.

Drupal core provides some optional development services at `docroot/sites/default/development.services.yml`. Custom services can be added at `docroot/sites/default/local.services.yml`. This file will be ignored by git. An example is provided at `docroot/sites/default/example.local.services.yml`.

## IDE plugins

There are plugins available to provide inline style checking according to project standards.

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
user data. The db export appears in `.dumps/cms-prod-db-sanitized-latest.sql`.

- **DDEV** `ddev pull va --skip-files`
- **Shell** `./scripts/sync-db.sh`

## Pulling Files

This is rarely needed and can take a long time.  Don't run unless you need to.
This copies the `/sites/default/files/*` from PROD down to your local environment.

- **DDEV** `ddev pull va --skip-db`
- **Shell** `./scripts/sync-files.sh`

### Composer

There are a number of helpful composer "scripts" available.  

These are defined in the [composer.json](composer.json) file, in the `scripts` section, and described in the `scripts-descriptions` section. These are made available as Composer commands.

Change to the CMS repository directory and run `composer list` to list all available commands.

See https://getcomposer.org/doc/articles/scripts.md for more information on how to create and manage scripts.

### Drush

All Drush commands should be run with either a `composer` or `ddev` prefix to ensure they function as intended.

Examples:

- `composer drush uli`
- `ddev drush cr`
- `composer drush sqlq "show tables"`

For a full list of available Drush commands, run `composer drush list`.  See [Drush](./drush.md) for more information.

### Testing

See [testing](testing.md).

## Memory limit issues (e.g. _persistent_ MySQL Server has gone away)

Sometimes your local environment may run out of memory and kill a container.  Sometimes this is the container for MariaDB, the database server.

This can be confirmed by running `docker ps`; if you don't see a container whose name includes "mariadb", then it would appear that the container has failed.

`ddev restart` will fix this in the short term. If the problem persists, bumping up your Docker RAM to 4GB or higher should fix the issue.

[Table of Contents](../README.md)
