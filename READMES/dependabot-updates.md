Dependabot automaticly scans the `composer.json`, `composer.lock`, `package.json`, and `package-lock.json` files to make sure packages are up to date.  This document describes the process for reviewing and merging dependabot updates.  Dependabot functionality is described on the [Github documentation page](https://docs.github.com/en/code-security/supply-chain-security/keeping-your-dependencies-updated-automatically)

## Determine the source
  
### va-gov/content-build

The package `va-gov/content-build` is the va.gov content build.  This PR can be merged if all tests pass.  No other work is needed.
  
### Packagist/NPM with release note

Updates from packagist and npm with release notes will have collapsed secions containing the details release notes and commits.

Example PR: https://github.com/department-of-veterans-affairs/va.gov-cms/pull/6069

![image](https://user-images.githubusercontent.com/121603/129742778-e08627e4-94bc-4ce2-bdff-1a8ba3eab31f.png)

Review the release notes and determine if manualy testing is required.  Most of the time if all tests pass then the PR can be merged but this is a case by case basis.  If you have any questions please reach out to your tech lead.

### Packagist/NPM without release notes

Most of the time the release notes will be automaticlly added.  In the cases where they are not, go to packagist/npm/github and add links to the release notes.  

Here is an example: https://github.com/department-of-veterans-affairs/va.gov-cms/pull/5665

![image](https://user-images.githubusercontent.com/121603/129743349-0facd0e5-8380-4b99-8092-16bd03fbfa4a.png)

To find the release notes, first start with the packagist/npm package which will link to the source code repository.  For the example above, phpmailer is found here: https://packagist.org/packages/phpmailer/phpmailer

### Drupal

Dependabot PRs created for Drupal packages will not have release notes or diff.  These can be created manually using the following pattern:

```
Release Notes: (one link to each of the releases between current and suggested)
- https://www.drupal.org/project/<project>/releases/<release>

Diff: https://git.drupalcode.org/project/<project>/-/compare/<current_release>...<suggested_release>

```

Example: https://github.com/department-of-veterans-affairs/va.gov-cms/pull/5651

Blazy module updating from version 8.x-2.2 to 8.x-2.4

```
Release Notes: 
* https://www.drupal.org/project/blazy/releases/8.x-2.4
* https://www.drupal.org/project/blazy/releases/8.x-2.3

Diff: https://git.drupalcode.org/project/blazy/-/compare/8.x-2.2...8.x-2.4?from_project_id=59405
```

![image](https://user-images.githubusercontent.com/121603/129744945-deb9d89c-9482-48a8-8c3c-4bcc1e8aa710.png)

Review the release notes and determine if manualy testing is required.  Most of the time if all tests pass then the PR can be merged but this is a case by case basis.  If you have any questions please reach out to your tech lead.

It's also useful to review the code diff to look for any API/method changes and see if we use any of the changed code.
  
