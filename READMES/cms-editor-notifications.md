# CMS User Notification Systems

   1. CMS Helpdesk
     Used to notify an editor(s) directly via email.
   2. [Sitewide Alert](#sitewide-alert)
   3. [CMS Notification System](https://github.com/department-of-veterans-affairs/va.gov-cms/tree/main/docroot/modules/custom/va_gov_notifications/README.md)


## Sitewide Alert

Sitewide Alerts (formerly Site Alerts) appear onscreen for ALL CMS users in real
time.  They do not require a page reload.  They are refreshed on a 15 second
AJAX interval. This leverages the Drupal [Sitewide Alert](https://www.drupal.org/project/sitewide_alert) module.  These only show in the CMS.  They do not ever appear on VA.gov.

Sitewide alerts are managed in two places:

- [/admin/config/sitewide_alerts](https://prod.cms.va.gov/admin/config/sitewide_alerts) for global settings
- [/admin/content/sitewide_alert](https://prod.cms.va.gov/admin/content/sitewide_alert) for individual alerts

These can be added/edited by humans but are also added and removed by our [Build-Release-Deploy System](devops/deploy-process.md).

CMS Helpdesk has guidance on when and how to use them.

----

[Table of Contents](../README.md)
