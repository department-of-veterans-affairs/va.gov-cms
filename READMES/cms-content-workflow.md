# CMS Content Workflow


## Alias Lockdown
Many of our content types are setup to lock the alias at whatever it was set
to when the node was changed to publish.  After that, any changes to title or
other alias pattern elements that make up the alias, will not alter the alias.
Which content types are affected by this lockdown are specified in
_va_gov_backend_get_locked_alias_bundles() in [va_gov_backend.module](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/docroot/modules/custom/va_gov_backend/va_gov_backend.module#L316)  See the function for the most up-to-date list of content types
affected.  The Alias can be updated on the node by anyone that has permission to
see the alias widget.

[Table of Contents](../README.md)
