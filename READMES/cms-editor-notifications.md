# CMS User Notification Systems

   1. CMS Helpdesk
     Used to notify an editor(s) directly via email.
   2. [Site Alert](#site-alert)
   3. CMS Notification System (non-existent at this time)


## Sitewide Alert

Sitewide Alerts (formerly Site Alerts) appear onscreen for ALL CMS users in real time.  They do not require
a page reload.  They are refreshed on a 15 second AJAX interval. This leverages the Drupal [Sitewide Alert](https://www.drupal.org/project/sitewide_alert) module.

Sitewide alerts are managed in two places:

- [/admin/config/sitewide_alerts](https://prod.cms.va.gov/admin/config/sitewide_alerts) for global settings
- [/admin/content/sitewide_alert](https://prod.cms.va.gov/admin/content/sitewide_alert) for individual alerts

These can be added by humans but are also added and removed by our [Build-Release-Deploy System](devops/deploy-process.md).

----

[Table of Contents](../README.md)
