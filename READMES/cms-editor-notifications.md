# CMS User Notification Systems

   1. CMS Helpdesk
     Used to notify an editor(s) directly via email.
   2. [Site Alert](#site-alert)
   3. CMS Notification System (non-existent at this time)


## Site ALert

Site alerts appear onscreen for ALL CMS users in real time.  They do not require
a page reload.  They run on a 15 second AJAX interval. This leverages the Drupal [Site Alert](https://www.drupal.org/project/site_alert) module.
Site alerts are managed [/admin/config/system/site-alerts](https://prod.cms.va.gov/admin/config/system/site-alerts)
The severity level determines their appearance
   * **Low** appears at the bottom of the screen and is informational and non-warning.
   * **Medium** appears at the bottom of the screen and is a warning.
   * **High** Covers the entire screen and is meant to keep ALL users from performing
      any actions.

  These can be added by humans but are also added and removed by our
  [Build Release Deploy System](devops/deploy-process.md).

[Table of Contents](../README.md)
