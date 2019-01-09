This is a Lightning based implementation of D8 that uses lando for container management.

## Get Started
How to start:
* get lando https://docs.devwithlando.io/installation/installing.html
* `git clone git@github.com:department-of-veterans-affairs/va.gov-cms.git vagov`
* `cd vagov`
* `lando start`
* `cd scripts; ./sync-db.sh`

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

Naming Conventions:
* Modules: `vagov_modulename`
* Content types: `vagov_contentype`
* Fields: `field_[contenttypename]_fieldname`

Xdebug:
* Setup:
    * Enable Xdebug by uncommenting the `xdebug: true` line in .lando.yml
    * Configure PHPStorm: Go to Settings > Languages & Frameworks > PHP > Debug
    * Check "allow connections" and ensure max connections is 2
    * Enable "Start listening for PHP debug connections"
* Browser:
    * Go to vagovcms.lndo.site in your browser (no extension needed) and it should trigger an "incoming connection" in PHPStorm, accept it
    * Open index.php and set a test breakpoint on the first line ($autoloader)
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
