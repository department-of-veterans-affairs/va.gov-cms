Sentry is used to track errors fom Drupal.  

URL: http://sentry.vfs.va.gov/
* Login using your Github Login
* Add your self to the [#cms](http://sentry.vfs.va.gov/settings/vsp/teams/cms/members/) team.
* Bookmark [All unresovled issues](http://sentry.vfs.va.gov/organizations/vsp/issues/?project=14&query=is%3Aunresolved)

Unless errors are in the Known Errors list below, an issue should be created in Github with a link to the sentry error.  If the error level is `critical`, `alert`, or `emergency` then contact the `@cms-engineers-group` `@cms-devops-engineers` teams in Slack.

## Known errors:
* Facility/Teamsites related errors

### `Could not process `{teamsite facility url}`

Example: http://sentry.vfs.va.gov/organizations/vsp/issues/44491/?project=14&query=is%3Aignored
`because it returned status 301 : Moved Permanently`
Possible issue:
* Facility is fully managed in Drupal and the URL shoudl be removed.  Contact the `@cms-product-support-team` in Slack for verification and next steps.
* If the facilty is still managed in Team Sites then there might be a misconfiguration. See [this sheet](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/upstream-dependencies.md) for contact information.

### `Exception va_node_health_care_local_facility_status migration failed.`

This error comes from the [periodic job in Jenkins](http://jenkins.vfs.va.gov/job/cms/job/cms-periodic-prod/).
This is being monitored in DataDog and Pager Duty and can be ignored.  If Pager Duty is triggered, then a notification will be triggered in Slack in the #cms-notifications channel.
