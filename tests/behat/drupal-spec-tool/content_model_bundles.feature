
@api
Feature: Content model bundles
  In order to enter structured content into my site
  As a content editor
  I want to have content entity bundles that reflect my content model.

  @dst @content_type @dstbundles
  Scenario: Bundles
    Then exactly the following content entity type bundles should exist
      | Name | Machine name | Type | Description |
      | Full Width Alert | banner | Content type | A full width dismissible banner |
      | Landing Page | basic_landing_page | Content type | Basic Landing Page can be used to build one-off pages for various products. E.g. a homepage for a specific product. |
      | Campaign Landing Page | campaign_landing_page | Content type |  |
      | Centralized Content | centralized_content | Content type | Common content for reuse on other content types. |
      | Checklist | checklist | Content type | A page that lists the items a Veteran or other beneficiary needs to complete a process. For example, a list of documents needed to apply for a benefit.   |
      | CMS Knowledge Base Article | documentation_page | Content type | Articles about how to use the VA.gov Drupal Content Management System (CMS). |
      | Event | event | Content type | For online or in-person events like support groups, outreach events, public lectures, and more. |
      | Events List | event_listing | Content type | A listing of events. |
      | FAQ page | faq_multiple_q_a | Content type | A collection of several Q&As related to one topic. |
      | VAMC System Banner Alert with Situation Updates | full_width_banner_alert | Content type | A full-width alert that will be added to a VAMC system, or multiple VAMC systems. |
      | VAMC Facility | health_care_local_facility | Content type | A clinic or hospital within a VAMC system. |
      | VAMC Facility Health Service | health_care_local_health_service | Content type | A facility specific description of a health care service, always embedded within a VAMC system description. |
      | VAMC Detail Page | health_care_region_detail_page | Content type | For static pages where there's not another content type already available.  |
      | VAMC System | health_care_region_page | Content type | A VAMC system contains multiple VHA health facilities, including usually at least one VAMC, sometimes more. |
      | Health Services List | health_services_listing | Content type | A listing of health services. |
      | Benefits Hub Landing Page | landing_page | Content type | A special page for top-level Benefits content with its own one-off layout and content. |
      | Leadership List | leadership_listing | Content type | A listing of staff profiles. |
      | VAMC System Locations List | locations_listing | Content type | A listing of VA facilities. |
      | Image list | media_list_images | Content type | A page that displays a series of related images. |
      | Video list | media_list_videos | Content type | A page that displays a series of related videos.  |
      | NCA Facility | nca_facility | Content type | A facility within National Cemetery Administration system. |
      | Story | news_story | Content type | Community stories highlight the role of a VA facility, program, or healthcare system in a Veteran's journey. They may be a case study of a specific patient, a description of a new or successful program, or a community-interest story. |
      | Office | office | Content type | An office at the VA, which may have contact info, events, news, and a leadership page in some cases. |
      | Publication | outreach_asset | Content type | Contains a document, image, or video, for publication within a Publication library. |
      | Benefits Detail Page | page | Content type | These pages hold all of the benefits overview content, such the detail pages linked to from va.gov/disability, va.gov/health-care, and va.gov/education. |
      | Staff Profile | person_profile | Content type | Profiles of staff members for display in various places around the site. |
      | News Release | press_release | Content type | Announcements directed at members of the media for the purpose of publicizing newsworthy events/happenings/programs at specific facilities or healthcare systems. |
      | News Releases List | press_releases_listing | Content type | A listing of news releases. |
      | Promo Banner | promo_banner | Content type | Promo banners are fixed content used for dismissible announcements such as new tools, news, etc. |
      | Publication Listing Page | publication_listing | Content type | This allows the listing of publication materials such as documents, videos, and images all in one place. |
      | Q&A - single | q_a | Content type | Reusable content that answers a single question from Veterans or other beneficiaries. A Q&A may appear on its own or as part of an FAQ page. |
      | VAMC System Health Service | regional_health_care_service_des | Content type | A description of a health service specific to a VAMC system, which appears on a VAMC's health services page and on facility pages, within accordions. |
      | Step-by-Step | step_by_step | Content type | A page that includes numbered steps a Veteran can take to complete an action. Use for instructions that must be completed in order. |
      | Stories List | story_listing | Content type | A listing of stories. |
      | Resources and Support Detail Page | support_resources_detail_page | Content type | A page that contains in-depth information about a single resource available to Veterans or other beneficiaries.  |
      | Support Service | support_service | Content type | Help desks, hotlines, etc, to be contextually placed alongside relevant content. |
      | VA Form | va_form | Content type | VA forms available for download. Used to populate search results and also generate form landing pages |
      | VAMC System Operating Status | vamc_operating_status_and_alerts | Content type | Create one of these pages for each VAMC system. Then you can add banner alerts and update facilities' operating status, all from one place. |
      | VAMC System Billing and Insurance | vamc_system_billing_insurance | Content type | A page about how Veterans can pay a bill within a VAMC system. |
      | VAMC System Medical Records Office | vamc_system_medical_records_offi | Content type | A page about how Veterans can access their medical records within a VAMC system. |
      | VAMC System Policies Page | vamc_system_policies_page | Content type | Add policies specific to this VA medical center to appear on the Policies page. Local policies will appear alongside national policies that apply to all VAMCs. |
      | VAMC System Register for Care | vamc_system_register_for_care | Content type | A page about how Veterans can register for care at a specific VAMC system. |
      | VBA Facility | vba_facility | Content type | A facility within Veterans Benefits Administration system. |
      | Vet Center | vet_center | Content type | Location and page content for community-based counseling centers. |
      | Vet Center - Community Access Point | vet_center_cap | Content type | Location information for Vet Center services situated in another organization. |
      | Vet Center - Facility Service | vet_center_facility_health_servi | Content type | Facility-specific description of a health service available at a Vet Center. |
      | Vet Center - Locations List | vet_center_locations_list | Content type | Page content that lists all facilities associated with a Vet Center, including nearby locations. |
      | Vet Center - Mobile Vet Center | vet_center_mobile_vet_center | Content type | Location information for Vet Center services provided out of an RV. |
      | Vet Center - Outstation | vet_center_outstation | Content type | Location information for remote facilities related to a main Vet Center. |
      | VAMC Facility Non-clinical Service | vha_facility_nonclinical_service | Content type | Address and contact info for offices and other non-clinical service locations. This content is always embedded within a VAMC system non-clinical service page. |
      | Alert | alert | Custom block type | An alert box that can be added to individual pages. |
      | Benefit Promo | benefit_promo | Custom block type | A call to action with a title, description, CTA label and link. |
      | CMS Announcement | cms_announcement | Custom block type | Display an announcement to CMS users. |
      | News Promo | news_promo | Custom block type | Promote a news link with a title and description. |
      | Promo | promo | Custom block type | Promote a link with an image, title, and description. |
      | Document | document | Media type | A locally hosted document, such as a PDF. |
      | Document - External | document_external | Media type |  |
      | Image | image | Media type | Locally hosted images. |
      | Video | video | Media type | A video hosted by YouTube, Vimeo, or some other provider. |
      | Address | address | Paragraph type | An address block. |
      | Alert | alert | Paragraph type | A reusable or non-reusable alert, either "information status" or "warning status". |
      | Alert (single) | alert_single | Paragraph type |  |
      | Audience & Topics | audience_topics | Paragraph type | Audience & Topic selection for "Resources and Support" articles. |
      | Accordion | basic_accordion | Paragraph type |  |
      | Call to action | button | Paragraph type | Button with a label and link field. |
      | Centralized content descriptor | centralized_content_descriptor | Paragraph type | This should only be used on Centralized content nodes to provide a field level name and description for Centralized Content paragraphs. |
      | Checklist | checklist | Paragraph type |  |
      | Checklist section | checklist_item | Paragraph type |  |
      | Accordion group | collapsible_panel | Paragraph type | A group of accordions. |
      | Accordion Item | collapsible_panel_item | Paragraph type | An individual accordion. |
      | Contact information | contact_information | Paragraph type |  |
      | Link to file or video | downloadable_file | Paragraph type | A download link for an image or document, or a link to a YouTube video. |
      | Email address | email_contact | Paragraph type |  |
      | Embedded Video | embedded_video | Paragraph type | A component to add embedded videos to articles |
      | Expandable Text | expandable_text | Paragraph type | Text that expands upon click. |
      | Featured content | featured_content | Paragraph type |  |
      | VAMC facility service (non-healthcare service) | health_care_local_facility_servi | Paragraph type | A service available at a specific health care facility, like Parking, or Chaplaincy. |
      | Link teaser | link_teaser | Paragraph type | A link followed by a description. For building inline "menus" of content. |
      | Link teaser with image | link_teaser_with_image | Paragraph type |  |
      | List of link teasers | list_of_link_teasers | Paragraph type | A paragraph that contains only one type of paragraph: Link teaser. |
      | List of links | list_of_links | Paragraph type | A set of links, with link text and URL required, and an optional header. |
      | Lists of links | lists_of_links | Paragraph type | WARNING: Resources and support and Knowledge Base only! A list of links, or several lists of links, with an optional section header. |
      | Embedded image | media | Paragraph type | For adding an image inline |
      | Media list - Images | media_list_images | Paragraph type |  |
      | Media list - Videos | media_list_videos | Paragraph type |  |
      | Non-reusable Alert | non_reusable_alert | Paragraph type |  |
      | Number callout | number_callout | Paragraph type | Number callouts can be used in the context of a question & answer, where the answer can be summarized in a short phrase that is number-oriented. |
      | Phone number | phone_number | Paragraph type |  |
      | Process list | process | Paragraph type | An ordered list (1, 2, 3, 4, N) of steps in a process. |
      | Q&A | q_a | Paragraph type | Question and Answer |
      | Q&A group | q_a_group | Paragraph type | Create a new group of existing Q&As. Use this (instead of Rich text) for better accessibility and easy rearranging. |
      | Q&A Section | q_a_section | Paragraph type | For content formatted as a series of questions and answers. Use this (instead of Rich text) for better accessibility and easy rearranging. |
      | React Widget | react_widget | Paragraph type | Advanced editors can use this to place react widgets (like a form) on the page. |
      | Rich text - char limit 1000 | rich_text_char_limit_1000 | Paragraph type | An open-ended text field that uses "Rich Text Limited" with a character limit 1000. |
      | Service location | service_location | Paragraph type |  |
      | Service location address | service_location_address | Paragraph type |  |
      | Situation update | situation_update | Paragraph type | A time-sensitive, added to a banner alert, and displayed on VAMC operating status pages. |
      | Additional information | spanish_translation_summary | Paragraph type | Text that expands to display additional information upon click. |
      | Staff profile | staff_profile | Paragraph type | Add a profile of a staff person. |
      | Step | step | Paragraph type | Single step. |
      | Step by step | step_by_step | Paragraph type | An ordered list (1, 2, 3, 4, N) of steps. |
      | Table | table | Paragraph type | Add an HTML table with rows and columns. |
      | Rich text | wysiwyg | Paragraph type | An open-ended text field. |
      | Sections | administration | Vocabulary | Represents a hierarchy of the VA, partly for governance purposes. |
      | Audience - Beneficiaries | audience_beneficiaries | Vocabulary |  |
      | Audience - Non-beneficiaries | audience_non_beneficiaries | Vocabulary |  |
      | Facility Supplemental Status | facility_supplemental_status | Vocabulary | Supplemental statuses to be used with facilities. |
      | VA Services | health_care_service_taxonomy | Vocabulary | Single source of truth for health service names, descriptions, patient-friendly names, and common conditions. |
      | Resources and support Categories | lc_categories | Vocabulary |  |
      | Products | products | Vocabulary |  |
      | Topics | topics | Vocabulary |  |
      | Type of Redirect | type_of_redirect | Vocabulary |  |

