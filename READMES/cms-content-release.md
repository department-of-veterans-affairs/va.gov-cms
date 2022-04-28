# CMS Content Release

Content releases are initiated in one of two ways:
- Automatically, via [this Github Action Workflow](https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml)
- Manually, via the ["Release content"](https://prod.cms.va.gov/admin/content/deploy) page in the CMS.

## Automatic

### Timed

The [Content Release Github Action Workflow](https://github.com/department-of-veterans-affairs/content-build/blob/master/.github/workflows/content-release.yml) handles automatic content deploys.

It is currently [set](https://github.com/department-of-veterans-affairs/content-build/blob/master/.github/workflows/content-release.yml#L16) to execute weekdays at 9AM, 10AM, 11AM, 12PM, 1PM, 2PM, 3PM, 4PM, and 5PM.

### Triggered

The content release is also triggered based upon content updates in Drupal.  The logic that controls when a build is triggered is at [`Drupal\va_gov_build_trigger\Service::triggerFrontendBuildFromContentSave()`](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/98f4666d7b6aabf984f679fdaec4088c35e08488/docroot/modules/custom/va_gov_build_trigger/src/Service/BuildFrontend.php#L162).

## Manual

Manual content releases are initiated from the "Release content" page in the CMS.

This page is constructed by the [`va_gov_build_trigger`](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/main/docroot/modules/custom/va_gov_build_trigger) module. The page will differ slightly in presentation and significantly in the details of its operation depending on which environment hosts it.

### Build-Release-Deploy (Production)

The "Release content" page on the BRD production environment invokes the [same Github Action Workflow]https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml) as the automatic deploys. Accordingly the content build output should be identical.

The Jenkins job configuration is stored in Drupal `settings.php`. Here are the settings for [production](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/docroot/sites/default/settings/settings.prod.php#L46). Settings for other environments can be found in the `*.settings.php` [files](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/docroot/sites/default/settings). The setting keys are:
```php
$settings['va_gov_frontend_build_type'] = 'brd';
$settings['github_actions_deploy_env'] = 'prod';
```

### Build-Release-Deploy (Staging)

The "Release content" page on the BRD staging environment is currently disabled because the content release GHA workflow does not support any environment other than production.


**To manually run the Content Release job:**
1. Go to https://github.com/department-of-veterans-affairs/content-build/actions/workflows/content-release.yml.
2. Click "Run Workflow"

![image](https://user-images.githubusercontent.com/121603/141811069-c7bf44ab-d8d9-4da3-96d0-860f234eaa5b.png)

[_**More documentation for the content build can be found here**_](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/platform/cms/accelerated_publishing/content-build).


### Tugboat and Local Development

The Tugboat and local development (Lando, etc) versions of the Release content page do not trigger a Github Actions workflow.  Instead, they check out the latest version (or a specified branch or release) of the [frontend](https://github.com/department-of-veterans-affairs/content-build/), build it, and then perform a content release.

For more information on creating or releasing content from a preview environment, see [Environments](./environments.md).

[Table of Contents](../README.md)
