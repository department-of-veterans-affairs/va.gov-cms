# Sentry

Sentry is used to track errors fom Drupal.  

URL: http://sentry.vfs.va.gov/
* Login using your Github Login
* Add your self to the [#cms](http://sentry.vfs.va.gov/settings/vsp/teams/cms/members/) team.
* Bookmark [All unresolved issues](http://sentry.vfs.va.gov/organizations/vsp/issues/?project=14&query=is%3Aunresolved)

Unless errors are in the Known Errors list below, an issue should be created in Github with a link to the sentry error.  If the error level is `critical`, `alert`, or `emergency` then contact the `@cms-engineers-group` `@cms-devops-engineers` teams in Slack.

## Testing

### How to generate a test error message

On a BRD server, you may run this command to generate a test message:

```bash
$ sudo su - cms
$ drush raven:captureMessage --level=error "TEST MESSAGE"
...
```

----

[Table of Contents](../README.md)
