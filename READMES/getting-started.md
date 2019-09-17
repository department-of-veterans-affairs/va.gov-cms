# Getting Started

Since this is a Drupal site, it can be launched with any Drupal development tool.

For regular development, the DSVA team uses [Lando](https://docs.devwithlando.io/) for local container management.

For testing and simple development, you can use the special Composer commands and Drupal Console to launch on any system 
with PHP-CLI and SQLite.

## Step 1: Get Source Code.

* Fork the repo by pressing the "Fork" button: [github.com/department-of-veterans-affairs/va.gov-cms](https://github.com/department-of-veterans-affairs/va.gov-cms)
* Clone your fork:

   ```sh
    $ git clone git@github.com:YOUR-GITHUB-USERNAME/va.gov-cms
    $ cd va.gov-cms  
   ```
- Add upstream repo (Recommended):

   ```sh
   $ git remote add upstream git@github.com:department-of-veterans-affairs/va.gov-cms.git
   ```
  You should periodically update your branch from `upstream:develop` branch:
  
  ```sh
   $ git pull upstream develop
  ``` 


## Step 2: Launch development environment

It is possible to run this site with Lando or any other Drupal development tool,
including PHP's built-in web server.

If you don't want to worry about your development machine's PHP version or 
libraries, use Lando.

### Option 1: Lando

1. [Get Lando](https://docs.lando.dev/basics/installation.html)
2. Change into the project directory and run `lando start`:

    ```
    $ cd va.gov-cms
    $ lando start
    ```
   
The `lando start` command will include the `composer install` command.

See [Environments: Local](./local.md) for more information on Lando.

### Option 2: Local PHP

If you are used to using tools like `composer` and `drush` locally, you can 
install the project using your native Terminal:

1. Change into the project directory and run `composer install`:

    ```
    $ cd va.gov-cms
    $ composer install
    ```
1. Run `composer va:start` to launch a running Drupal instance using PHP web-server and SQLite.



## Step 3: Sync your local site with Production Data

You need a copy of the production database to get the full VA.gov CMS running.

Use the provided scripts to download a database and files backup into the 
correct locations in your local development environment.

* `.scripts/sync-db.sh`
* `.scripts/sync-files.sh`

NOTE: These scripts download the SQL and files first, then attempts to use 
`lando` commands to import them. 

If you are not using lando, the scripts will
 fail, but the files will still be available. The `sync-db.sh` script downloads the 
 SQL file to `./.dumps/cms-prod-db-sanitized-latest.sql`
 
See [Environments: Local](./local.md) for more information on Lando.

[Table of Contents](../README.md)
