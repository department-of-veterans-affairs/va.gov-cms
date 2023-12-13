# Getting Started

Since this is a Drupal site, it can be launched with any Drupal development tool.

For regular development, the DSVA team uses [ddev](https://ddev.com/) for local container management.

For testing and simple development, you can use the special Composer commands and Drupal Console to launch on any system
with PHP-CLI and SQLite.

## Quickstart with Codespaces

See [the Codespaces README](./codespaces.md) to get a fully functional cloud-based development environment.

## Step 1: Get Source Code / Git Setup

- Clone the repo: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms)
  ```sh
   $ git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git
   $ cd va.gov-cms
  ```

* Make sure your local repo is aware of what's on the remotes.
  ```sh
  $ git fetch --all
  ```

* Make sure git is not tracking perms
  ```sh
  $ git config core.fileMode false
  $ git config --global core.fileMode false
  ```

* Make sure rebase is your default
  ```sh
  $ git config --global branch.autosetuprebase always
  $ git config --global branch.main.rebase true
  ```

*  Make changes to simplesaml storage not be tracked locally.

  ```sh
   git update-index --skip-worktree samlsessiondb.sq3
  ```

  You should periodically update your branch from `origin main` branch. This will rebase your current branch on top of new commits in main:

  ```sh
   $ git pull origin main
  ```

## Step 2: Launch development environment

1. Set ddev environment variables:

```bash
$ cd va.gov-cms
$ cp .env.example .env
```

2. [Install ddev](https://ddev.readthedocs.io/en/stable/#installation)
3. Change into the project directory and run `ddev start`:

```bash
$ ddev start
```

The `ddev start` command will include the `composer install` command. Ensure that a CMS account is created and [Step 3](#step-3-sync-your-local-site-with-production-data) is run to sync login information before logging into the local CMS environment.

See [Environments: Local](./local.md) for more information on ddev.

## Step 3: Sync your local site with Production Data

You need a copy of the production database to get the full VA.gov CMS running.

Use the provided ddev commands to download a database and files backup into the
correct locations in your local development environment.

- `ddev pull va `  or  `ddev pull va --skip-files`

NOTE: This command downloads and imports the db followed by any configuration import.

If you are not using ddev, the scripts will
fail, but the files will still be available. The `sync-db.sh` script downloads the
SQL file to `./.dumps/cms-prod-db-sanitized-latest.sql`

See [Environments: Local](./local.md) for more information on ddev.

[Table of Contents](../README.md)
