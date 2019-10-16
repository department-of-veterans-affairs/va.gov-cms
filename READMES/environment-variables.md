# Environment Variables

This Drupal site is designed to read critical environment variables from the `.env` file in the root of the repository,
leveraging the [`phpdotenv` library](https://github.com/vlucas/phpdotenv).

The file [EnvironmentHandler.php](../scripts/composer/EnvironmentHandler.php) loads this file for every request or CLI 
action that includes the Drupal autoloader. 

Any existing environment variables will be overwritten if the same variable exists in this file.

## Benefits

Using a `.env` file instead on relying on the server environment has many benefits: 
 
 - When the Composer autoloader is forced to use the `.env`, all of tools that use it will have the exact
   same environment. This includes Drupal itself, and everything in the [.bin folder](../bin): `drush`, `phpunit`, `behat`, `yaml-tests`, `etc`. 
 - No need to write variables to server configuration.
 - No need to pass variables through docker, docker-compose, Dockerfiles, etc.
 - No need to worry about the execution environment: 
   - Every system (local, CI, BRD, etc) has it's way of loading the "execution environment" for running processes. 
   - In other words, commands like `drush cache-rebuild` or `composer yaml-tests` are run by the `apache` user in BRD,
     the `aegir` user in CMS-CI, and the `www-data` user in Lando.
   - By using a single `.env` file for all environments, we no longer have to maintain scripts to set system-specific
     environment variables. 
 
See [Why .env?](https://github.com/vlucas/phpdotenv#why-env) on the dotenv README for more information.

## Setting the Environment

All environments except Lando will load the `.env` file, if it exists.
 
The `.env` file is ignored in the git repo. It must be created by the ops platform that is running the code.

### Local Development

The default values contained in [`.env.lando`](../.env.lando) are designed to work in a standard Lando environment. You
shouldn't have to change these, but you are free to.

If you are using a different Drupal development environment, you can create your own `.env` file by copying the `.env.lando`
file and changing the values as needed.

### CMS-CI

The `.env` file in CMS-CI is written using DevShop/Drush hooks in a Drush include file on the CMS-CI server: `/var/aegir/.drush/va.drush.inc`

The drush hook `hook_devshop_environment_file_alter` is used to set more environment variables. The VA-specific hook
looks like this:

```php
<?php

/**
 * Implement hook_devshop_environment_file_alter()
 */
function va_devshop_environment_file_alter(&$environment){
  // Pass AEGIR database variables to CMS variables.  
  $environment['CMS_MARIADB_DATABASE'] = $_SERVER['db_name'];
  $environment['CMS_MARIADB_USERNAME'] = $_SERVER['db_user'];
  $environment['CMS_MARIADB_PASSWORD'] = $_SERVER['db_passwd'];
  $environment['CMS_MARIADB_HOST'] = $_SERVER['db_host'];

  // Set DRUPAL_ADDRESS, DTT_BASE_URL, and SIMPLETEST_BASE_URL to match the site's URL. 
  $environment['DRUPAL_ADDRESS'] =
  $environment['DTT_BASE_URL'] =
  $environment['SIMPLETEST_BASE_URL'] =
    'http://' . d()->uri;

  // Set API keys that need to be private
  $environment['GITHUB_TOKEN'] = '00000';
  $environment['FACILITY_API'] = '00000';

}

```

### CMS in BRD

The BRD environments for CMS include DEV, STAGING, and PROD. The environment variables for these are set in the [DevOps Repo](https://github.com/department-of-veterans-affairs/devops/tree/master/ansible/deployment/config).

The VA `devops` repo is private. Sensitive environment variables for BRD *may* be stored in Credstash if it is deemed 
necessary, but it is not required.

See the files:
 - [ansible/deployment/config/cms-vagov-dev](https://github.com/department-of-veterans-affairs/devops/blob/master/ansible/deployment/config/cms-vagov-dev.yml#L125) 
 - [ansible/deployment/config/cms-vagov-staging](https://github.com/department-of-veterans-affairs/devops/blob/master/ansible/deployment/config/cms-vagov-staging.yml#L125) 
 - [ansible/deployment/config/cms-vagov-prod](https://github.com/department-of-veterans-affairs/devops/blob/master/ansible/deployment/config/cms-vagov-prod.yml#L125) 
 


### CredStash

Environment variables like API tokens should be treated as secrets.

For secrets, the BRD system uses CredStash. See the [DevOps Repo: CredStash Readme](https://github.com/department-of-veterans-affairs/devops/blob/fef2340e5891c5aef6f7ed23af4d5a6f56711468/ansible/README.md#credstash) section for information on how to add 
values to CredStash. 
