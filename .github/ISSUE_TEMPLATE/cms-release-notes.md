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
1. Draft the release note in this GH issue
2. Assign someone to peer review the drafted content. Ideally, this is the person who make the change, and the product manager.
3. Migrate content into markdown file in va.gov-cms/product-release-notes using a correct naming convention
4. Post in Slack: @here in #cms-support using Slack template 

## Release background info
* [link to relevant GH issues or epics]
* [link to any relevant screenshots or artifacts]
* Make sure you have info on the following before drafting:

**Technically, what happened?**
<e.g. Previously, we were using Metalsmith to generate VAMC content listing pages as Frankenstein-like assemblages of many different content types, and they only existed on the front end. Now that these pages are their own content type, pages like /events have a home in the CMS.>

**Who is affected?**
<e.g.> VAMC content editors 

**Where will I see the change?**
<e.g. VAMC home pages: fields for content listings removed
VAMC content sections: now searchable by the new content types: “Events listing,” “Stories listing” 
New paths in the CMS for VAMC pages “/events,” “/locations,” “/health-services,” “/stories” “/news-releases,” “/leadership”>


## Drafted content for Slack
### CMS Release notes: Removed irrelevant items from the editorial workflow options
**[datetime]** 

Get the full details here: <link>

## Drafted extended content for GH repo
**Who is affected?**
<complete list of who is affected, in bulleted form
**Where will I see the change?**
<complete list of where changes will appear, in bulleted form>

**Ecstatic? Confused? Angry?**
Talk to <person> in #cms-support
