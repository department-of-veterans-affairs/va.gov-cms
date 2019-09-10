## WEB & CMS Integration

Recoupled Drupal. 

### VA.gov WEB

This Drupal site acts solely as a CMS and Content API. The actual website for va.gov is powered by the [vets-website](https://github.com/department-of-veterans-affairs/vets-website) repo.

The `WEB` project is built on Metalsmith. The project consumes the Drupal CMS GraphQL endpoint and generates static files for VA.gov.

### Using Composer to install WEB

The CMS codebase leverages a composer feature called "repositories" that let's us define 
our own "pseudo-package" inside `composer.json`:

```json
{
 "repositories": {
        "va-gov-web":   {
            "type": "package",
            "package": {
                "name": "va-gov/web",
                "version": "dev-VAGOV-2937-unity",
                "source": {
                    "url": "https://github.com/department-of-veterans-affairs/vets-website",
                    "type": "git",
                    "reference": "e76d8b6e1ae5fddc9c3f629b76c082e2d956ee8f"
                }
            }
        }
    }
}
```

This results in `vets-website` code being downloaded to the composer vendor directory, in our case `docroot/vendor/va-gov/web`.

This method allows us to pin the version we want to run down to the exact commit.
 
See `"reference": "e76d8b6e1ae5fddc9c3f629b76c082e2d956ee8f"` above.

This means tests can be run against a specific commit, ensuring the entire content editing workflow, including the WEB 
build process, is working correctly.

### Build WEB from CMS Locally

#### Change branch or SHA of WEB

CMS developers can now change the version of the WEB project they want to use by editing `composer.json` and changing 
the "reference" under "va-gov-web" repository:

```json
{
 "repositories": {
        "va-gov-web":   {
            "type": "package",
            "package": {
                "name": "va-gov/web",
                "version": "dev-VAGOV-2937-unity",
                "source": {
                    "url": "https://github.com/department-of-veterans-affairs/vets-website",
                    "type": "git",
 +                  "reference": "e76d8b6e1ae5fddc9c3f629b76c082e2d956ee8f"
 -                  "reference": "bf54229be12badf4078abab5ae156f30ce6908f9"
                }
            }
        }
    }
}
```
Then followup with a `composer update --lock` and `lando test` will build the FE with the new hash.

#### Build static content from local CMS

There is now a composer command to rebuild the WEB front-end static files from a locally running Drupal site:

```
composer va:web:build
```

This command will regenerate the HTML and CSS for the entire site, and will put it into `./docroot/static`. 

If using lando, you can load it using http://va-gov-cms.lndo.site/static.

NOTE: We are working on a method to load this content from a root url, like http://va-gov-web.lndo.site

### Build CMS PR Environment for WEB PR

If a WEB developer has an open PR (or just commits) and wants to get a PR environment with the CMS and the WEB code, they 
can open a PR in the `va.gov-cms` repository:
 
1. Determine the SHA of the WEB code you want to test.
1. Put that SHA into the "reference" field in `composer.json`, as described above.
1. Visit [Create Pull Request](https://github.com/department-of-veterans-affairs/va.gov-cms/compare?expand=1) page of
the CMS repo, and describe your intentions.
1. Wait for the Deployment notification to give you a link to your new site. 

  - You will see the text "va-cms-bot requested a deployment to pr548.ci.cms.va.gov - devshop-deploy just now Pending"
  - Your site will have a URL with the pattern: http://pr548.ci.cms.va.gov. To see the WEB version of the site, add 
  ".web": http://pr548.web,ci.cms.va.gov
 

[Table of Contents](../README.md)
