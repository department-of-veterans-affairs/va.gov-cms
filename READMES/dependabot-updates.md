Dependabot automatically scans the `composer.json`, `composer.lock`, `package.json`, and `package-lock.json` files to make sure packages are up to date. This document describes the process for reviewing and merging dependabot updates. Dependabot functionality is described on the [Github documentation page](https://docs.github.com/en/code-security/supply-chain-security/keeping-your-dependencies-updated-automatically)

## Current methodology

Dependabot will now be only used to track updates to the content-build module within the VA. All other modules have been ignored in its tracking.

## Adding or removing modules

When adding or removing modules, make sure to do the same to your module's reference in ignore section of [the dependabot.yml file](../.github/dependabot.yml).
