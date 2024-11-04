---
name: "(AP) Next-build migration"
about: Templates to be migrated to next-build
title: "Next Build Template: "
labels: Accelerated Publishing, Migration
assignees: ''

---

## Template or Content Type
_Name of template or content type_

## Example
_URL to show an example of the template or content type in both Drupal CMS and VA.gov_

## Description
_Overview of template or content type, including relevant details to the migration lift._

## Product Owner 
_The team that owns the product in production_

## Definition of Done
**Data:**
- [ ] Conditional logic matches content-build / production 
- [ ] SEO Metadata matches production
- [ ] Analytics match production

**Interaction / Behavior:**
- [ ] Interactions / behavior match production (e.g. accordion expansion by default on page load if they do, data IDs on elements / their usage, link behavior, form / input field behaviors, etc) 

**Accessibility:**
- [ ] Accessibility experience matches production (voiceover/screen reader behavior, appearance at 400% zoom, params on elements, etc) 
- [ ] axe devTools output matches production

**Visual presentation:**
- [ ] Design system components, prop usage and component versions match production
- [ ] Design (font size, spacing, Anything else?) matches production
- [ ] Review all breakpoints

**Signoff / Launch**
- [ ] Owning team has reviewed the build and provided feedback / approval
- [ ] CMS Team has confirmed launch readiness 
