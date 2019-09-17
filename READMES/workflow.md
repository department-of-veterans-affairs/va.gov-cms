# Workflow
1. Git
1. Drupal SpecTool
1. Patching Modules

## Git
To avoid cluttering up the main repo with lots of branches, please push your branches to your fork and make your pull request from your fork to the upstream repo.

### Branches
 We are currently working off a two branch system.  Development is performed on a fresh branch off `develop`  At the time of release, code is merged from `develop` into `master` and tagged appropriately.  Production runs off of tagged master.
Develop is protected and requires both approval from code review and passing tests to be merged.  Commits within PR's are squashed and merged when they are accepted so that the only relate to one git commit, even if they originally contained multiple commits.
**Note:** Soon we will be moving to continuous deployment and all merge requests will be made against the master branch and the develop branch will be removed.

### Example Git workflow:

1. `git fetch --all`
1. `git checkout --branch <VAGOV-000-name> origin/develop`
1. `lando composer install`
1. `./scripts/sync-db.sh`
1. `./scripts/sync-files.sh` # (optional)
1. Running `lando tests` will build the frontend web and run all tests (php unit, behat, FE web) See [testing](testing.md) for additional details.

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


## Drupal SpecTool

* We use the [Drupal SpecTool](https://github.com/acquia/drupal-spec-tool) to keep track of config changes, and generate tests related to roles, content types, fields, menus views.
* If you are modifying configuration of roles, content types, fields, menus views, Go update the appropritate tab(s) in our version of the [SpecTool](https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit?usp=sharing).
* Once all the modifications are made to the appropriate tab(s) and cell(s) go to the [Behat](https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit#gid=624373408) tab and copy the cell with the tests that would have changed based on what you changed.
* Open the related file in [/tests/behat/drupal/drupal-spec-tool/](../tests/behat/drupal/drupal-spec-too/)
* Delete all the existing text in the file and paste in what you copied from the SpecTool.  (Do not format the output in any way. Disable any Behat beautifier plugins.)
* After updating config, run `lando behat --tags=spec` to run just the spec tool tests. Discrepancies between code and config will be reflected in test output
* If needed, run tests again, correcting and updating the spreadsheet, and exporting accordingly until tests and spreadsheet are in sync.
* Export config to code: `lando drush config:export` then commit test and config changes and make your Pull Request.   Your newly updated Behat tests will run along with the other tests and passing or failure will be indicated on your PR.   Please make sure they are passing locally before sending the PR for code review.


## Patching

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


[Table of Contents](../README.md)
