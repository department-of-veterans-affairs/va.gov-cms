# VA Gov Notifications

This module orchestrates the [messages stack](https://www.drupal.org/node/2180145) for email and Slack notifications.

- [6102 Details and design intent](https://github.com/department-of-veterans-affairs/va.gov-team/blob/master/platform/cms/product-outlines/VA-Directive-6102-Notifications.md)
- [How to send and see email?](#how-to-send-and-see-email)
- [How to add a new monthly outdated content email?](#how-to-add-a-new-monthly-outdated-content-email)
- [The queues](https://prod.cms.va.gov/admin/config/system/queues)
- [Cautions and Notes](#cautions-and-notes)


## Existing implementations
  - Slack notifications for specific changes to the VA Forms product.
  - Email notifications to helpdesk for specific changes to the facilities.
  - [Monthly email to VAMC facility editors](#vamc-facility-editor-email) to check their outdated content list.
  - [Monthly email to Vet Center facility editors](#vet-center-facility-editor-email) to refresh their content.


## VAMC facility editor email

An email is sent once per month triggered by a Jenkins (cron-like) ["every_month" tasks-periodic](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/tasks-periodic.yml#L92).

### Testing
This can be tested locally and on tugboat by running
`drush php-eval "print_r(\Drupal::service('va_gov_notifications.outdated_content')->queueOutdatedContentNotifications('vamc', 'vamc_outdated_content', ['<user id 1>','<user id 2']));"`

It is recommended when testing that you use at least 2 users, one who is assigned to a VAMC section, and one that is not.  Only one email should be queued.  Passing in You can validate what is in [the queue here](/admin/config/system/queues/jobs/vamc_outdated_content)
Calling it without the optional array of user ids will send it to all editors thate belong to that product who have outdated content.
## Vet Center facility editor email

An email is sent once per month triggered by a Jenkins job (cron-like) running ["every_month" in tasks-periodic](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/tasks-periodic.yml#L92).

### Testing
This can be tested locally and on tugboat by running
`drush php-eval "print_r(\Drupal::service('va_gov_notifications.outdated_content')->queueOutdatedContentNotifications('vet_center', 'vet_center_outdated_content', ['<user id 1>','<user id 2']));"`
Calling it without the optional array of user ids will send it to all editors thate belong to that product who have outdated content.
It is recommended when testing that you use at least 2 users, one who is assigned to a VAMC section, and one that is not.  Only one email should be queued.  You can validate what is in [the queue here](https://prod.cms.va.gov/admin/config/system/queues/jobs/vet_center_outdated_content)

## How to send and see email?
Once email notifications have been queued, The queue can be processed. To process the items in the queue, run `drush cron` Then use MailPit locally or the "captured mail" section in Tugboat to see the mail that was sent.

## How to add a new monthly outdated content email?
1. [Create the messages template](https://prod.cms.va.gov/admin/structure/message).
2. Set up the body and subject view modes for the message.  Tip: 'Partial 0' is the message text from the message template (not the twig template).  The message template text is "{{ content }}" on the twig template.
3. [Add a new queue](/admin/config/system/queues)
4. [Add a new mime mailer entry](/admin/config/system/mailsystem)
5. Export your config. 'drush cex'
6. Edit docroot/modules/custom/va_gov_notifications/src/Service/OutdatedContent.php to add any product specific details to the following methods:
   - getProductId()
   - getSubject()
   - getExcludedContentTypes()
7. Add a new appropriately named twig template to docroot/modules/custom/va_gov_notifications/templates/
8. Register your new template by adding it to va_gov_notifications_theme() in the .module file.
9. Add template name to '$types' in va_gov_notifications_mail_alter() in the .module file.
10. Edit the section taxonomy term that represents the product you are adding, Make sure it has the right product assigned to it.
11. Test with `drush php-eval "print_r(\Drupal::service('va_gov_notifications.outdated_content')->queueOutdatedContentNotifications('<product name>', '<template_name>', [<test user ids>]));"`
12. Add entry to 'every_month' task in [tasks-periodic.yml](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/tasks-periodic.yml#L92).

## Cautions and Notes
- On prod, mail can only be sent to va.gov email addresses.
- Locally mail gets sent to MailPit (type `ddev status` to get the address), On tugboat mail gets captured and can be seen from the tugboat instance dashboard.  Staging does not send mail.
- Message variable tokens can not be used in a link url because CKE editor will mangle tokens in href.
- H1 can not be used in the message content because the "Rich Text" filter does not allow it, so it has to go on the twig template.
- To test html on va.gov email (Outlook) while running locally, you can copy the html displayed in MailPit, paste it into gmail, then send it to your VA.gov email. Formatting is preserved.
