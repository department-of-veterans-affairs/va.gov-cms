---
name: Product release notes
about: Draft release notes
title: "[datetime or release number] []"
labels: Release notes
assignees: rachel-kauff

---

## Publishing info
**Release date:** [mm/dd/yyyy] [optional: time]
**Scheduled publication date for product release note:** [mm/dd/yy] [optional: time]
** Items to be covered in the release note:** [e.g. several bugs], [an enhancement], [another thing]
**GH repo link:** [a link]


## User Story 
*(General) As a CMS user, I need product release notes that help me understand what changed and how it might affect my work. And because I expect these changes are boring at best and annoying at worst, I need extra encouragement to read them. Entertain me, dangit.*

## Process
1. Identify items for release notes during grooming and demo
2. Create new release note issue using this template and add items to the [Editorial calendar](https://docs.google.com/spreadsheets/d/13nWUY11A84c4WmC6vVmSE42OHaywPir5Eil4orBZz5Q/edit#gid=0)
3. Fill out release background info in this template
3. Draft the release note in this GH issue
4. Assign someone to peer review the drafted content. Ideally, this is the person/developer who made the change, and the product manager.
5. Migrate content into markdown file in va.gov-cms/product-release-notes using a correct naming convention
6. Post in Slack: @here in #cms-support  

## Release background info
* [link to relevant GH issues or epics]
* [link to any relevant screenshots or artifacts]
* Make sure you have info on the following before drafting:

**Who is responsible for the change?**

What team(s) were involved? 
* CMS team
* VSA facilities
* Public website team
* VAMC upgrade team>

**What was it like for users before the change?**

**What is like for users *after* the change?**


**Technically, what happened?**

<e.g. Previously, we were using Metalsmith to generate VAMC content listing pages as Frankenstein-like assemblages of many different content types, and they only existed on the front end. Now that these pages are their own content type, pages like /events have a home in the CMS.>

**Who is affected?**

<e.g.> VAMC content editors 

**Where will I see the change?**

<e.g. VAMC home pages: fields for content listings removed
VAMC content sections: now searchable by the new content types: “Events listing,” “Stories listing” 
New paths in the CMS for VAMC pages “/events,” “/locations,” “/health-services,” “/stories” “/news-releases,” “/leadership”>

**Will users need additional training to utilize the change?**

<e.g. Users need training to use the new form for rebuilding content PR environments for demo purposes, or content changes that require a separate front-end branch.>

## Drafted content for Slack
### CMS Release notes: [title]
**[datetime]** 

Get the full details here: <link>

## Drafted extended content for GH repo

