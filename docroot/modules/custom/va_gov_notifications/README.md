# VA Notifications

## VA.gov Notifications
This module sends email alerts to users who have been added to node workflow participants forms.
Alerts are only sent if a workflow state change has occurred, and this is triggered by node save.

### Workflow Participants form
An alter has been added to remove the `Editors` fieldset - it is redundant,
as workflow participants module doesn't pull from va roles to autopopulate fields,
and doesn't assign any extra perms to users entered in `Editors` or `Reviewers` fields.
This form can be found on these paths: `node/{NID}/workflow-participants`
Once a user is added to this form, any time a workflow state has changed, an email
will be sent to them on node save.
Once a user is removed from the workflow participants form, they will no longer receive emails when workflow state changes.

### Email data
```Content title: Make an appointment
Link: http://va.gov/pittsburgh-health-care/make-an-appointment
Status Change From: review
Status Change To: draft
Log Message: This is a thing.
Change made by: some.user
Time changed: 2019-08-04 11:59:04
```

### Error Reporting
Email send errors will be reported to Recent Log Messages (`/admin/reports/dblog`)
