This is an Aquia Lightning based implementation of Drupal 8 that uses [Lando](https://docs.devwithlando.io/) for local container management.

## Get Started
How to start:
* get lando https://docs.devwithlando.io/installation/installing.html
* `git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git vagov`
* `cd vagov`
* `lando start`
* `lando sync-db`
* `lando sync-files`

Example workflow:
* `git fetch --all`
* `git checkout --branch <VAGOV-000-name> origin/develop`
* `lando composer install`
* `lando sync-db`
* `lando sync-files` # (optional)

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

Running Behat Tests:
* `cd tests/behat`
* `lando behat --tags=name-of-tag`

Running Phpunit Tests:
* `cd tests`
* `lando phpunit {Path-to-test}`
to run a test group use
* `lando phpunit . --group security`

Apply patches:
* Get the patch file:
  * example" https://patch-diff.githubusercontent.com/raw/drupal-graphql/graphql/pull/726.patch
  * for Github, you can usually type in `.patch` at the end of the PR url to get the patch file
  * some people use github, some use drupal.org. drupal is moving to gitlab
* In the "`patches`" property of `composer.json`, make an entry for the package you are patching, if not already there, write an explanation as to what the patch does, and then put the url to the patch 
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

Naming Conventions:
* Modules: `vagov_modulename`
* Content types: `vagov_contentype`
* Fields: `field_[contenttypename]_fieldname`

Xdebug:
* Setup:
    * Enable Xdebug by uncommenting the `xdebug: true` line in .lando.yml
    * Run `lando rebuild`
    * Configure PHPStorm: Go to Settings > Languages & Frameworks > PHP > Debug
    * Check "allow connections" and ensure max connections is 2
    * Enable "Start listening for PHP debug connections"
* Browser:
    * Open index.php and set a test breakpoint on the first line ($autoloader)
    * Go to vagovcms.lndo.site in your browser (no extension needed) and it should trigger an "incoming connection" in PHPStorm, accept it
* CLI:
    * Open Settings > Languages & Frameworks > PHP > Servers and change the server name to "appserver"
    * Set a test breakpoint on /docroot/vendor/drush/drush/drush
    * Run `lando drush status` and it should trigger the breakpoint

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
