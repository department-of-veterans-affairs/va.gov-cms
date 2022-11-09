# CMS Content Workflow
   * [Alias Lockdown](#alias-lockdown)
   * [Content Preview](#content-preview)


## Alias Lockdown
Many of our content types are setup to lock the alias at whatever it was set
to when the node was changed to publish.  After that, any changes to title or
other alias pattern elements that make up the alias, will not alter the alias.
Which content types are affected by this lockdown are specified in
_va_gov_backend_get_locked_alias_bundles() in [va_gov_backend.module](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/main/docroot/modules/custom/va_gov_backend/va_gov_backend.module#L316)  See the function for the most up-to-date list of content types
affected.  The Alias can be updated on the node by anyone that has permission to
see the alias widget.

## Content Preview

Most nodes in the CMS have a preview button.  The preview button should only appear on nodes that have the potential to have a front-end page. *Example: Facility health service nodes, have no page on the front-end, so they have no preview button.*  The preview button's intent is to allow editors to see their work as though it appears on VA.gov.  In a decoupled site with a static build, this is a bit challenging.  As a result the preview button behaves differently based on the environment.
  * **Prod and Staging:** The preview button links to the single page preview server.  The most recent revision is used to generate that specific page.  All other pages on the preview server had already been built with their currently published state.

  * **Tugboat (PR and demo):** The preview button points to the "Web" container for that environment. It only shows the version of the page that was published at the time
  of the last content release that built Web. It is NOT a draft preview or a just published preview.  A content-release (rebuild of Web) is needed to see recently
  published changes.  Web gets built as part of the environment creation so it will be there.

  * **Sandboxes:** The preview button points to the internal build of "Web" (local va.gov) and it only shows the version of the page that was published at the time
  of the last content release that built Web. It is NOT a draft preview or a just published preview.  A content-release (rebuild of Web) is needed to see recently
  published changes. Web is NOT built automatically locally.  It gets built by running tests locally, or running the build command.
  
**More information:** [Preview button topic dive](https://www.youtube.com/watch?v=_PDtbqQQWyU&ab_channel=VACMSTraining 
) (YouTube) 

[Table of Contents](../README.md)
