[![Build Status](https://travis-ci.org/acquia/headless-lightning.svg?branch=master)](https://travis-ci.org/acquia/headless-lightning)

# Headless Lightning
Headless Lightning is a more opinionated version of the [Lightning](https://github.com/acquia/lightning)
Drupal distribution intended for use as a backend for decoupled applications.

## Quick Start
To build the codebase:

    composer create-project acquia/lightning-project:dev-headless --no-interaction --stability dev

This will create a new directory, `lightning-project` which contains a `docroot`
folder. This is where you should point your web server.

Just like any Drupal project, you will need an environment in which Drupal can
be run and an available database. Once you have that setup, you can use Drush,
Drupal Console, or the web interface to install the site.

When it comes time to integrate with a frontend application, you will likely need to configure [Drupal CORS](https://www.drupal.org/node/2715637) to allow cross-origin requests and set up OAuth tokens.

There is no need to clone or fork this project unless you want to contribute
back. Use a scaffold project like Lightning Project, BLT, or Drupal Project to
build a codebase for actual use. See `composer create-project` command above.

## Contributing
Headless Lightning provides some Robo and Composer commands to aid in
development. To take advantage:

1. Clone this project.
2. Build working codebase (`composer install`).
3. Install dev environment (`lightning install 'mysql\://{DB_USER}:{DB_PASS}@127.0.0.1/{DB_NAME}' headless_lightning 'http://127.0.0.1:8080'` 
4. Make changes in `/docroot/profiles/headless_lightning`.
5. Once you're happy with changes, use `composer pull` to move your changes back
   into the top-level repository to be committed or submitted as a PR or patch.
   
Please file issues in our [GitHub Issue Queue](https://github.com/acquia/headless-lightning/issues).

## Goals
**Headless Lightning aims to provide a standard backend content repository that
allows for easy ingestion by decoupled applications.** It does so by building on
and configuring the basic tool set provided by the contrib modules selected and
implemented in [Lightning's Content API](https://github.com/acquia/lightning/tree/8.x-2.x/modules/lightning_features/lightning_api).

### Headless Lightning Goals
* Make the UI more intuitive for non-Drupalists and more logical for everyone
  that uses Drupal primarily as a content store.
* Have opinions about and examples of how an external application should
  authenticate against and consume the API.
* Not get in the way of developers, site builders, or content editors. 

## Features
### JSON Content
Presentation layer blanket that generally hides or redirects users from content
rendered by the Drupal application.

### Headless UI
Imposes UI opinions on the administrative backend, mainly to make it intuitive
to create and manage content without worrying about how Drupal will render it.

#### Headless UI Sub-components
Headless UI Sub-components consolidate links and add relevant descriptions to
common administrative tasks central to using Drupal as a decoupled backend:

* **Access UI**: Consolidates API configuration and access.
* **Content Model UI**: Consolidates configuring content types and related
  entities.

### Built on Drupal and Lightning
Headless Lightning is built on Drupal and extends the Lightning distribution.
That means you can create a sub-profile of Headless Lightning - just like
Headless Lightning is a sub-profile of Lightning itself. It also means that
applications built with Headless Lightning will get all of the features of and
updates to Drupal and Lightning along with update paths.

## Similar Projects
*From which we have borrowed heavily and for which we are very thankful.*

* [Contenta CMS](https://github.com/contentacms)  
  **Contenta makes your content happy**  

* [Reservior](https://github.com/acquia/reservoir)  
  **A back end for your front end**  
