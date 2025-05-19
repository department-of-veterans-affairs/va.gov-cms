# 🎟️ SPIKE Ticket: Removing Storybook from VA.GOV-CMS 

**Title:**  
SPIKE Ticket: Removing Storybook from VA.GOV-CMS 

**Ticket Type:**  
SPIKE / Research

**Owner:**  
DevOps Engineer (Leigh Villarroel) - VACMS

---

## 📝 Background

It was mentioned that the Storybook in question is the one found though NodeJS and not the one utilized in Drupal via the module

---

## ✅ Goals / Acceptance Criteria
- [ ] An understanding if other Drupal CMS product teams are using Storybook. 
- [ ] A plan of action, including scope and level of effort, to remove storybook from Drupal CMS. 
- [ ] A plan of action of how to move any CMS infrastructure away from storybook into alternative. 

---

## Understanding usage of Storybook
Looks like Storybook (Node JS) was only used for the design-system. There is also a theme that is compiled and stored in docroot/design-system.
There is some commands also referenced in composer.json file that builds storyboopk

---

## Plan of Action to remove Storybook
Since the Storybook is based in Node, it should be removed just like a npm package, with all its artifacts deleted first. The following steps can
be done on every folder utilizing Storybook (e.g. design-system)
- Delete the stories created this way
- Delete the .storybook folder
- Delete Scripts Added on to package.json
- Delete all storybook related content from package.json
- Run `npm install`

---

## Plan of Action to Manage content Impacted from Storybook removal
Referring to the Storybook Node JS site, this will need to be done first in order to preserve the content as static files, as per this link [here](https://storybook.js.org/docs/sharing/publish-storybook)

If Content is not necessary, Delete?


---

## 🔗 Resources / Links

- Storybook Node JS site: [Getting Started](https://storybook.js.org/docs/get-started/install)
