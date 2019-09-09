# cms.VA.gov

## About

This is the Content Management System and API for VA.gov.

It is an Aquia Lightning based implementation of Drupal 8.

Table of Contents
=================

1. **Developer Info**
    1. [Project Conventions](READMES/project-conventions.md)
    1. [Environements](READMES/environments.md)
        1. [Builds](READMES/builds.md)
    1. [Testing](READMES/testing.md)
    1. [Debugging](READMES/debugging.md)
1. **Architecture**
    1. Overview
    1. Drupal
    1. MetalSmith
    1. [Interfaces](READMES/interfaces.md)


This is an Aquia Lightning based implementation of Drupal 8 that uses [Lando](https://docs.devwithlando.io/) for local container management.

## Get Started



## HTTPS browser setup for production usage
All computers in VA already have this setup, if you are using a non-VA laptop for development you will need to trust the VA Root Certificate Authority (CA) in your browser(s).

Chrome
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer`
* Go to chrome://settings/certificates?search=https
* Click "Authorities"
* Click "Import" and select VA-Internal-S2-RCA1-v1.cer file downloaded above

Firefox
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-RCA1-v1.cer`
* `wget http://crl.pki.va.gov/PKI/AIA/VA/VA-Internal-S2-ICA1-v1.cer`
* Go to about:preferences#privacy, scroll to bottom
* Click "View Certificates"
* Click "Authorities" tab
* Click "Import"
* Import both files downloaded above

Lando
* `rebuild lando -y`

Since this is a Drupal site, it can be launched with any Drupal development tool.

For regular development, the DSVA team uses [Lando](https://docs.devwithlando.io/) for local container management.

For testing and simple development, you can use the special Composer commands and Drupal Console to launch on any system 
with PHP-CLI and SQLite.

## HTTPS testing (locally/Lando)
You can't test with the VA cert locally using Lando but you can use Lando's self-signed cert. If you need to test the actual cert locally contact the DevOps team to help you setup the vagrant build system to get HTTPS working with VA CA.

To test with Lando's self-signed cert you need to tell your system to trust the Lando Certificate Authority. Instructions are here > https://docs.devwithlando.io/config/security.html

TODO, create upstream PR with `sudo trust anchor --store ~/.lando/certs/lndo.site.pem` for Arch Linux

Note: I had to still import that same CA into Chrome.
Go to chrome://settings/certificates?search=https
Click "Authorities"
Import `.lando\certs\lndo.site.pem`

## Custom Composer Scripts

There are a number of helpful composer "scripts" available, located in the [composer.json](composer.json) file, in the `scripts` section. These scripts get loaded in as composer commands.

Change to the CMS repositiory directory and run `composer` to list all commands, both built in and ones from this repo.

The VA.gov project has the following custom commands.

1. `set-path`

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


2. `va:proxy:start`

    Simply runs the "socks proxy" command which is needed to connect to the VA.gov network. Add the `&` character to run it as a background process.

3. `va:proxy:test`

    Test the proxy when it is running.

4. `nuke`

    Removes all composer installed directories, useful when you manually
    made changes to any files inside a composer managed directory. e.g.
    docroot/core, docroot/vendor.

@TODO: Document all of the custom composer commands.

See https://getcomposer.org/doc/articles/scripts.md for more information on how to create and manage scripts.

## VA.gov WEB

This Drupal site acts solely as a CMS and API. The actual website for va.gov is powered by the [vets-website](https://github.com/department-of-veterans-affairs/vets-website) repo.

The `WEB` project is built on Metalsmith. The project consumes the Drupal CMS GraphQL endpoint and generates static files for VA.gov.

## WEB+CMS Unity Initiative

This repository now includes the vets-website project, and it is built on `composer install`. 

The "Unification" or "Unity" initiative aims to stabilize the project by unifying the codebases to allow for paired releases.

This has many benefits:

- Prepare the source code for the entire project, CMS+WEB in a single command. 
- Run instances of CMS and WEB site by side, locked into the right versions.
- Run tests against BOTH CMS and WEB to ensure compatibility.
- Release both projects at once. CMS is handled as a dependency of WEB, so we can tag a release of web, then tag a release of CMS that is locked to that release of WEB.

See `composer.json` `scripts.va:web:install` section for the code that triggers the WEB build process.
The "Unification" or "Unity" initiative aims to stabilize the project by unifying the codebases to allow for paired releases.

This has many benefits:

- Prepare the source code for the entire project, CMS+WEB in a single command. 
- Run instances of CMS and WEB site by side, locked into the right versions.
- Run tests against BOTH CMS and WEB to ensure compatibility.
- Release both projects at once. CMS is handled as a dependency of WEB, so we can tag a release of web, then tag a release of CMS that is locked to that release of WEB.

See `composer.json` `scripts.va:web:install` section for the code that triggers the WEB build process.

### Launch with Composer

1. [Install Composer "globally"](https://getcomposer.org/doc/00-intro.md#globally). 
2. Clone this repo:

    ```bash 
    $ git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git
    $ cd va.gov-cms
3. Install and Launch
 
    Run the "va:start" composer command to install and launch a local instance of cms.va.gov:

    ```bash
    $ composer va:start
    ```
    
    At the end of the long process you should get output that looks like this:
    
    ```
    > # cms.VA.gov installed: Username: admin  Password: admin
    > drupal server localhost:80 --ansi

     [OK] Executing php from "/usr/bin/php".                                        
     Listening on "http://localhost:80".                                          
    ```
    The port (80) may change based on your system. The Drupal Console command `drupal server` will automatically find an available port, defaulting to 80 if available.
    
    Click the link that looks like "http://localhost:80" and log in with username `admin` and password `admin`.
    
    ##### About `composer va:start`
    
    The `composer va:start` command (shortcut `composer v:s`) is defined in [composer.json](composer.json#L123). It is similar to the [Drupal Quickstart command](https://www.drupal.org/docs/8/install/drupal-8-quick-start-command) and was inspired by the default commands in the [Acquia Lightning Composer Project](https://github.com/acquia/lightning-project/blob/master/composer.json#L69).
    
    The `composer v:s` command does the following:
    
    1. Runs `composer install`.
    2. Runs `drupal site:install va_gov` to run the [VA.gov CMS Install Profile](docroot/profiles/va_gov).*
    3. Runs `drupal server` to launch a running instance of the site.**
    
    \**The install profile does not yet automatically import the configuration. Right now config import on an empty site fails because it's looking for modules in headless_lightning. The code is there to import, it is commented out. See [va_gov.profile](docroot/profiles/va_gov/va_gov.profile).*
    
    \***The site is launched using a simple PHP CLI web server and connects to whatever database credentials are setup in [settings.php](docroot/sites/default/settings.php). By default, this command will use SQLite, which is pre-installed on most systems.*

4. Reinstall & Launch

    The `composer va:reinstall` command (shortcut `v:r`) will delete the SQLite database, reinstall the site from the [VA.gov CMS Install Profile](docroot/profiles/va_gov) and restart the PHP web server.
    
    **WARNING: This will delete any content or un-exported configuration in your site.**
    
    *This command can currently only reinstall sites that use SQLite.*
    
 4. Configure, Export, Commit.
 
    ##### Command `composer va:config:import` and `composer va:config:export` 
    
    The structure and behavior of the Drupal site is determined by it's "Configuration". You can import and export 
    the Drupal configuration with these helpful composer commands.
    
    These are defined in `composer.json` as well and are just wrappers for `drush` and `drupal` console commands.
    
    - The `composer va:config:import` command (shortcut `v:c:i`) will import the Drupal configuration from the [config/sync](config/sync) folder.
    - The `composer va:config:export` command (shortcut `v:c:e`) will export the Drupal configuration from the site into the [config/sync](config/sync) folder.
    
    After running `va:config:export`, you can commit the changes to your branch. 

### Launch with Lando
* get lando https://docs.devwithlando.io/installation/installing.html
* `git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git vagov`
* `cd vagov`
* `lando start`

### How to sync:

Run these scripts to recreate the site locally. The server holding the database dump must be accessed via a proxy.

Once you have [submitted your SSH Public Key](https://github.com/department-of-veterans-affairs/vets-external-teams/blob/master/Onboarding/request-access-to-tools.md#additional-onboarding-steps-for-developers), you can run the following commands to create a local instance of https://cms.va.gov:

* `ssh socks -D 2001 -N &` # Runs an SSH socks proxy in a separate process. Run `ps` to see the running ssh process.
* `./scripts/sync-db.sh` # Downloads a recent, sanitized database export file to `.dumps/cms-db-latest.sql`.
* `./scripts/sync-files.sh` # Downloads a recent backup of site files to `sites/default/files`, and runs `lando db-import cms-db-latest.sql`.

### Example workflow:

* `git fetch --all`
* `git checkout --branch <VAGOV-000-name> origin/develop`
* `lando composer install`
* `scripts/sync-db.sh`
* `scripts/sync-files.sh` # (optional)

What it does:
* Spins up php, mysql, and node containers
* Dependencies (including components project) are pulled in via composer
* Base config installs uswds and sets a subtheme for this project (project is headless, so this isn't critical)

How to use:
* visit the site by clicking one of the urls provided (aliased and https options are available)
* compile scss to css by going to theme dir and running `lando gulp`
* drush commands are prefixed with lando, e.g.: `lando drush cr`
* composer is used for project management, e.g.: `composer require drupal/uswds`

Theme structure (project is headless, so this isn't critical):
* Base theme is USWDS: https://www.drupal.org/project/uswds
* vagov Subtheme lives in themes/custom


### Patching

Apply patches:
* Get the patch file:
  * example" https://patch-diff.githubusercontent.com/raw/drupal-graphql/graphql/pull/726.patch
  * for Github, you can usually type in `.patch` at the end of the PR url to get the patch file
  * some people use github, some use drupal.org. drupal is moving to gitlab
* In the "`patches`" property of `composer.json`, make an entry for the package you are patching, if not already there, write an explanation lando sync-dbas to what the patch does, and then put the url to the patch
  * ex:
  * ```
    "patches": {
                   "drupal/migration_tools": {
                       "Add changeHtmlContents DomModifier method": "https://www.drupal.org/files/issues/2018-11-26/change_html_contents-3015381-3.patch",
    ```
* Run `lando composer update <source>/<package>`
  * `lando composer update drupal/graphql`

groups include
 - migration
 - performance
 - security

Triggering Metalsmith static site builds at /admin/config/build-trigger
* @see va_gov_build_trigger.module
* Uncomment the va-socks-proxy code in .lando.yml
* Uncomment the "VA_CMS_BOT_GITHUB_AUTH_TOKEN" in the appserver container in .lando.yml
* `export` the following local environment variables from
va.gov-cms-devops Ansible Vault and then run `lando rebuild --yes`.
Contact Mouncif or Elijah in Slack #cms-engineering to obtain these ENV variables:
  * VA_CMS_BOT_GITHUB_AUTH_TOKEN
  * VA_SOCKS_PROXY_PRIVATE_KEY
  * VA_SOCKS_PROXY_SSH_CONFIG

Trigger local build of Drupal content in vets-website `yarn build --pull-drupal`


Troubleshooting:
* Sometimes after initial setup or `lando start`, Drush is not found. Running `lando rebuild -y` once or twice usually cures, if not, see: https://github.com/lando/lando/issues/580#issuecomment-354490298

Workflow:
* We use [drupal-spec-tool](https://github.com/acquia/drupal-spec-tool) to keep track of config changes, and sync tests
* After updating config, cd into /tests, and run `lando behat --tags=spec`
* Discrepancies between code and config will be reflected in test output
* Visit https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit?usp=sharing, choose the tab
related to config changes, and update cells accordingly.
* Go back to https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit?usp=sharing, and copy the cell that
pertains to the test you are updating, and paste into the test file in /tests/behat (before pasting, take note of any tags related to test(s), and add them back in after pasting).
* Run tests again, correcting and updating the spreadsheet, and exporting accordingly until tests and spreadsheet are in sync.
* Export config to code: `lando drush config:export` then commit changes to code.

Todo:
* decide how we are going to sync files across environments
* work out settings.php for various environments - lando db settings are stored in settings.lando.php

## GraphQL

The site uses GraphQL (https://www.drupal.org/project/graphql) as the mechanism for delivering JSON to Metalsmith for the static site build (see https://github.com/department-of-veterans-affairs/vets-website for info and setup).

The GraphQL endpoint is at `/graphql`. GraphQL Explorer to assist in writing queries is avilable via the CMS admin at: `/graphql/explorer`. Sample GraphQL query to grab all entities in the system:

```
query {
  nodeQuery()  {
    entities {
      entityLabel
      entityType
    }
  }
}
```

### Custom Composer Scripts

There are a number of helpful composer "scripts" available, located in the [composer.json](composer.json) file, in the `scripts` section. These scripts get loaded in as composer commands.

Change to the CMS repositiory directory and run `composer` to list all commands, both built in and ones from this repo.

The VA.gov project has the following custom commands.

1. `set-path`

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
    
    Useful for running the `behat` tests:
    
    ```bash
    $ cd tests/behat
    $ behat
    ```

2. `va:proxy:start`

    Simply runs the "socks proxy" command which is needed to connect to the VA.gov network. Add the `&` character to run it as a background process.

3. `va:proxy:test`

    Test the proxy when it is running.

@TODO: Document all of the custom composer commands.

See https://getcomposer.org/doc/articles/scripts.md for more information on how to create and manage scripts.
  

# Branches

The `develop` branch is now protected. It requires tests to pass and a manual review to be merged.
