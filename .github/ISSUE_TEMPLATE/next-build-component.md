---
name: next-build Component
about: Template for collecting info for a next-build component.
title: 'Component: <insert name of component>'
labels: Needs refining
assignees: ''

---

## Description
A component is needed in the next-build system to render <the component>. This component should be able to be used standalone or to render field data for other nodes and paragraphs.

## Acceptance Criteria
- [ ] A component exists that can render a `<component>` data structure
- [ ] A page exists within the next-build repo that demonstrates the rendering of all `<component>`
- [ ] That component's output is consistent with the existing output of content-build (visually, markup structure)

## Supporting detail
**Paragraph/Node**: `specific paragraph/content type`

**CMS structure**: <Replace> https://prod.cms.va.gov/admin/structure/paragraphs_type/table/fields

**API query**: <Replace> https://prod.cms.va.gov/jsonapi/paragraph/table

**Existing template(s)**: <Replace> https://github.com/department-of-veterans-affairs/content-build/blob/main/src/site/paragraphs/table.drupal.liquid

**Existing/example existing GraphQL**: <Replace> https://github.com/department-of-veterans-affairs/content-build/blob/main/src/site/stages/build/drupal/graphql/paragraph-fragments/table.paragraph.graphql.js

**Logic notes**:
* <enter any specific details>

**Example to render collection of <component> objects**:
```javascript
    const phone = await getResourceCollectionFromContext(
        '<replace>',
        context,
        {} // params
    );
```

**Example data structure (subject to refinement)**:
```
enter complete data structure for one item
```

Further info: <reference to collecting info in content-api-react-poc>

### CMS Team
Please check the team(s) that will do this work.

- [ ] `Sitewide program`
- [x] `Platform CMS Team`
- [ ] `Sitewide crew ` (leave Sitewide unchecked and check the specific team instead)
  - [ ] `⭐️ Content ops`
  - [ ] `⭐️ CMS experience`
  - [ ] `⭐️ Public Websites`
  - [ ] `⭐️ Facilities`
  - [ ] `⭐️ User support`
