# CMS Analytics

## Tools

### Google Analytics / Google Tag Manager

The CMS uses Google Tag Manager (GTM) container to send data to Google Analytics (GA) VA.gov CMS Backend property.

The data in GA VA.gov CMS Backend property is available under three views:

* CMS Lower Environments
* CMS Prod
* Unfiltered Data - All Website Data

The GTM container is rendered on each page via custom Drupal library `gtm_tag_push`.

The CMS sends several custom dimensions related to currently viewed page as well as events related to user browsing patterns.

Implementation can be found in `docroot/modules/custom/va_gov_backend/js/gtm_tag_push.js`.

#### How to send new GA dimensions

Start by filling out [Analytics Implementation and QA template](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=joanneesteban%2C+bsmartin-ep%2C+jonwehausen%2C+bmcgrady-ep&labels=analytics-insights%2C+analytics-request%2C+collaboration-cycle%2C+collab-cycle-review&template=analytics-implementation-and-qa-request-template.md&title=Analytics+Implementation+or+QA+Support+for+%5BTeam+Name+-+Feature+Name%5D).

The process of adding a new dimension includes connecting with the VSP Analytics & Insights team to add it in GA/GTM and adding backend code that sends the dimension.

#### How to request access to GA for new users

Fill out [Analytics request template](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new?assignees=joanneesteban%2C+bsmartin-ep%2C+jonwehausen&labels=analytics-insights%2C+analytics-request&template=analytics-request-google-analytics-access.md&title=Request+access+to+Google+Analytics).
