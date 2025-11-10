# [Environments](environments): Local

## DDEV

We use [DDEV](https://ddev.com/) to power our local sandboxes.  
See [DDEV documentation](https://ddev.readthedocs.io/en/latest/) for general information.

## Quickstart

This section provides a concise, end-to-end guide for getting Drupal running locally with DDEV.  
This Quickstart has been tested by non-Drupal developers to ensure it’s easy to follow.

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/)
- [DDEV](https://ddev.readthedocs.io/en/latest/users/install/ddev-installation/)
- [mkcert](https://github.com/FiloSottile/mkcert) — run `mkcert -install` once to install HTTPS certificates
- [Homebrew](https://brew.sh/) (Mac only)

### Step 1: Clone and Configure

```bash
$ git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git
$ cd va.gov-cms
$ cp .env.example .env
```

Ensure the repository is up-to-date:

```bash
$ git fetch --all
$ git pull origin main
```

### Step 2: Start the Local Environment

Run the following to initialize and start DDEV:

```bash
$ ddev start
```

This will build the local containers, install Composer dependencies, and initialize Drupal.

Once setup completes, your local CMS should be available at:

```
https://va-gov-cms.ddev.site
```

### Step 3: Pull the Production Database (Sanitized)

Populate your environment with production-like data:

```bash
$ ddev pull va --skip-files
```

> This downloads a sanitized copy of the production database and imports it automatically.
> The database dump will be stored in `.dumps/cms-prod-db-sanitized-latest.sql`.

If you need uploaded files (rarely required):

```bash
$ ddev pull va --skip-db
```

This copies the `/sites/default/files` directory from production.

### Step 4: Log In to the Local CMS

To log in:

```bash
$ ddev drush uli
```

Then open the generated one-time login link in your browser.

## Commands

Run `ddev help` to view available commands.
Project-specific commands can be run with:

```bash
$ ddev <command-name>
```

Examples:

```bash
$ ddev blackfire on
$ ddev drush cr
```

## Local Development Settings

Common development settings are stored at:

```
docroot/sites/default/settings/settings.local.php
```

This file is shared across all engineers.
For individual configuration, create your own local settings file:

```
docroot/sites/default/settings.local.php
```

This file is ignored by Git.

Additional service overrides can be added in:

```
docroot/sites/default/local.services.yml
```

See examples provided in the repository.

## Composer

Helpful Composer scripts are defined in `composer.json` under `scripts` and `scripts-descriptions`.
To view all available commands:

```bash
$ composer list
```

See [Composer Scripts Documentation](https://getcomposer.org/doc/articles/scripts.md) for more details.

## Drush

Drush commands should be run using either `composer` or `ddev`:

```bash
$ composer drush uli
$ ddev drush cr
$ composer drush sqlq "show tables"
```

To see all available commands:

```bash
$ composer drush list
```

For more information, see [Drush Documentation](./drush.md).

## IDE Plugins

Recommended IDE extensions for code quality and linting:

### ESLint

* [PhpStorm](https://www.jetbrains.com/help/phpstorm/eslint.html)
* [VS Code](https://marketplace.visualstudio.com/items?itemName=dbaeumer.vscode-eslint)

### PHPCS

* [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-php-code-sniffer.html)
* [VS Code](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs)

### Stylelint

* [PhpStorm](https://www.jetbrains.com/help/phpstorm/using-stylelint-code-quality-tool.html)
* [VS Code](https://marketplace.visualstudio.com/items?itemName=stylelint.vscode-stylelint)

## Testing

See [Testing Documentation](testing.md) for details on how to run tests locally.

## Troubleshooting

### Memory Limit Issues (e.g. “MySQL Server has gone away”)

If your local environment runs out of memory and a container stops (often MariaDB):

1. Run `docker ps` to verify if the MariaDB container is missing.

2. Restart your environment:

   ```bash
   $ ddev restart
   ```

3. If the issue persists, increase Docker’s RAM allocation to **4GB or higher**.

## Verification Checklist

Before signing off:

* [ ] Followed all Quickstart steps from a clean machine.
* [ ] Confirmed `ddev start` completes successfully.
* [ ] Confirmed CMS loads at `.ddev.site`.
* [ ] Verified `ddev pull va --skip-files` syncs data successfully.
* [ ] Logged in using `ddev drush uli`.
* [ ] Noted any unclear or incorrect instructions.


[Table of Contents](../README.md)