The Behat Drush Endpoint is the remote component neede to work with the [Behat Drupal Driver](https://github.com/jhedstrom/DrupalDriver).

The Behat Drupal Driver contains three drivers:  *Blackbox*, *Direct Drupal API*, and *Drush*.  The Behat Drush Endpoint is only necessary when using the *Drush* driver.

**THIS PROJECT IS STILL UNDER DEVELOPMENT.**

At this point in time, the PR needed to enable this project, [Enhance the Drush driver to allow creation of nodes and taxonomy terms](https://github.com/jhedstrom/DrupalDriver/pull/56), has been committed to the drupal/drupal-driver project; however, it is not yet part of a stable release.

## Installation Instructions

If you are managing your Drupal site with Composer, ensure that your composer.json contains the following entries:
``` json
{
    "require-dev": {
        "drush-ops/behat-drush-endpoint": "*",
        "drupal/drupal-driver": "dev-master"
    },
}
```
If you are not using composer.json on the remote Drupal site, then copy the entire contents of this project to either **__ROOT__**/drush or **__ROOT__**/sites/all/drush, then `cd behat-drush-endpoint` and run `composer install`.  You must still ensure that the system running Behat is using the dev-master release of the drupal/drupal-driver project.
