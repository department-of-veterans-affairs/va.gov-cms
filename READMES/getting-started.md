# Getting Started

Since this is a Drupal site, it can be launched using any Drupal development tool.

For regular development, the DSVA team uses [DDEV](https://ddev.com/) for local container management.

For testing and simple development, you can use the special Composer commands and Drupal Console to launch on any system
with PHP-CLI and SQLite.

## Step 1: Get Source Code / Git Setup

- Clone the repo: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms) (Please clone the main repo rather than forking it.)
  ```sh
   $ git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git
   $ cd va.gov-cms
  ```

- Make sure your local repo is aware of what's on the remotes.
  ```sh
  $ git fetch --all
  ```

- Make sure git is not tracking perms
  ```sh
  $ git config core.fileMode false
  $ git config --global core.fileMode false
  ```

- Make sure rebase is your default
  ```sh
  $ git config --global branch.autosetuprebase always
  $ git config --global branch.main.rebase true
  ```

-  Make changes to simplesaml storage not be tracked locally.

   This command starts the local containers and runs `composer install` automatically.

Once complete, you will have a running local Drupal site accessible at a `.ddev.site` URL.
Before attempting to log in, complete Step 3 to sync your local data.

For additional information about local environments, see [Local Environment Documentation](./local.md).

### Step 3: Sync Your Local Site with Production Data

To get a functional CMS experience, you’ll need a copy of the sanitized production database:

```bash
$ ddev pull va --skip-files
```

> **Note:**
> Do **not** run `ddev pull va` (without `--skip-files`) unless you explicitly need all CMS-hosted content files.
> The `--skip-files` flag downloads only the database and is sufficient for most developers.

This command downloads the latest sanitized production database, imports it, and runs configuration imports automatically.

If you are not using DDEV, you can still manually download the same database using:

```bash
$ ./scripts/sync-db.sh
```

The SQL file will be available at:

```
./.dumps/cms-prod-db-sanitized-latest.sql
```

See [Environments: Local](./local.md) for more details on working with DDEV.


## Quickstart with Codespaces

If you prefer a cloud-based development setup, see the [Codespaces README](./codespaces.md) for a fully functional environment that requires no local setup.

## Verification

To confirm the documentation works as intended:

1. Follow the Quickstart instructions from a clean machine.
2. Ensure `ddev start` completes without errors.
3. Verify the CMS is accessible at the generated `.ddev.site` URL.
4. Log in using `ddev drush uli` to confirm local access.
5. Note any unclear or incorrect steps and report back to the CMS team.


[Table of Contents](../README.md)