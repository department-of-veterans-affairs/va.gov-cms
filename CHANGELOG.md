# Changelog
All notable changes to this project will be documented in this file.

The format is based on [git-release-notes](https://github.com/ariatemplates/git-release-notes).


## [Unreleased]

* __Nothing unreleased__

## 2019-08-20.1 Sprint 20 release 3. tag  20190820.1

*  __VAGOV-1555 Add preprod cert for staging IAM SSO metadata.__
    2019-08-16 12:53:04 -0700 - (Elijah Lynn) [3facd609]

## 2019-08-19.1 Sprint 20 release 2.

These two items were needed urgently on prod so they were cherry-picked on top of the previous release.
*  __VAGOV-5510: Update linkit url filters.__
    2019-08-19 13:27:13 -0400 - (Steve Wirt) [19a45699] Cherry-picked.
*  __VAGOV-5586 Add homepage label field.__
    2019-08-19 13:26:34 -0400 - (Steve Wirt) [51c2e245] Cherry-picked.


## 2019-08-13.1 Sprint 20 release 1.

* __VAGOV-000: 2019-8-13.1 Sprint 20 end-of-sprint release 1.__

    [Steve Wirt](mailto:swirtMiles@138230.no-reply.drupal.org) - Tue, 13 Aug 2019 17:01:11 -0400

    EAD -&gt; refs/heads/master


* __Merge pull request #503 from beeyayjay/VAGOV-5287-migrate-from-branch__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 13 Aug 2019 16:37:48 -0400

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-5287: migrate from PR

* __Merge pull request #507 from ElijahLynn/VAGOV-3930-update-sso-for-preprod__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 13 Aug 2019 14:57:02 -0400


    VAGOV-3930 Update SSOi integration to use preprod endpoint.

* __Merge pull request #502 from ethanteague/VAGOV-3202_3978-linkit-implementation-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 13 Aug 2019 13:34:21 -0400


    VAGOV-3202 | VAGOV-3978 linkit implementation

* __Merge branch &#39;develop&#39; into VAGOV-3202_3978-linkit-implementation-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 13 Aug 2019 13:25:01 -0400




* __Merge pull request #505 from beeyayjay/VAGOV-5070-remaining-hub-migrations__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 13 Aug 2019 11:05:15 -0400


    VAGOV-5070 remaining hub migrations

* __Merge pull request #491 from kevwalsh/5111-workbench__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Thu, 8 Aug 2019 12:13:45 -0400

    efs/remotes/beeyayjay/develop
    VAGOV-5111: Enable workbench access for office content type.

* __Merge branch &#39;develop&#39; into 5111-workbench__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 7 Aug 2019 16:18:27 -0700




* __Merge pull request #496 from ElijahLynn/VAGOV-5292-ci-migration__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 7 Aug 2019 15:31:50 -0700


    VAGOV-5292 CI Migration

* __Merge remote-tracking branch &#39;origin/develop&#39; into VAGOV-5292-ci-migration__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 7 Aug 2019 15:10:39 -0700


    To fix the conflic in composer.lock I ran:

    `git checkout HEAD -- composer.json composer.lock`
    `composer require drupal/views_bulk_edit`

     Conflicts:
    composer.lock


* __Merge pull request #500 from kevwalsh/5357-views-bulk-edit__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Wed, 7 Aug 2019 17:07:30 -0400


    VAGOV-5357: Configure Views bulk edit for media and nodes.

* __Merge branch &#39;develop&#39; into VAGOV-5292-ci-migration__

    [Jon Pugh](mailto:jon@thinkdrop.net) - Tue, 6 Aug 2019 19:44:58 -0400




* __Merge branch &#39;VAGOV-5292-ci-migration&#39; of github.com:ElijahLynn/va.gov-cms into VAGOV-5292-ci-migration__

    [Jon Pugh](mailto:jon@thinkdrop.net) - Tue, 6 Aug 2019 18:28:24 -0400




* __Merge pull request #499 from department-of-veterans-affairs/VAGOV-4975-home-page-hub-queue__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 6 Aug 2019 17:31:18 -0400


    VAGOV-4975: Add entityqueue config for home page hub list

* __Merge branch &#39;develop&#39; into VAGOV-4975-home-page-hub-queue__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 6 Aug 2019 16:02:31 -0400

    efs/remotes/upstream/VAGOV-4975-home-page-hub-queue


* __Merge branch &#39;develop&#39; of https://github.com/department-of-veterans-affairs/va.gov-cms into VAGOV-5292-ci-migration__

    [Jon Pugh](mailto:jon@thinkdrop.net) - Tue, 6 Aug 2019 15:29:22 -0400




* __Merge pull request #492 from kevwalsh/4863-dismissable-alerts__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 6 Aug 2019 14:42:43 -0400


    VAGOV-4863: Dismissable alerts.

* __Merge branch &#39;develop&#39; into 4863-dismissable-alerts__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 6 Aug 2019 11:51:00 -0400




* __Merge pull request #495 from ElijahLynn/VAGOV-5323__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Tue, 6 Aug 2019 11:39:43 -0400


    VAGOV-5323 - Fix PeformanceLoginTest failure, decrease threshold from 5 &gt; 2.

* __Merge branch &#39;develop&#39; into VAGOV-5323__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 22:29:44 -0400




* __Merge pull request #497 from ethanteague/VAGOV-1955-workflow-assign-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 17:08:49 -0400


    VAGOV-1955 - Workflow Participant notifications

* __Merge branch &#39;develop&#39; into VAGOV-1955-workflow-assign-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 15:46:43 -0400




* __Merge pull request #472 from kevwalsh/4629-file-management-poc__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 15:02:36 -0400


    VAGOV-4629: Media governance updates.

* __Merge branch &#39;develop&#39; into 4629-file-management-poc__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 14:26:23 -0400




* __Merge pull request #498 from department-of-veterans-affairs/VAGOV-5346-remove-unused-js-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Mon, 5 Aug 2019 14:13:01 -0400


    VAGOV-5346: Removing artifacts referencing removed file.

* __Merge pull request #482 from kevwalsh/4622-health-service-options__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 15:42:35 -0400

    efs/remotes/myfork/develop, refs/remotes/myfork/HEAD
    VAGOV-4622: Force health service taxonomy references to use child terms.

* __Merge branch &#39;develop&#39; into 4622-health-service-options__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 14:44:16 -0400




* __Merge pull request #479 from department-of-veterans-affairs/VAGOV-4972-landing-page-teaser-field__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 14:43:37 -0400


    VAGOV-4972: Add field_teaser_text to landing page content type

* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 14:01:33 -0400

    efs/remotes/upstream/VAGOV-4972-landing-page-teaser-field


* __Merge pull request #487 from kevwalsh/5038-allow-lists__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 14:01:15 -0400


    VAGOV-5038: Allow &lt;ol&gt; and &lt;ul&gt; in plain text.

* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 13:14:24 -0400




* __Merge branch &#39;develop&#39; into 5038-allow-lists__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 13:10:48 -0400




* __Merge pull request #478 from schiavo/VAGOV-2833__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 13:08:13 -0400


    VAGOV-2833 Update Behat tests from Drupal Spec Tool.

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 11:57:09 -0400




* __Merge pull request #480 from schiavo/VAGOV-5229__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 11:37:06 -0400


    VAGOV-5229 Update nightwatch tests with updated site name and update r…

* __Merge branch &#39;develop&#39; into 4622-health-service-options__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 11:15:01 -0400




* __Merge branch &#39;develop&#39; into VAGOV-5229__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 10:55:29 -0400




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 10:53:19 -0400




* __Merge branch &#39;develop&#39; into 5038-allow-lists__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 10:52:01 -0400




* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Fri, 2 Aug 2019 10:51:02 -0400




* __Merge pull request #489 from beeyayjay/VAGOV-5038-migration-anomaly-fix__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Thu, 1 Aug 2019 22:46:15 -0400


    Vagov 5038 migration anomaly fix

* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Thu, 1 Aug 2019 21:26:03 +0200




* __Merge pull request #490 from ethanteague/VAGOV-5237-drupal-outreach-menu-et__

    [Steve Wirt](mailto:swirtSJW@users.noreply.github.com) - Thu, 1 Aug 2019 15:23:15 -0400


    VAGOV-5237: Changing outreach menu name to sentence case.

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:16:28 -0700




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:16:25 -0700




* __Merge branch &#39;develop&#39; into VAGOV-5229__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:16:21 -0700




* __Merge branch &#39;develop&#39; into 4622-health-service-options__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:16:18 -0700




* __Merge branch &#39;develop&#39; into 5038-allow-lists__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:16:14 -0700




* __Merge pull request #488 from ElijahLynn/VAGOV-3930-uninstall-simplesaml-on-test-envs__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 19:15:48 -0700


    VAGOV-3930 Uninstall simplesaml on test envs

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 16:09:17 -0700




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 16:09:11 -0700




* __Merge branch &#39;develop&#39; into 5038-allow-lists__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 16:07:33 -0700




* __Merge branch &#39;develop&#39; into VAGOV-5229__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 16:06:51 -0700




* __Merge branch &#39;develop&#39; into 4622-health-service-options__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 16:06:43 -0700




* __Merge pull request #486 from andyhawks/feature/VAGOV-000-SSOI-SQA-metadata-updates-ah__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 31 Jul 2019 14:59:42 -0700


    VAGOV-000: SSOI SQA IdP Metadata updates.

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 31 Jul 2019 09:43:38 -0600




* __Merge branch &#39;develop&#39; into 4622-health-service-options__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Wed, 31 Jul 2019 16:38:37 +0200




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Wed, 31 Jul 2019 16:16:35 +0200




* __Merge branch &#39;VAGOV-5229&#39; of https://github.com/schiavo/va.gov-cms into VAGOV-5229__

    [Daniel Schiavone](mailto:daniel@snakehill.net) - Wed, 31 Jul 2019 08:41:45 -0400




* __VAGOV-4972 empty commit__

    [Jonathan Bourland](mailto:jonathanbourland@hotmail.com) - Tue, 30 Jul 2019 17:39:53 -0400




* __Merge branch &#39;develop&#39; into VAGOV-5229__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 15:09:01 -0600




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 15:08:56 -0600




* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 15:08:53 -0600




* __Merge branch &#39;develop&#39; into VAGOV-5229__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:40:54 -0600




* __Merge branch &#39;develop&#39; into VAGOV-4972-landing-page-teaser-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:40:45 -0600




* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:40:38 -0600






## 2019-07-31.1 Sprint 19 final release 4.


* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Wed, 31 Jul 2019 10:44:13 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190731.1, refs/remotes/upstream/master, refs/remotes/origin/master
    VAGOV-000: 2019-07-31.1 Sprint 19 final release 4.

* __Merge pull request #483 from ElijahLynn/VAGOV-3930-simplesaml__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 30 Jul 2019 20:31:49 -0700

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3930 Fix simplesamlphp sq3 database path to work on new hosting platform.

* __Merge pull request #401 from andyhawks/VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 15:08:22 -0600

    VAGOV-3930: SimpleSAMLPHP Configuration.

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:08:45 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 13:20:07 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:51:34 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:05:37 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Thu, 25 Jul 2019 16:09:36 -0700

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 25 Jul 2019 14:58:40 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 19 Jul 2019 11:42:38 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:37:48 -0600

* __Merge remote-tracking branch &#39;elijahlynn/VAGOV-1555-https&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Fri, 28 Jun 2019 09:50:48 -0700

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 24 Jun 2019 10:31:47 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 18:02:39 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 16:01:12 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:07:34 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 19 Jun 2019 10:08:53 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3930-new-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 18 Jun 2019 09:44:39 -0600

* __Merge branch &#39;develop&#39; into VAGOV-1555-https__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Fri, 7 Jun 2019 18:01:37 -0700

* __Merge branch &#39;develop&#39; into VAGOV-1555-https__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 28 May 2019 11:09:44 -0600


## 2019-07-30.1 Sprint 19 final release 3.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:03:56 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190730.3, refs/remotes/upstream/master
    VAGOV-000: 2019-07-30.3 Sprint 19 final release 3.

* __Merge pull request #481 from ElijahLynn/VAGOV-5135-fix-vfs-build-trigger__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:03:27 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-5135 Update build trigger to not use SOCKS proxy anymore. (update)

* __Merge branch &#39;develop&#39; into VAGOV-5135-fix-vfs-build-trigger__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 14:02:29 -0600

* __Merge branch &#39;develop&#39; into VAGOV-5135-fix-vfs-build-trigger__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 30 Jul 2019 13:00:44 -0700


* __Merge pull request #469 from kevwalsh/4967-toc-help-text__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 13:57:51 -0600

    VAGOV-4967: Improve TOC help text.

* __Merge pull request #471 from ethanteague/VAGOV-000-add-missed-profile-file-download-field-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 13:12:32 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-000: Adding file download field to staff profile content type.

* __Merge pull request #426 from kevwalsh/000-top-tasks-menu__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 30 Jul 2019 14:16:13 -0400

    VAGOV-4870: Top tasks menu for homepage.

* __Merge pull request #475 from ElijahLynn/VAGOV-5135-fix-vfs-build-trigger__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 30 Jul 2019 11:11:43 -0700

    VAGOV-5135 Remove SOCKS proxy, not needed anymore

* __Merge pull request #473 from department-of-veterans-affairs/VAGOV-4970-add-entityqueue__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 30 Jul 2019 13:55:52 -0400

    VAGOV-4970: Add Entityqueue Module

* __Merge branch &#39;develop&#39; into 000-top-tasks-menu__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 30 Jul 2019 13:43:31 -0400

* __Merge branch &#39;develop&#39; into VAGOV-5135-fix-vfs-build-trigger__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 11:29:36 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4970-add-entityqueue__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 11:27:50 -0600

    efs/remotes/upstream/VAGOV-4970-add-entityqueue

* __Merge pull request #468 from kevwalsh/3311-sections-home__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 11:24:06 -0600

    VAGOV-3311: Replace moderation dashboard with new sections listing.

* __Merge branch &#39;develop&#39; into 000-top-tasks-menu__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 30 Jul 2019 13:01:26 -0400

* __Merge branch &#39;develop&#39; into 3311-sections-home__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:49:08 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4970-add-entityqueue__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:46:14 -0600

* __Merge pull request #474 from ElijahLynn/VAGOV-5135-merge-brd-vsp-migration-code-develop__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 30 Jul 2019 09:45:09 -0700

    VAGOV-5135 Merge migration code into develop

* __Merge branch &#39;develop&#39; into 3311-sections-home__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:42:27 -0600

* __Merge pull request #453 from kevwalsh/5013-inline-images-and-files__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Jul 2019 10:42:10 -0600

    VAGOV-5013: Inline images and files.

* __Merge branch &#39;master&#39; into VAGOV-5135-merge-brd-vsp-migration-code-develop__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 30 Jul 2019 09:11:50 -0700

* __Merge pull request #466 from department-of-veterans-affairs/brd-migration__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Mon, 29 Jul 2019 13:47:07 -0700

    [DO NOT MERGE] Update settings for BRD migration.

* __Merge branch &#39;develop&#39; into 3311-sections-home__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Mon, 29 Jul 2019 14:16:13 +0200

* __Merge branch &#39;develop&#39; into 5013-inline-images-and-files__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Mon, 29 Jul 2019 14:15:19 +0200

* __Merge pull request #470 from ethanteague/VAGOV-000-remove-commented-code-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 26 Jul 2019 16:37:21 -0600

    VAGOV-000: Removing commented out code to pass fortify scan.

* __Merge branch &#39;develop&#39; into VAGOV-000-remove-commented-code-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 26 Jul 2019 14:05:24 -0600

* __Merge branch &#39;develop&#39; into 3311-sections-home__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 26 Jul 2019 14:05:17 -0600

* __Merge pull request #463 from department-of-veterans-affairs/vagov-4546__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 26 Jul 2019 14:04:35 -0600

    VAGOV-4546: add health service api id field to health service taxonomy

* __Merge branch &#39;develop&#39; into vagov-4546__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 25 Jul 2019 15:09:34 -0600

    efs/remotes/upstream/vagov-4546

* __Merge pull request #458 from kevwalsh/5212-alert-ax-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 25 Jul 2019 15:09:07 -0600

    5212 alert ax improvements

* __Merge branch &#39;develop&#39; into 5212-alert-ax-improvements__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Thu, 25 Jul 2019 12:45:16 +0200

* __Merge branch &#39;develop&#39; into vagov-4546__

    [ahay-agile6](mailto:aurora.hampton@agile6.com) - Tue, 23 Jul 2019 13:10:05 -0700

* __Merge pull request #460 from beeyayjay/VAGOV-4638-disability-migration-fixes__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 23 Jul 2019 16:08:46 -0400

    VAGOV-4638: disability migration fixes

* __Merge branch &#39;develop&#39; into VAGOV-4638-disability-migration-fixes__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 23 Jul 2019 15:43:02 -0400

* __Merge branch &#39;develop&#39; into VAGOV-4638-disability-migration-fixes__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 11:03:57 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4638-disability-migration-fixes__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 10:16:29 -0600

* __Merge branch &#39;develop&#39; into 5212-alert-ax-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 10:16:23 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4638-disability-migration-fixes__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 09:19:09 -0600

* __Merge branch &#39;develop&#39; into 5212-alert-ax-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 09:19:03 -0600

* __Merge branch &#39;develop&#39; into 5212-alert-ax-improvements__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Sun, 21 Jul 2019 14:15:18 +0200

* __Merge branch &#39;develop&#39; into 000-top-tasks-menu__

    [Jon Pugh](mailto:jon@thinkdrop.net) - Tue, 16 Jul 2019 13:26:17 -0400

* __Merge branch &#39;develop&#39; into 000-top-tasks-menu__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 16:14:15 -0600

* __Merge branch &#39;develop&#39; into 000-top-tasks-menu__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 8 Jul 2019 14:25:14 -0600


## 2019-07-23.1 Sprint 19 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 13:22:58 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190723.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-23 Sprint 19 mid-sprint release 1.

* __Merge pull request #461 from department-of-veterans-affairs/VAGOV-4852-operating-status-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 13:08:32 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4852: Adding operation status to facility type.

* __Merge branch &#39;develop&#39; into VAGOV-4852-operating-status-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 11:03:54 -0600

    efs/remotes/upstream/VAGOV-4852-operating-status-et

* __Merge pull request #459 from ethanteague/VAGOV-4351-staff-fields__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 11:03:27 -0600

    VAGOV-4351: Updating profile alias pattern.

* __Merge branch &#39;develop&#39; into VAGOV-4351-staff-fields__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 10:16:26 -0600

* __Merge pull request #456 from beeyayjay/VAGOV-5066-update-hub-icons__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 10:16:06 -0600

    VAGOV-5066 update hub icons

* __Merge branch &#39;develop&#39; into VAGOV-4852-operating-status-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 09:19:11 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4351-staff-fields__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 09:19:05 -0600

* __Merge branch &#39;develop&#39; into VAGOV-5066-update-hub-icons__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Jul 2019 09:18:39 -0600

* __Merge pull request #455 from department-of-veterans-affairs/VAGOV-4623-featured-facilities-content__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 23 Jul 2019 10:50:38 -0400

    VAGOV-4623: Featured content functionality

* __Merge pull request #454 from kevwalsh/4371-rename-press-release__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 19 Jul 2019 09:59:56 -0600

    VAGOV-4371: Rename press release and health service node types.

* __Merge pull request #452 from ElijahLynn/VAGOV-5047-fix-failed-deploys__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Thu, 18 Jul 2019 20:41:20 -0700

    VAGOV-5047 Use 0.3.10 of j2cli `j2` due to upstream bug.

* __Merge pull request #450 from beeyayjay/VAGOV-4767-link-teasers__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 18 Jul 2019 08:50:38 -0600

    VAGOV-4899: Migrate linkslist items with html in summary.

* __Merge pull request #447 from ethanteague/VAGOV-4965-update-office-config__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 17 Jul 2019 09:56:20 -0600

    VAGOV-4965: Updating office config.


## 2019-07-16.1 Sprint 18 final sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 16 Jul 2019 13:51:49 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190716.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-16.1 Sprint 18 final release 1.

* __Merge pull request #445 from kevwalsh/4625-featured-on-health-services__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 16 Jul 2019 10:13:24 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4625: Add a featured content block for health services page.

* __Merge branch &#39;develop&#39; into 4625-featured-on-health-services__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 16 Jul 2019 09:34:06 -0600

* __Merge pull request #444 from ethanteague/VAGOV-000-fix-events-pathauto-pattern__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 16 Jul 2019 09:33:47 -0600

    VAGOV-000: Updating events path to include events landing page.

* __Merge branch &#39;develop&#39; into 4625-featured-on-health-services__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Jul 2019 16:24:49 -0600

* __Merge branch &#39;develop&#39; into VAGOV-000-fix-events-pathauto-pattern__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Jul 2019 16:24:42 -0600

* __Merge pull request #441 from kevwalsh/4005-toc-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Jul 2019 16:24:25 -0600

    VAGOV-4005: Table of contents boolean for detail pages.

* __Merge pull request #443 from beeyayjay/VAGOV-000-hub-icon-migration-fix__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Jul 2019 13:20:09 -0600

    VAGOV-000 hub icon migration fix


## 2019-07-12.1 Sprint 18 mid-sprint release 3.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Fri, 12 Jul 2019 16:23:23 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190712.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-12.1 Sprint 18 mid-sprint release 3.

* __Merge pull request #438 from ElijahLynn/VAGOV-4891-lando-drush-commands-broken__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 12 Jul 2019 14:14:06 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4891 Update bin paths for .lando.yml, they were moved.

* __Merge branch &#39;develop&#39; into VAGOV-4891-lando-drush-commands-broken__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 11 Jul 2019 09:18:54 -0600


## 2019-07-11.1 Sprint 18 mid-sprint release 2.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Thu, 11 Jul 2019 11:01:30 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190711.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-11.1 Sprint 18 mid-sprint release 2.

* __Merge pull request #440 from kevwalsh/4010-improve-sections-view__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 11 Jul 2019 09:17:32 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4010: Improve Sections content admin views.

* __Merge pull request #439 from ethanteague/VAGOV-4423-redirects-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 18:27:43 -0600

    VAGOV-4423:

* __Merge branch &#39;develop&#39; into VAGOV-4423-redirects-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 16:47:40 -0600

* __Merge pull request #437 from kevwalsh/4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 16:47:14 -0600

     VAGOV-4688: Temporarily restore deleted field instances.

* __Merge branch &#39;develop&#39; into 4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 16:12:41 -0600

* __Merge pull request #436 from ElijahLynn/VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 16:12:15 -0600

    VAGOV-4770 Update Jenkins job build trigger URL.

* __Merge branch &#39;develop&#39; into VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 15:43:13 -0600

* __Merge branch &#39;develop&#39; into 4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 15:42:56 -0600

* __Merge pull request #435 from schiavo/VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 15:42:06 -0600

    VAGOV 2833 Update Drupal Spec Tool

* __Merge branch &#39;develop&#39; into 4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 14:38:34 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 14:38:31 -0600

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 14:38:27 -0600

* __Merge pull request #434 from kevwalsh/4744-edit-link-on-content-listing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 14:38:04 -0600

    VAGOV-4744: Edit link on content listing.

* __Merge branch &#39;develop&#39; into VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 13:28:08 -0600

* __Merge branch &#39;develop&#39; into 4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 13:28:03 -0600

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 13:28:00 -0600

* __Merge branch &#39;develop&#39; into 4744-edit-link-on-content-listing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 13:27:56 -0600

* __Merge pull request #433 from department-of-veterans-affairs/VAGOV-4732-outreach-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 13:27:28 -0600

    VAGOV-4732: Updating update hook to account for new outreach config.

* __Merge branch &#39;develop&#39; into 4688-get-updates-links3__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 11:39:44 -0600

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 11:39:18 -0600

* __Merge branch &#39;develop&#39; into 4744-edit-link-on-content-listing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 11:39:12 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4732-outreach-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 11:39:07 -0600

    efs/remotes/upstream/VAGOV-4732-outreach-et

* __Merge pull request #431 from kevwalsh/000-single-value-alert__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 11:38:38 -0600

    VAGOV-000: Make alert to single value, and update landing page AX.

* __Merge branch &#39;develop&#39; into 000-single-value-alert__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:05:47 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:05:14 -0600

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:05:10 -0600

* __Merge branch &#39;develop&#39; into 4744-edit-link-on-content-listing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:05:05 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4732-outreach-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:04:47 -0600

* __Merge pull request #432 from kevwalsh/4688-get-updates-links2__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 10:04:17 -0600

    VAGOV-4688: Add operating status field to health care system.

* __Merge branch &#39;develop&#39; into 4688-get-updates-links2__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:12:45 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4770-build-trigger-url-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:12:19 -0600

* __Merge branch &#39;develop&#39; into VAGOV-2833__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:12:14 -0600

* __Merge branch &#39;develop&#39; into 4744-edit-link-on-content-listing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:12:11 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4732-outreach-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:11:57 -0600

* __Merge branch &#39;develop&#39; into 000-single-value-alert__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:11:43 -0600

* __Merge pull request #430 from beeyayjay/VAGOV-4562-widget-migration-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 10 Jul 2019 09:11:24 -0600

    VAGOV-4562: Update migration to get widget data from content

* __Merge branch &#39;develop&#39; into VAGOV-4562-widget-migration-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 13:46:39 -0600

* __Merge branch &#39;develop&#39; into 000-single-value-alert__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 13:46:36 -0600

* __Merge branch &#39;develop&#39; into 000-single-value-alert__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Tue, 9 Jul 2019 20:52:02 +0200


## 2019-07-09.1 Sprint 18 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 13:47:30 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190709.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-09.1 Sprint 18 mid-sprint release 1.

* __Merge pull request #429 from kevwalsh/4688-get-updates-links__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 13:46:10 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4688: Configure new email links on facility pages.

* __Merge pull request #428 from kevwalsh/4779-benefits-hubs-menus__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 11:12:01 -0600

    efs/heads/feature/VAGOV-3950-remove-xmlsitemap-ah
    VAGOV-4779: Configure menus for remaining benefits hub.

* __Merge branch &#39;develop&#39; into 4779-benefits-hubs-menus__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 10:41:48 -0600

* __Merge pull request #427 from kevwalsh/4732-outreach-events__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 9 Jul 2019 10:41:30 -0600

    VAGOV-4732: Outreach events.

* __Merge pull request #425 from kevwalsh/4732-outreach-events__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 8 Jul 2019 14:24:47 -0600

    VAGOV-4732: Refactor outreach events and publications to use listings.

* __Merge branch &#39;4732-outreach-events&#39; of https://github.com/kevwalsh/va.gov-cms into 4732-outreach-events__

    [Daniel Schiavone](mailto:daniel@snakehill.net) - Mon, 8 Jul 2019 13:43:36 -0400

* __Merge pull request #422 from kevwalsh/4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 14:53:52 -0600

    VAGOV-4709: (and 4708) for Housing Assistance and Careers icons.

* __Merge branch &#39;develop&#39; into 4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:53:41 -0600

* __Merge pull request #424 from kevwalsh/4383-pittsburgh-menu-breadcrumb2__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:53:31 -0600

    VAGOV-4383: Re-enable housing and life insurance menu breadcrumb settings.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:29:28 -0600

    VAGOV-000: 2019-07-01.1 final PR merges.

* __Merge branch &#39;develop&#39; into 4383-pittsburgh-menu-breadcrumb2__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:23:23 -0600

* __Merge branch &#39;develop&#39; into 4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:23:19 -0600

* __Merge pull request #392 from kevwalsh/3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:23:08 -0600

    VAGOV-3122: Create staff profile paragraph type, and enable on Detail Page.

* __Merge branch &#39;develop&#39; into 4383-pittsburgh-menu-breadcrumb2__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:20:40 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:55:17 -0600

* __Merge branch &#39;develop&#39; into 4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:55:15 -0600

* __Merge branch &#39;develop&#39; into 4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:26:52 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:25:42 -0600

* __Merge branch &#39;develop&#39; into 4709-housing-icon__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:24:36 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 28 Jun 2019 11:11:45 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:06:54 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Kevin Walsh](mailto:kevinjosephwalsh@gmail.com) - Mon, 17 Jun 2019 23:34:11 +0200

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 10:06:07 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 09:12:20 -0600

* __Merge branch &#39;develop&#39; into 3122-staff-profile-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 13 Jun 2019 09:28:14 -0600


## 2019-07-02.1 Sprint 17 final release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 13:18:13 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190702.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-02.1 Sprint 17 final release 1.

* __Merge pull request #421 from department-of-veterans-affairs/VAGOV-3733-single-value-field-link__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:54:59 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3733: Revert back to single value field_link

* __Merge branch &#39;develop&#39; into VAGOV-3733-single-value-field-link__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:26:45 -0600

    efs/remotes/upstream/VAGOV-3733-single-value-field-link

* __Merge pull request #423 from kevwalsh/4383-pittsburgh-menu-breadcrumb__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:25:29 -0600

    VAGOV-4383: Enable menu breadcrumb for Pittsburgh menu.

* __Merge branch &#39;develop&#39; into VAGOV-3733-single-value-field-link__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 2 Jul 2019 12:24:04 -0600

* __Merge pull request #420 from beeyayjay/VAGOV-4319-migration-add-page__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 2 Jul 2019 12:37:27 -0400

    VAGOV-4319 migration add page

* __Merge pull request #419 from beeyayjay/VAGOV-4319-migration-fixes__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 1 Jul 2019 15:32:27 -0600

    VAGOV-4319 migration fixes


## 2019-07-01.1 Sprint 17 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Mon, 1 Jul 2019 11:12:17 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190701.1, refs/remotes/upstream/master
    VAGOV-000: 2019-07-01 Sprint 17 mid-sprint release 1.

* __Merge pull request #418 from ethanteague/VAGOV-4361-related-office-fix-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 27 Jun 2019 11:39:29 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-4361

* __Merge branch &#39;develop&#39; into VAGOV-4361-related-office-fix-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 27 Jun 2019 09:36:10 -0600

* __Merge pull request #417 from kevwalsh/4662-menu-items-unpublished-nodes__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 27 Jun 2019 09:35:04 -0600

    VAGOV-4662: Patch drupal core to allow unpublished parent menu items.

* __Merge branch &#39;develop&#39; into 4662-menu-items-unpublished-nodes__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Thu, 27 Jun 2019 00:59:33 +0200

* __Merge pull request #416 from beeyayjay/VAGOV-4302-life-insurance-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 13:13:42 -0600

    VAGOV-4302: life insurance migration

* __Merge branch &#39;develop&#39; into VAGOV-4302-life-insurance-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 12:40:46 -0600

* __Merge pull request #413 from department-of-veterans-affairs/VAGOV-3158-email-sending__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 12:40:30 -0600

    VAGOV-3158: Email Sending from Drupal

* __Merge branch &#39;develop&#39; into VAGOV-3158-email-sending__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 12:04:23 -0600

    efs/remotes/upstream/VAGOV-3158-email-sending

* __Merge branch &#39;develop&#39; into VAGOV-4302-life-insurance-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 12:04:20 -0600

* __Merge pull request #415 from kevwalsh/4580-node-revisions-setting__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 12:03:57 -0600

    VAGOV-4580: Set page content type revisions autoclean to 200.

* __Merge branch &#39;develop&#39; into VAGOV-4302-life-insurance-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 11:25:37 -0600

* __Merge branch &#39;develop&#39; into 4580-node-revisions-setting__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 11:25:21 -0600

* __Merge pull request #414 from kevwalsh/4579-entity-reference-unpublished__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 11:25:06 -0600

    VAGOV-4579: Remove published checkbox and other fields from media entity forms.

* __Merge branch &#39;develop&#39; into VAGOV-3158-email-sending__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 26 Jun 2019 11:22:33 -0600

* __Merge pull request #412 from beeyayjay/VAGOV-4316-housing-migration__

    [jonbot](mailto:jonathan@majorrobot.com) - Tue, 25 Jun 2019 12:15:55 -0400

    VAGOV-4316: housing migration

* __Merge pull request #406 from ethanteague/VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 24 Jun 2019 10:31:14 -0600

    VAGOV-3812

* __Merge branch &#39;develop&#39; into VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 18:03:05 -0600

* __Merge pull request #405 from kevwalsh/4398-moderation-sidebar-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 18:02:20 -0600

    VAGOV-4398: Permissions for moderation sidebar.

* __Merge branch &#39;develop&#39; into VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 13:33:56 -0600

* __Merge branch &#39;develop&#39; into 4398-moderation-sidebar-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 13:33:51 -0600

* __Merge pull request #410 from department-of-veterans-affairs/VAGOV-4804-facility-detail-config-updates__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 13:30:28 -0600

    VAGOV-4804: Config changes for healthcare local facility content type

* __Merge branch &#39;develop&#39; into VAGOV-4804-facility-detail-config-updates__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 12:08:44 -0600

    efs/remotes/upstream/VAGOV-4804-facility-detail-config-updates

* __Merge branch &#39;develop&#39; into VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 12:08:30 -0600

* __Merge branch &#39;develop&#39; into 4398-moderation-sidebar-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 12:08:17 -0600

* __Merge pull request #409 from beeyayjay/VAGOV-4117-migrate-from-different-sources__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 11:47:03 -0600

    VAGOV-4117: Add optional config var to set server (defaults to va.gov)

* __Merge branch &#39;develop&#39; into VAGOV-4804-facility-detail-config-updates__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 08:57:45 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4117-migrate-from-different-sources__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 08:57:01 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 08:56:21 -0600

* __Merge branch &#39;develop&#39; into 4398-moderation-sidebar-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 08:55:54 -0600

* __Merge pull request #403 from beeyayjay/VAGOV-4368-migrate-select-tags-to-intro__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 21 Jun 2019 08:55:34 -0600

    VAGOV-4368: Modify plain text migration to allow &lt;a&gt;&lt;em&gt;&lt;strong&gt;&lt;p&gt;&lt;br&gt;

* __Merge branch &#39;develop&#39; into VAGOV-4117-migrate-from-different-sources__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:06:29 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3812-flags-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:05:41 -0600

* __Merge branch &#39;develop&#39; into 4398-moderation-sidebar-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:05:24 -0600

* __Merge branch &#39;develop&#39; into VAGOV-4368-migrate-select-tags-to-intro__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 14:05:04 -0600

* __Merge pull request #408 from kevwalsh/4506-allow-required-login-class__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 20 Jun 2019 13:40:56 -0600

    VAGOV-4506: Allow login-required class in rich text.

* __Merge branch &#39;develop&#39; into 4398-moderation-sidebar-permissions__

    [Kevin Walsh](mailto:kevinjosephwalsh@gmail.com) - Wed, 19 Jun 2019 15:57:11 +0200

## 2019-06-18.1 Sprint 16 final release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 18 Jun 2019 11:19:51 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190618.1, refs/remotes/upstream/master
    VAGOV-000: 2019-06-18 Sprint 16 final release 1.

* __Merge pull request #400 from kevwalsh/3983-alias-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 18 Jun 2019 11:15:56 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3983: Allow content_publisher to create URL aliases.

* __Merge branch &#39;develop&#39; into 3983-alias-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 18 Jun 2019 09:44:28 -0600

* __Merge pull request #402 from kevwalsh/4333-cer-region-to-regional-health-service__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 18 Jun 2019 09:43:42 -0600

     VAGOV-4333: Create CER between Health System and Regional Health Service.

* __Merge branch &#39;develop&#39; into 3983-alias-permissions__

    [Kevin Walsh](mailto:kevinjosephwalsh@gmail.com) - Mon, 17 Jun 2019 23:46:59 +0200

* __Merge pull request #399 from ethanteague/VAGOV-4154-event-info-field-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 13:14:29 -0600

    VAGOV-4154: Changing additional info event field to rich text.

* __Merge branch &#39;develop&#39; into VAGOV-4154-event-info-field-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 12:39:57 -0600

* __Merge pull request #398 from kevwalsh/3982-aliases-menus__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 12:39:39 -0600

    VAGOV-3982: Fix menu link alias.

* __Merge branch &#39;develop&#39; into 3982-aliases-menus__

    [Kevin Walsh](mailto:kevinjosephwalsh@gmail.com) - Mon, 17 Jun 2019 18:50:59 +0200

* __Merge pull request #397 from beeyayjay/VAGOV-3365-migrate-meta-title__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 10:04:36 -0600

    VAGOV-3365 migrate meta title

* __Merge branch &#39;develop&#39; into VAGOV-3365-migrate-meta-title__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 09:36:04 -0600

* __Merge pull request #396 from schiavo/VAGOV-0000__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 17 Jun 2019 09:35:26 -0600

    VAGOV-0000 Disable outdated migration phpunit test.

* __Merge pull request #394 from department-of-veterans-affairs/VAGOV-3733-single-value-link-teaser-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 10:30:28 -0600

    VAGOV-3733: fieldLink multivalue fix

* __Merge branch &#39;develop&#39; into VAGOV-3733-single-value-link-teaser-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 10:01:34 -0600

    efs/remotes/upstream/VAGOV-3733-single-value-link-teaser-field

* __Merge pull request #393 from ethanteague/VAGOV-3951-debug-mod-sidebar-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 10:00:36 -0600

    VAGOV-3951

* __Merge branch &#39;develop&#39; into VAGOV-3733-single-value-link-teaser-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 09:12:53 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3951-debug-mod-sidebar-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 09:12:44 -0600

* __Merge pull request #390 from beeyayjay/VAGOV-3911-migrate-careers__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 14 Jun 2019 09:10:58 -0600

    VAGOV-3911 migrate careers

* __Merge branch &#39;develop&#39; into VAGOV-3911-migrate-careers__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 13 Jun 2019 09:27:43 -0600

* __Merge pull request #388 from kevwalsh/2753-content-locking__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 13 Jun 2019 09:27:22 -0600

    VAGOV-2753: Content locking

* __Merge branch &#39;develop&#39; into VAGOV-3911-migrate-careers__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 12 Jun 2019 13:29:13 -0700

* __Merge pull request #364 from kevwalsh/3888-admin-menu-improvements__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 12 Jun 2019 13:21:23 -0700

    VAGOV-3888: admin menu improvements. (Sprint 16)

* __Merge pull request #2 from schiavo/PR-364__

    [Kevin Walsh](mailto:kevinjosephwalsh@gmail.com) - Wed, 12 Jun 2019 20:36:38 +0200

    VAGOV-3888 Update security permissions test.

* __Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 12 Jun 2019 11:59:42 -0600

* __Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 11 Jun 2019 15:19:22 -0600

* __Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 11 Jun 2019 09:50:06 -0600

* __VAGOV-3888 Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Mon, 10 Jun 2019 17:07:07 -0700

    Conflicts:
       composer.lock

    `git fetch origin`
    `git merge origin/develop` (conflicts here)
    `git checkout develop -- composer.json composer.lock
    &#39;Manually add back GraphQL deletions we lost on above step`
    `lando composer require drupal/taxonomy_menu drupal/toolbar_menu
    drupal/toolbar_menu_clean` (adds these to composer.json and updates
    composer.lock`
    `git add --update`
    `git commit` (finish the merge here)

* __Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 7 Jun 2019 16:52:26 -0600

* __Merge branch &#39;develop&#39; into 3888-admin-menu-improvements__

    [Kevin Walsh](mailto:kevin.walsh@civicactions.com) - Wed, 5 Jun 2019 07:05:58 -0700


## 2019-06-12.1 Sprint 16 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 4 Jun 2019 13:59:55 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190604.1
    VAGOV-000: 2019-06-04.1 Sprint 15 final release 1.

* __Merge pull request #365 from kevwalsh/1895-revert-moderation-tab-change__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 4 Jun 2019 10:17:46 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-1895: Revert: Hide history and revision tabs on node edit pages.

* __Merge branch &#39;develop&#39; into 1895-revert-moderation-tab-change__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 4 Jun 2019 08:53:05 -0600

* __Merge pull request #363 from beeyayjay/VAGOV-3472-migrate-linkslist-outside-qa__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 4 Jun 2019 08:52:16 -0600

    VAGOV-3957: End answer when links list is hit during migration.

* __Merge pull request #303 from ethanteague/VAGOV-3247-remove-deactivated-theme-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 16:26:51 -0600

    VAGOV-3247 remove deactivated theme - merge 2nd

* __Merge branch &#39;develop&#39; into VAGOV-3247-remove-deactivated-theme-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 15:39:05 -0600

* __Merge pull request #361 from andyhawks/feature/VAGOV-2247-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 15:38:37 -0600

    VAGOV-2247: Set up SimpleSAML infrastructure on CMS.

* __Merge branch &#39;develop&#39; into feature/VAGOV-2247-simplesamlphp-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 15:09:47 -0600

    efs/remotes/origin/feature/VAGOV-2247-simplesamlphp-ah

* __Merge pull request #362 from department-of-veterans-affairs/VAGOV-000-adhoc-enable-menu-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 15:09:15 -0600

    VAGOV-000: Adding additional menu option to detail pages.

* __Merge pull request #360 from department-of-veterans-affairs/VAGOV-3734-remaining-health-services-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 13:41:55 -0600

    VAGOV-3734: Changing commonly treated conditions label

* __Merge branch &#39;develop&#39; into VAGOV-3734-remaining-health-services-field__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 11:27:42 -0600

    efs/remotes/upstream/VAGOV-3734-remaining-health-services-field

* __Merge pull request #359 from kevwalsh/3887-sort-composer__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 11:25:29 -0600

    VAGOV-3887: Sort composer dependencies alphabetically.

* __Merge branch &#39;develop&#39; into VAGOV-3734-remaining-health-services-field__

    [Adrienne](mailto:adrienne.cabouet@gmail.com) - Mon, 3 Jun 2019 10:02:44 -0700

* __Merge branch &#39;develop&#39; into 3887-sort-composer__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 10:53:49 -0600

* __Merge pull request #358 from beeyayjay/VAGOV-3483-migration-similarity-test__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 10:53:28 -0600

    VAGOV-3483 migration similarity test

* __Merge branch &#39;develop&#39; into VAGOV-3483-migration-similarity-test__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 10:25:30 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3247-remove-deactivated-theme-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 10:24:36 -0600

* __Merge pull request #356 from department-of-veterans-affairs/VAGOV-3734-health-services-fields__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 3 Jun 2019 10:21:28 -0600

    VAGOV-3734: Updated fields on health service taxonomy

* __Merge branch &#39;develop&#39; into VAGOV-3734-health-services-fields__

    [Adrienne](mailto:adrienne.cabouet@gmail.com) - Fri, 31 May 2019 16:45:07 -0700

    efs/remotes/upstream/VAGOV-3734-health-services-fields

* __Merge pull request #354 from beeyayjay/VAGOV-3600-composer-patch-workaround__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 31 May 2019 17:31:22 -0600

    VAGOV-3600 composer patch workaround

* __Merge branch &#39;develop&#39; into VAGOV-3600-composer-patch-workaround__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 31 May 2019 16:39:51 -0600

* __Merge pull request #355 from schiavo/VAGOV-001__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 31 May 2019 16:38:08 -0600

    VAGOV-000 Update counts in migration count test.

* __Merge pull request #353 from beeyayjay/VAGOV-3600-pension-migration-alert__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 31 May 2019 12:44:51 -0600

    VAGOV-3600 pension migration alert

## 2019-05-30.1 Sprint 15 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Thu, 30 May 2019 13:14:08 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190530.1, refs/remotes/origin/master
    VAGOV-000: Sprint 15 mid-sprint release 1.

* __Merge pull request #352 from ethanteague/VAGOV-2955-json-tab-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 30 May 2019 12:33:08 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-2955: Change the view json tab to node view.

* __Merge branch &#39;develop&#39; into VAGOV-2955-json-tab-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 30 May 2019 09:40:43 -0600

* __Merge pull request #351 from beeyayjay/VAGOV-3600-pension-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 30 May 2019 09:40:09 -0600

    Vagov 3600 pension migration

* __Merge pull request #350 from andyhawks/feature/VAGOV-3750-feature-flag-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 13:20:45 -0600

    VAGOV-3750: Implement Feature Flagging in Drupal.

* __Merge branch &#39;develop&#39; into feature/VAGOV-3750-feature-flag-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 12:41:58 -0600

    efs/remotes/origin/feature/VAGOV-3750-feature-flag-ah

* __Merge pull request #349 from schiavo/VAGOV-3633__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 12:41:38 -0600

    VAGOV-3633 Uninstall paragraphs_type_permissions module, update test.

* __Merge branch &#39;develop&#39; into feature/VAGOV-3750-feature-flag-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:56:57 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3633__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:56:44 -0600

* __Merge pull request #347 from beeyayjay/VAGOV-000-records-menu-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:39:27 -0600

    VAGOV-000 records menu migration

* __Merge branch &#39;develop&#39; into feature/VAGOV-3750-feature-flag-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:24:23 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3633__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:24:14 -0600

* __Merge branch &#39;develop&#39; into VAGOV-000-records-menu-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:21:42 -0600

* __Merge pull request #346 from kevwalsh/3850-not-a-veteran__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 11:21:28 -0600

    VAGOV-3850: Links for non-veterans on hub landing pages.

* __Merge branch &#39;develop&#39; into VAGOV-3633__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 29 May 2019 10:41:14 -0600

* __Merge branch &#39;develop&#39; into VAGOV-000-records-menu-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 28 May 2019 11:07:14 -0600

* __Merge branch &#39;develop&#39; into 3850-not-a-veteran__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 28 May 2019 11:04:41 -0600

* __Merge pull request #343 from kevwalsh/ax-various__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 28 May 2019 11:02:51 -0600

    efs/heads/feature/VAGOV-3213-core-update-ah
    VAGOV-000: Various Sprint 15 AX improvements.

* __Merge pull request #333 from schiavo/VAGOV-3650__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 16:57:25 -0600

    VAGOV-3650 Added functional tests for edit node tab and workflow mode…

* __Merge branch &#39;develop&#39; into VAGOV-3650__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 16:31:22 -0600

* __Merge pull request #344 from beeyayjay/VAGOV-000-migration-updates__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 16:30:32 -0600

    Vagov 000 migration updates

* __Merge branch &#39;develop&#39; into VAGOV-000-migration-updates__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 16:06:15 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3650__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 15:55:50 -0600

* __Merge pull request #339 from ethanteague/VAGOV-3439-outreach-nav-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 15:55:01 -0600

    VAGOV-3439:

* __Merge branch &#39;develop&#39; into VAGOV-3650__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 15:09:34 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3439-outreach-nav-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 15:06:35 -0600

* __Merge pull request #341 from department-of-veterans-affairs/VAGOV-000-composer-commands__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 09:20:01 -0600

    VAGOV-000: Add fun commands to allow easily connecting to the proxy

* __Merge branch &#39;develop&#39; into VAGOV-000-composer-commands__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 08:52:58 -0600

    efs/remotes/upstream/VAGOV-000-composer-commands

* __Merge pull request #337 from department-of-veterans-affairs/VAGOV-3559-readme__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 23 May 2019 08:51:52 -0600

    VAGOV-3559: Updating readability of README.md, including SOCKS proxy command.

* __Merge branch &#39;develop&#39; into VAGOV-000-composer-commands__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 22 May 2019 16:49:21 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3559-readme__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 22 May 2019 16:48:20 -0600

    efs/remotes/upstream/VAGOV-3559-readme

* __Merge pull request #336 from department-of-veterans-affairs/VAGOV-0000-graphql-update-fix__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 22 May 2019 16:46:28 -0600

    VAGOV-0000: Graphql Module update with graphql core update

* __Merge branch &#39;develop&#39; into VAGOV-0000-graphql-update-fix__

    [Adrienne](mailto:adrienne.cabouet@gmail.com) - Wed, 22 May 2019 13:49:20 -0600

* __Merge branch &#39;develop&#39; into VAGOV-3559-readme__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Wed, 22 May 2019 12:13:46 -0700

* __Merge pull request #338 from department-of-veterans-affairs/readme-test__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 22 May 2019 13:04:58 -0600

    Update README.md

* __Merge pull request #335 from beeyayjay/VAGOV-3288-migration-tests__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 22 May 2019 13:02:57 -0600

    VAGOV-3288 migration tests

* __Merge pull request #332 from schiavo/VAGOV-0000__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 21 May 2019 12:29:06 -0600

    VAGOV-0000 Update permissions security phpunit test.


## 2019-05-21.1 Sprint 14 final release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 21 May 2019 09:51:40 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190521.1, refs/remotes/upstream/master
    VAGOV-000: 2019-05-21 Sprint 14 final release 1.

* __Merge pull request #330 from beeyayjay/VAGOV-3259-fix-migration-title-casing__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 21 May 2019 08:55:30 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3259 Fix migration title casing

* __Merge pull request #329 from andyhawks/feature/VAGOV-000-perms-0520-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 17:44:40 -0600

    VAGOV-000: Update permissions phpunit test.

* __Merge pull request #326 from beeyayjay/VAGOV-3531-migrate-table-paragraphs__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 14:46:26 -0600

    VAGOV-3531: Add paragraph migration for tables.

* __Merge pull request #325 from ethanteague/VAGOV-2270-facility-maps-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 14:44:55 -0600

    VAGOV-2270: Updating image field to indicate 3:2 crop style.

* __Merge pull request #324 from kevwalsh/vagov-3655-textfield-counter-bug__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 14:44:23 -0600

    VAGOV-3665: textfield counter bug.

* __Merge branch &#39;develop&#39; into vagov-3655-textfield-counter-bug__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 14:43:15 -0600

* __Merge pull request #323 from acabouet/VAGOV-3663-graphql-module-update__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 14:41:42 -0600

    VAGOV-3663: Update graphql module

* __Merge pull request #327 from schiavo/VAGOV-000__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 20 May 2019 13:17:14 -0600

    VAGOV-000 Update roles/permission test

* __Merge pull request #322 from beeyayjay/VAGOV-3305-records-migration-content-bugs__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 17 May 2019 08:47:34 -0600

    VAGOV-3305 records migration content bugs

* __Merge pull request #320 from kevwalsh/3639-workbench-menu-access__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 16 May 2019 14:43:25 -0600

    VAGOV-3639: Workbench menu access

* __Merge branch &#39;develop&#39; into 3639-workbench-menu-access__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 16 May 2019 14:38:22 -0600

* __Merge pull request #318 from department-of-veterans-affairs/VAGOV-3028__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 16 May 2019 14:36:06 -0600

    VAGOV-3028 Tablefield authoring experience updates with test updates.

* __Merge pull request #317 from kevwalsh/3517-hub-icons__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 16 May 2019 14:32:52 -0600

    VAGOV-3517: Add more icons as options for hub landing pages, and improve form
    AX

* __Merge pull request #316 from beeyayjay/VAGOV-000-migration-work__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 15 May 2019 13:39:04 -0600

    Vagov 000 migration work


## 2019-05-14.1 Sprint 14 mid-sprint release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 14 May 2019 10:26:01 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190514.1, refs/remotes/upstream/master
    VAGOV-000: Release 2019-05-14.1 Sprint 14 mid-sprint release 1.

* __Merge pull request #314 from kevwalsh/3457-other-va-locations__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 14 May 2019 08:52:23 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3457: Other VA locations field on Our Locations page.

* __Merge pull request #313 from kevwalsh/3474-allow-react-widget__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 14 May 2019 08:49:56 -0600

    VAGOV-3474: Allow react widget as a question type.

* __Merge pull request #312 from kevwalsh/3448-health-service-relationships__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 14 May 2019 08:48:09 -0600

    VAGOV-3448: Health service relationships

* __Merge pull request #309 from ElijahLynn/VAGOV-3461-fix-sync-db-invoking-socks-proxy__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 13 May 2019 09:15:03 -0600

    VAGOV-3461 Fix sync-db/files.sh invoking socks proxy.

* __Merge pull request #302 from acabouet/VAGOV-3262-admin-custom-theme__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 10 May 2019 11:33:31 -0600

    VAGOV-3262: Adding seven sub theme

* __Merge pull request #308 from kevwalsh/3249-buttons__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 10 May 2019 11:28:04 -0600

    VAGOV-3249: Fix primary button in rich text format and VAGOV-3197: Remove img
    and drupal-entity

* __Merge pull request #306 from acabouet/VAGOV-1897-max-character-length-counter__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 10 May 2019 11:27:06 -0600

    VAGOV-1897: Textfield Counter Module

* __Merge pull request #305 from schiavo/VAGOV-2945__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 10 May 2019 11:16:52 -0600

    VAGOV-2945 Write new Nightwatch tests for new content types

* __Merge branch &#39;develop&#39; into VAGOV-3262-admin-custom-theme__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 7 May 2019 09:27:35 -0600


## 2019-05-07.1 Sprint 13 final release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 7 May 2019 09:36:57 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190507.1
    VAGOV-000: Release 20190507.1 Sprint 13 final release

* __Merge pull request #304 from ethanteague/VAGOV-3247-deactivate-theme-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 7 May 2019 08:53:09 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-3247: Uninstall vagov custom theme - merge 1st

* __Merge pull request #301 from ElijahLynn/VAGOV-3079-fix-public-private-s3-backup-issue__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 7 May 2019 08:51:38 -0600

    VAGOV-3079 Fix public private S3 bucket, backup issue

* __Merge pull request #299 from beeyayjay/VAGOV-3058-records-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 7 May 2019 08:50:03 -0600

    VAGOV-3058 records migration

* __Merge pull request #298 from beeyayjay/VAGOV-3164-migrate-alert-paragraphs__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 6 May 2019 10:59:26 -0600

    VAGOV-3164 migrate alert paragraphs

* __Merge pull request #297 from beeyayjay/VAGOV-2807-records-section-migration__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 6 May 2019 10:57:29 -0600

    VAGOV-2807 records section migration

* __Merge pull request #294 from kevwalsh/2865-alerts-paragraph-browser__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 6 May 2019 10:53:55 -0600

    VAGOV-2865: Provide help text in paragraphs browser.


## 2019-05-02.1 Sprint 13 mid-sprint release 2.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Thu, 2 May 2019 16:19:41 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190502.1, refs/remotes/upstream/master
    VAGOV-000: 20190502.1 Release

* __Merge pull request #293 from beeyayjay/VAGOV-2867-migration-anomalies-tests__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 2 May 2019 16:17:08 -0600

    efs/remotes/upstream/develop, refs/heads/develop
    VAGOV-2867 migration anomalies tests

* __Merge pull request #292 from kevwalsh/3143-admin-content-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 2 May 2019 16:11:42 -0600

    VAGOV-3143: Remove access to admin/content for anon.

* __Merge branch &#39;develop&#39; into 3143-admin-content-permissions__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 2 May 2019 16:11:34 -0600

* __Merge pull request #291 from andyhawks/feature/VAGOV-000-update-env-indicator-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 2 May 2019 16:09:50 -0600

    VAGOV-3143: VAGOV-000: Environment indicator and admin/content fixes.

* __Merge pull request #290 from ethanteague/VAGOV-2973-drupal-assets-lib-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Wed, 1 May 2019 13:00:20 -0600

    VAGOV-2973: Updating outreach asset config to add field_office.


## 2019-04-30.1 Sprint 13 mid-sprint release 1.

* __Merge pull request #284 from kevwalsh/2970-detail-page__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Apr 2019 11:02:15 -0600

    efs/remotes/upstream/develop
    VAGOV-2970: Rename regional health care detail page to detail page.

* __Merge pull request #283 from kevwalsh/2516-alert-paragraph__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Apr 2019 11:00:44 -0600

    VAGOV-2516: Modify alert paragraph type and enable on page content type.

* __Merge pull request #282 from beeyayjay/VAGOV-2197-local-migration-tests__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 30 Apr 2019 10:59:17 -0600

    VAGOV-2197 local migration tests

* __Merge pull request #289 from ElijahLynn/VAGOV-3079-fix-backup-public-private-s3-bucket-master__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 29 Apr 2019 14:18:07 -0600

    VAGOV-3079 fix backup public private s3 bucket master

* __Merge branch &#39;VAGOV-3079-fix-backup-public-private-s3-bucket&#39; into VAGOV-3079-fix-backup-public-private-s3-bucket-master__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Mon, 29 Apr 2019 13:13:36 -0700

* __Merge pull request #287 from ElijahLynn/VAGOV-3079-fix-backup-public-private-s3-bucket__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Mon, 29 Apr 2019 11:55:30 -0700

    VAGOV-3079 Fix sync-db, sync-files to use private bucket.

* __Merge remote-tracking branch &#39;origin/develop&#39; into VAGOV-3079-fix-backup-public-private-s3-bucket__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Mon, 29 Apr 2019 09:21:41 -0700

* __Merge pull request #285 from ElijahLynn/VAGOV-3079-temp-fix-sync-db-files-public-private-s3-bucket__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Thu, 25 Apr 2019 18:40:02 -0600

    VAGOV-3079 Fix sync-db, sync-files commands to use manually downloaded files
    temporarily.



## 2019-04-23.1 Sprint 12 final release 1.

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 14:58:37 -0600

    EAD -&gt; refs/heads/master, tag: refs/tags/20190423.1
    VAGOV-000: Sprint 12 final rease 2 2019-04-23.


* __Merge pull request #271 from ElijahLynn/VAGOV-2022-vaec-build-trigger__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 14:53:22 -0600

    efs/remotes/upstream/develop
    VAGOV-2022 Update build trigger to use new job &amp; internal VAEC IP for DEV box.

* __Merge pull request #280 from schiavo/VAGOV-2436__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 14:48:41 -0600

    VAGOV-2436 Remove all instances of drupal8 username and password from…

* __Merge pull request #274 from acabouet/VAGOV-2507-paragraph-browser__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 14:48:12 -0600

    VAGOV-2507: Install and Configure Paragraphs Browser Module

* __Merge branch &#39;develop&#39;__

    [Andy Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 14:41:46 -0600

    VAGOV-000: Sprint 12 final release 1, 2019-04-23

* __Merge pull request #281 from beeyayjay/VAGOV-2868-widgets-in-answers__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 13:46:23 -0600

    VAGOV-2868: Allow react widgets in Q&amp;A answers.

* __Merge pull request #279 from beeyayjay/VAGOV-2193-migration-github-api__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 13:42:56 -0600

    VAGOV-2193 migration GitHub api

* __Merge pull request #277 from ethanteague/VAGOV-2450-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 23 Apr 2019 13:42:09 -0600

    VAGOV-2450

* __Merge pull request #273 from acabouet/VAGOV-1884-more-flexible-field-help-text__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 22 Apr 2019 14:27:10 -0600

    VAGOV-1884: More flexible field help text

* __Merge remote-tracking branch &#39;origin/develop&#39; into VAGOV-2022-vaec-build-trigger__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Thu, 18 Apr 2019 09:55:55 -0700

* __VAGOV-000: Merge branch &#39;develop&#39; into &#39;master&#39;__

    [Elijah Lynn](mailto:elijah.lynn@agile6.com) - Tue, 16 Apr 2019 18:34:53 -0700

    efs/remotes/upstream/master

* __Merge pull request #276 from department-of-veterans-affairs/revert-249-mb_testing_refactoring__

    [Elijah Lynn](mailto:elijah@elijahlynn.net) - Tue, 16 Apr 2019 16:33:37 -0700

    efs/remotes/upstream/ElijahLynnVAGOV-2022-vaec-build-trigger
    Revert &#34;VAGOV-1824 refactor/zero downtime deployment&#34;

* __Merge pull request #275 from schiavo/VAGOV-000__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Tue, 16 Apr 2019 12:14:52 -0600

    VAGOV-000 Update permissions in PhpUnit test

* __Merge pull request #270 from department-of-veterans-affairs/VAGOV-2749-FIX-DEPLOYMENT-ERROR__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Apr 2019 16:40:52 -0600

    VAGOV-2749 fix deployment error

* __Merge pull request #269 from ethanteague/VAGOV-2463-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Apr 2019 09:30:46 -0600

    VAGOV-2463

* __Merge pull request #268 from ethanteague/VAGOV-1811-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Apr 2019 09:27:34 -0600

    VAGOV-1811

* __Merge pull request #249 from mbenziane/mb_testing_refactoring__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Mon, 15 Apr 2019 09:25:19 -0600

    VAGOV-1824 refactor/zero downtime deployment

* __Merge pull request #267 from ethanteague/VAGOV-000-hotfix-help-email-et__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 12 Apr 2019 08:59:41 -0600

    VAGOV-000: Change help email address.

* __Merge pull request #266 from andyhawks/feature/VAGOV-2623-password-policy-reset-ah__

    [Andrew Hawks](mailto:andy@andyhawks.com) - Fri, 12 Apr 2019 08:56:33 -0600

    VAGOV-2623: Remove password_policy.

## END OF FILE
