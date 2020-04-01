@api
Feature: Content model bundles
  In order to enter structured content into my site
  As a content editor
  I want to have content entity bundles that reflect my content model.

  @dst @content_type @dstbundles
     Scenario: Bundles
       Then exactly the following content entity type bundles should exist
       | Name | Machine name | Type | Description |
| Benefits detail page | page | Content type | These pages hold all of the benefits overview content, such the detail pages linked to from va.gov/disability, va.gov/health-care, and va.gov/education. |
| Benefits hub landing page | landing_page | Content type | A special page for top-level Benefits content with its own one-off layout and content. |
| Detail Page | health_care_region_detail_page | Content type | For static pages where there's not another content type already available.  |
| Documentation page | documentation_page | Content type | Help pages VA.gov CMS editors. |
| Event | event | Content type | For online or in-person events like support groups, outreach events, public lectures, and more. |
| Event listing | event_listing | Content type | A listing of events. |
| News release | press_release | Content type | Announcements directed at members of the media for the purpose of publicizing newsworthy events/happenings/programs at specific facilities or healthcare systems. |
| Office | office | Content type | An office at the VA, which may have contact info, events, news, and a leadership page in some cases. |
| Publication | outreach_asset | Content type | Contains a document, image, or video, for publication within a Publication library. |
| Publication listing | publication_listing | Content type | This allows the listing of publication materials such as documents, videos, and images all in one place. |
| Staff profile | person_profile | Content type | Profiles of staff members for display in various places around the site. |
| Story | news_story | Content type | Community stories highlight the role of a VA facility, program, or healthcare system in a Veteran's journey. They may be a case study of a specific patient, a description of a new or successful program, or a community-interest story. |
| Support Service | support_service | Content type | Help desks, hotlines, etc, to be contextually placed alongside relevant content. |
| VAMC system | health_care_region_page | Content type | A VAMC system contains multiple VHA health facilities, including usually at least one VAMC, sometimes more. |
| VAMC system banner alert with situation updates | full_width_banner_alert | Content type | A full-width alert that will be added to a VAMC system, or multiple VAMC systems. |
| VAMC facility | health_care_local_facility | Content type | A clinic or hospital within a VAMC system. |
| VAMC facility health service | health_care_local_health_service | Content type | A facility specific description of a health care service, always embedded within a VAMC system description. |
| VAMC system health service | regional_health_care_service_des | Content type | A description of a health service specific to a VAMC system. |
| VAMC system operating status | vamc_operating_status_and_alerts | Content type | Create one of these pages for each VAMC system. Then you can add banner alerts and update facilities' operating status, all from one place. |
| Alert | alert | Custom block type | An alert box that can be added to individual pages. |
| Promo | promo | Custom block type | Promote a link with an image, title, and description. |
| Document | document | Media type | A locally hosted document, such as a PDF. |
| Image | image | Media type | Locally hosted images. |
| Video | video | Media type | A video hosted by YouTube, Vimeo, or some other provider. |
| Accordion group | collapsible_panel | Paragraph type | A group of accordions. |
| Accordion Item | collapsible_panel_item | Paragraph type | An individual accordion. |
| Additional information | spanish_translation_summary | Paragraph type | Spanish summary to include a brief spanish-language summary of the content. |
| Address | address | Paragraph type | An address block. |
| Alert | alert | Paragraph type | A reusable or non-reusable alert, either "information status" or "warning status". |
| Embedded image | media | Paragraph type | For adding an image inline |
| Expandable Text | expandable_text | Paragraph type | Text that expands upon click. |
| Link teaser | link_teaser | Paragraph type | A link followed by a description. For building inline "menus" of content. |
| Link to file or video | downloadable_file | Paragraph type | For image or document downloads. |
| List of link teasers | list_of_link_teasers | Paragraph type | A paragraph that contains only one type of paragraph: Link teaser. |
| Number callout | number_callout | Paragraph type | Number callouts can be used in the context of a question & answer, where the answer can be summarized in a short phrase that is number-oriented. |
| Process list | process | Paragraph type | An ordered list (1, 2, 3, 4, N) of steps in a process. |
| Q&A | q_a | Paragraph type | Question and Answer |
| Q&A Section | q_a_section | Paragraph type | For content formatted as a series of questions and answers. Use this (instead of WYSIWYG) for better accessibility and easy rearranging. |
| React Widget | react_widget | Paragraph type | Advanced editors can use this to place react widgets (like a form) on the page. |
| Staff profile | staff_profile | Paragraph type | Add a profile of a staff person. |
| Starred Horizontal Rule | starred_horizontal_rule | Paragraph type | Current an inactive paragraph type, not enabled within any fields. |
| Table | table | Paragraph type | Add an HTML table with rows and columns. |
| VAMC facility service (non-healthcare service) | health_care_local_facility_servi | Paragraph type | A service available at a specific health care facility, like Parking, or Chaplaincy. |
| WYSIWYG | wysiwyg | Paragraph type | An open-ended text field. |
| Situation update | situation_update | Paragraph type | A time-sensitive, added to a banner alert, and displayed on VAMC operating status pages. |
| Sections | administration | Vocabulary | Represents a hierarchy of the VA, partly for governance purposes. |
| Type of Redirect | type_of_redirect | Vocabulary |  |
| VHA health service taxonomy | health_care_service_taxonomy | Vocabulary | Single source of truth for health service names, descriptions, patient-friendly names, and common conditions. |
| Health services listing | health_services_listing | Content type | A listing of health services. |
| Locations listing | locations_listing | Content type | A listing of locations. |
| News releases listing | press_releases_listing | Content type | A listing of press releases. |
| Story listing | story_listing | Content type | A listing of stories. |
| Leadership listing | leadership_listing | Content type | A listing of staff members. |
| VBA facility | vba_facility | Content type | A facility within Veterans Benefits Administration system. |
| NCA facility | nca_facility | Content type | A facility within National Cemetery Administration system. |
| Vet Center | vet_center | Content type | A facility within Vet Centers system. |
