---
name: Dark launch request
about: Request to dark launch CMS content that includes React widget, owned by CMS
  team
title: 'CMS/React content dark launch request: <content info>'
labels: CMS Team, Drupal engineering
assignees: ''

---

## Description
Use this issue to request dark launch of a CMS page that includes a React widget. 

**Review documentation about the process** prior to submitting your ticket: [Dark launch Drupal content to Staging.va.gov](https://github.com/department-of-veterans-affairs/va.gov-team/tree/master/products/public-websites#4-publish-a-cms-page-only-to-staging-using-entityqueue-in-order-to-stage-cms--content-build--vets-website-for-viewing-off-of-va-network)
  
  
### Staging/dark launch
* **CMS link to content**:
* **Target dark launch date**:
* **Is this date flexible?** If not, please explain: 
* **Technical point of contact** for vets-website React code: 
* **Strategic point of contact** for timing & approval to publish the node to Staging: 

### Production launch
CMS team does not want to get in the habit of having staged nodes that will not publish, so this method should only be used for business cases that have a line of sight on production launch. 

* **Target production launch date:** 
* **What is driving and/or blocking prod launch date?** 
  
# For CMS Team use
## Tasks
Please tick the boxes for completed steps as we go, for cross-team visibility.
- [ ] Technical POC has merged vets-website code to main & confirmed deploy to prod
- [ ] Add Node ID to [Staged Content entity subqueue](https://prod.cms.va.gov/admin/structure/entityqueue/staged_content/staged_content?destination=/admin/structure/entityqueue)
- [ ] Strategic POC has given approval to publish node to Staging
- [ ] Node is published with a revision log indicating dark launch and referencing this ticket #
- [ ] Nightly CMS database sync from production to [content-build-branch-builds Tugboat](https://tugboat.vfs.va.gov/6189a9af690c68dad4877ea5) has run successfully
- [ ] After the next merge to main in content-build, the [continuous integration Github action](https://github.com/department-of-veterans-affairs/content-build/actions/workflows/continuous-integration.yml) must run and be successful.
- [ ] Verify: page is present on staging.va.gov and does not appear on va.gov (prod)

## Acceptance Criteria
- [ ] Page is verified: Live on staging.va.gov
- [ ] Page is **not** live on VA.gov
- [ ] Ticket is cut for production launch planning, e.g. #10627
