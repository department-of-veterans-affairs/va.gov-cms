# Drush

In local development environments, these commands should normally be run with a `composer` or `ddev` prefix, e.g. `composer drush sqlq show tables"`.  For other usage notes regarding local development environments, see [Local](./local.md).

## Custom Drush Commands

### Site Status

See [SiteStatusCommands.php](../docroot/modules/custom/va_gov_build_trigger/src/Commands/SiteStatusCommands.php).

- `va-gov:disable-deploy-mode` -- Sets the Deploy Mode flag to FALSE. It is not normally necessary to perform this operation manually.
- `va-gov:enable-deploy-mode` -- Sets the Deploy Mode flag to TRUE. It is not normally necessary to perform this operation manually.
- `va-gov:get-deploy-mode` -- Indicates whether the CMS is currently in Deploy Mode, which is a precautionary measure used to prevent content changes while content is being deployed.

### Content Release

See [ContentReleaseCommands.php](../docroot/modules/custom/va_gov_build_trigger/src/Commands/ContentReleaseCommands.php).

- `va-gov:content-release:advance-state` -- Advance the state like an external system would do through HTTP.
- `va-gov:content-release:check-scheduled` -- Make sure builds are going out at least hourly during business hours.
- `va-gov:content-release:check-stale` -- If the state is stale, reset the state.
- `va-gov:content-release:get-frontend-version` -- Get the frontend version that was requested by the user.
- `va-gov:content-release:get-state` -- Get the current release state.
- `va-gov:content-release:is-continuous-release-enabled` -- Check continuous release state.
- `va-gov:content-release:request-frontend-build` -- Request a frontend build (but do not initiate it).
- `va-gov:content-release:reset-frontend-version` -- Reset the content release frontend version.
- `va-gov:content-release:reset-state` -- Reset the content release state.
- `va-gov:content-release:toggle-continuous` -- Toggle continuous release.

### Metrics

See [MetricsCommands.php](../docroot/modules/custom/va_gov_backend/src/Commands/MetricsCommands.php).

- `va-gov:metrics:send` -- Send various application metrics to DataDog.


### Outdated Content
See
[CMS Notification System](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/main/docroot/modules/custom/va_gov_notifications/README.md) for commands to test or
execute the various outdated content sends.
