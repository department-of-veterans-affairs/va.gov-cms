# VA.gov "vets-website"
## Self-Contained Packagist.org

This folder containes the files needed to load the `va-gov/web` package using 
composer.

It uses the "satis" tool to generate [packages.json](packages.json) using the 
GitHub Repository.

Once generated, it is committed and pushed to GitHub. 

Then, the github-hosted packages.json file will be read by composer to update
composer.lock.

### Usage:

See Satis documentation in Composer docs:

https://getcomposer.org/doc/articles/handling-private-packages-with-satis.md#setup

Satis is included in VA.gov-cms composer.json "require-dev" section.

Run the following command in the repo root to regenerate the packages.json file:

    ```
    bin/satis build packagist/satis.json packagist
    ```
    
This will generate the following files:

- `packagist/`
  - `index.html`: a UI for browsing the available packages and versions.
  - `packages.json`: A composer-compatible list of packages and versions.
  - `include/`
    - `all$HASH.json`: An file included by packages.json that has metadata about that specific HASH.
    
    
    
 