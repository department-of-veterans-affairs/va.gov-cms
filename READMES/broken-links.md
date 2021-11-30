# Broken Links

## Guiding principles

* Veterans should not encounter broken links.
* With hundreds of editors, broken links are inevitable.

## Strategies to Prevent Broken Links

There are 3 concurrent strategies to prevent broken links.

   1.  **CMS Pre-save Validation**
      There are several checks performed on links as they are saved that are either cleaned up automatically, or prevent saving the revision.
   2.  **[CMS Post-save Warning](#cms-post-save-warning)**: Node Link Report
   3.  **[Front End Link Checking](#front-end-link-checking)**

## CMS Post-save Warning
  The [Node Link Report](https://www.drupal.org/project/node_link_report) module tests all links from a page rendered to the anonymous user.  It looks for broken links, links to pages that are unpublished and links that may have accessability issues.
  **Cons:**

  * Cached, so it only updates on node save or 24hr, whichever comes first.
  * Some external domains are blocked by the VA network, so there are links that get falsely reported as broken.  They can be exempted here (/admin/config/content/node_link_report) once they have been confirmed to be legitimate.
  * Some external sites block the useragent that we use to check the link, which results in a falsely reported broken link.  These can also be exempted.

## Front End Link Checking

The link checking on the front end happens during the content-build in [Content-Build repo](https://github.com/department-of-veterans-affairs/content-build/tree/master/src/site/stages/build/plugins/modify-dom/check-broken-links).  Only internal links are checked. If more than 10 broken links are found, the content release is considered a failure and does not occur.  CMS team is to respond and remedy if this happens.  The broken links are reported to the #vfs-platform-builds channel in Slack.

**Notes:**

  * Only schemeless links (lacking https://) are considered internal.

[Table of Contents](../README.md)
