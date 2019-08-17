@api
Feature: Content model
  In order to enter structured content into my site
  As a content editor
  I want to have content entity types that reflect my content model.

  @spec @content_type
     Scenario: Bundles
       Then exactly the following content entity type bundles should exist
       | Name | Machine name | Type | Description |
| Benefits detail page | page | Content type | These pages hold all of the benefits overview content, such the detail pages linked to from va.gov/disability, va.gov/health-care, and va.gov/education. |
| Benefits hub landing page | landing_page | Content type | A special page for top-level Benefits content with its own one-off layout and content. |
| Support Service | support_service | Content type | Help desks, hotlines, etc, to be contextually placed alongside relevant content. |
| Document | document | Media type | A locally hosted document, such as a PDF. |
| Image | image | Media type | Locally hosted images. |
| Video | video | Media type | A video hosted by YouTube, Vimeo, or some other provider. |
| Accordion group | collapsible_panel | Paragraph type | A group of accordions. |
| Accordion Item | collapsible_panel_item | Paragraph type | An individual accordion.  |
| Alert | alert | Paragraph type | A reusable or non-reusable alert, either "information status" or "warning status". |
| Expandable Text | expandable_text | Paragraph type | Text that expands upon click. |
| Link teaser | link_teaser | Paragraph type | A link followed by a description. For building inline "menus" of content. |
| List of link teasers | list_of_link_teasers  | Paragraph type | A paragraph that contains only one type of paragraph: Link teaser.   |
| Process list | process | Paragraph type | An ordered list (1, 2, 3, 4, N) of steps in a process. |
| Q&A | q_a | Paragraph type | Question and Answer |
| Q&A Section | q_a_section | Paragraph type | For content formatted as a series of questions and answers. Use this (instead of WYSIWYG) for better accessibility and easy rearranging. |
| Starred Horizontal Rule | starred_horizontal_rule | Paragraph type |  |
| WYSIWYG | wysiwyg | Paragraph type | An open-ended text field. |
| Alert | alert | Custom block type | An alert box that can be added to individual pages. |
| Address | address | Paragraph type | An address block  |
| React Widget | react_widget | Paragraph type | Advanced editors can use this to place react widgets (like a form) on the page. |
| Sections | administration | Vocabulary | Represents a hierarchy of the VA, partly for governance purposes. |
| Promo | promo | Custom block type | Promote a link with an image, title, and description.     |
| Regional Health Service | regional_health_care_service_des | Content type | Each specific healthcare services (like geriatrics, audiology, psychiatry, and so on) has a description written at the national level. When regional healthcare systems need to add regional info the default description, they do it here. |
| Number callout | number_callout | Paragraph type | Number callouts can be used in the context of a question & answer, where the answer can be summarized in a short phrase that is number-oriented. |
| Health Care Facility | health_care_local_facility | Content type | Specific facilities, like clinics or hospitals, within a healthcare system. |
| Health Care Local Facility Service | health_care_local_facility_servi | Paragraph type | A service available at a specific health care facility. |
| Health Care System | health_care_region_page  | Content type | A landing page for a regional health care system. |
| Health Care Service taxonomy  | health_care_service_taxonomy  | Vocabulary | For Clinical Health or Patient & Family & Caregiver services |
| Event | event | Content type | For online or in-person events like support groups, outreach events, public lectures, and more. |
| Publication | outreach_asset | Content type | Contains a document, image, or video, for publication within a Publication library. |
| Staff profile | person_profile | Content type | Profiles of staff members for display in various places around the site. |
| Story | news_story | Content type | Community stories highlight the role of a VA facility, program, or healthcare system in a Veteran's journey. They may be a case study of a specific patient, a description of a new or successful program, or a community-interest story. |
| Detail Page | health_care_region_detail_page | Content type | For static pages where there's not another content type already available.  |
| Office | office | Content type | An office at the VA, which may have contact info, events, news, and a leadership page in some cases. |
| News release | press_release | Content type | Announcements directed at members of the media for the purpose of publicizing newsworthy events/happenings/programs at specific facilities or healthcare systems. |
| Type of Redirect | type_of_redirect | Vocabulary |  |
| Documentation page | documentation_page | Content type | Help pages VA.gov CMS editors.  |
| Local Health Service | health_care_local_health_service | Content type | A facility specific description of a health care service, always embedded within a regional description |
| Additional information | spanish_translation_summary | Paragraph type | Spanish summary to include a brief spanish-language summary of the content. |
| Embedded image | media | Paragraph type | For adding an image inline |
| Megamenu | megamenu | Custom block type | A pane within the main nav megamenu |
| Megamenu - Links Column | megamenu_links_column | Paragraph type | First or second column in a megamenu pane - contains a list of links. |
| Megamenu - Menu Item | megamenu_menu_item | Paragraph type | A menu item displayed in a mega menu pane. Contains title, two columns of links, 1 column containing a block, and a 'see all' link. |
| Table | table | Paragraph type | Add an HTML table with rows and columns. |
| Event listing | event_listing | Content type | A listing of events.  |
| Link to file or video | downloadable_file | Paragraph type | For image or document downloads. |
| Publication listing  | publication_listing | Content type | This allows the listing of publication materials such as documents, videos, and images all in one place. |
| Staff profile  | staff_profile | Paragraph type | Add a profile of a staff person.  |

  @spec @field_type
     Scenario: Fields
       Then exactly the following fields should exist
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Paragraph type | Accordion group | Add border around items   | field_collapsible_panel_bordered | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Allow more than one item to expand at a time  | field_collapsible_panel_multi  | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Collapsible Panel Items | field_va_paragraphs | Entity reference revisions | Required | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Accordion group | Start expanded | field_collapsible_panel_expand   | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion Item | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Accordion Item | Title | field_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Paragraph type | Address | Address | field_address | Address |  | 1 | Address |  |
| Paragraph type | Alert | Alert Frequency | field_alert_frequency | List (text) | Required | 1 | Select list |  |
| Paragraph type | Alert | Alert Scope | field_node_reference | Entity reference |  | Unlimited | Autocomplete |  |
| Paragraph type | Alert | Alert Type | field_alert_type | List (text) | Required | 1 | Select list |  |
| Paragraph type | Alert | Crisis Alert Answer  | field_alert_content | Entity reference revisions | Required | 1 | Paragraphs Classic |  |
| Paragraph type | Alert | Crisis Alert Question | field_alert_title | Text (plain) | Required | 1 | Textfield |  |
| Paragraph type | Alert | Crisis alerts look like | field_content_preview  | Markup |  | 1 | Markup |  |
| Paragraph type | Alert | General help text  | field_help_text | Markup |  | 1 | Markup |  |
| Paragraph type | Alert | Owner | field_owner | Entity reference | Required | 1 | Client-side hierarchical select |  |
| Paragraph type | Alert | Reusability | field_reusability | List (text) | Required | 1 | Check boxes/radio buttons |  |
| Paragraph type | Alert Paragraph | Alert Heading | field_alert_heading | Text (plain) | Required | 1 | Textfield |  |
| Paragraph type | Alert Paragraph | Alert Message | field_alert_message | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Paragraph type | Alert Paragraph | Alert Type | field_alert_type | List (text) | Required | 1 | Select list |  |
| Paragraph type | Alert Paragraph | Trigger Text | field_alert_trigger_text | Text (plain) |  | 1 | Textfield |  |
| Content type | Benefits detail page | Description | field_description | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits detail page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits detail page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
| Content type | Benefits detail page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits detail page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form |  |
| Content type | Benefits detail page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits detail page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Benefits detail page | Page last built | field_page_last_built | Date |  | 1 | -- Disabled -- |  |
| Content type | Benefits detail page | Plain Language Certification Date | field_plainlanguage_date | Date |  | 1 | Date and time |  |
| Content type | Benefits detail page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits hub landing page | Administration | field_administration | Entity reference | Required | 1 | Select list |  |
| Content type | Benefits hub landing page | Alert | field_alert | Entity reference |  | 3 | Autocomplete | Translatable |
| Content type | Benefits hub landing page | Description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits hub landing page | Hub Icon | field_title_icon | List (text) |  | 1 | Select list |  |
| Content type | Benefits hub landing page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Benefits hub landing page | Links for non-veterans | field_links | Link |  | Unlimited | Link |  |
| Content type | Benefits hub landing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Benefits hub landing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits hub landing page | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits hub landing page | Plain language Certified Date | field_plainlanguage_date | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits hub landing page | Promo | field_promo | Entity reference |  | 1 | Select list |  |
| Content type | Benefits hub landing page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Benefits hub landing page | Spokes | field_spokes | Entity reference revisions | Required | 4 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits hub landing page | Support Services | field_support_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Content type | Benefits page | Alert | field_alert | Entity reference |  | 3 | Select list |  |
| Content type | Benefits page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | Benefits page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form |  |
| Content type | Benefits page | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Benefits page | Page last built  | field_page_last_built  | Date |  | 1 | -- Disabled -- |  |
| Content type | Benefits page | Plain Language Certification Date      | field_plainlanguage_date  | Date |  | 1 | Date and time |  |
| Content type | Benefits page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits page | Summary | field_description | Text (plain) | Required | 1 | Textfield |  |
| Media type | Document | Document | field_document | File | Required | 1 | File |  |
| Media type | Document | Show in media library | field_media_in_library | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Documentation page | Content block | field_content_block | Entity reference |  | Unlimited | Paragraphs Classic |  |
| Content type | Event | A brief (ideally one sentence) summary of the event | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Event | A human-readable label for the event location. | field_location_humanreadable | Text (plain) |  | 1 | Textfield |  |
| Content type | Event | Additional information about registration | field_additional_information_abo | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Address | field_address | Address |  | 1 | Address |  |
| Content type | Event | Cost | field_event_cost | Text (plain) |  | 1 | Textfield |  |
| Content type | Event | End date and time of the event  | field_event_date_end | Date |  | 1 | Date and time |  |
| Content type | Event | Facility location | field_facility_location | Entity reference |  | 1 | Select list |  |
| Content type | Event | Full event description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Event | Location type | field_location_type | List (text) |  | 1 | Select list |  |
| Content type | Event | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Event | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Event | Registration required | field_event_registrationrequired | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Event | Related office or health care region | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Event | Start date and time of the event  | field_event_date | Date | Required | 1 | Date and time |  |
| Content type | Event | URL Link Label | field_event_cta | List (text) |  | 1 | Select list |  |
| Content type | Event | URL of an external page or registration link for this event   | field_link | Link |  | 1 | Link | Translatable |
| Content type | Event | URL of an online event  | field_url_of_an_online_event  | Link |  | 1 | Link |  |
| Paragraph type | Expandable Text | Full Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Expandable Text | Text Expander | field_text_expander | Text (plain) | Required | 1 | Textfield |  |
| Content type | Health Care Local Facility | Facility Locator API ID  | field_facility_locator_api_id  | Text (plain) | Required | 1 | Textfield |  |
| Content type | Health Care Local Facility | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Health Care Local Facility | Introductory Description  | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Health Care Local Facility | Location services | field_location_services | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
| Content type | Health Care Local Facility | Main location | field_main_location | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Health Care Local Facility | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Health Care Local Facility | Nickname for this facility | field_nickname_for_this_facility | Text (plain) |  | 1 | Textfield |  |
| Content type | Health Care Local Facility | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Health Care Local Facility | Region page | field_region_page | Entity reference | Required | 1 | Select list |  |
| Paragraph type | Health Care Local Facility Service | Service name | field_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Paragraph type | Health Care Local Facility Service | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Health Care Region Detail Page | Downloads | field_media | Entity reference |  | 1 | Entity browser | Translatable |
| Paragraph type | Health Care Region Detail Page | Main content | field_content_block | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Health Care Region Detail Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Paragraph type | Health Care Region Detail Page | Related health care region  | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Paragraph type | Health Care Region Detail Page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Health Care Region Detail Page | Summary | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | Health Care Region Landing Page  | Appointments can be scheduled and viewed online | field_appointments_online | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Health Care Region Landing Page  | Banner image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Paragraph type | Health Care Region Landing Page  | Clinical Health Services  | field_clinical_health_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Paragraph type | Health Care Region Landing Page  | Common Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Health Care Region Landing Page  | Community stories intro text | field_intro_text_news_stories | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Events page intro text   | field_intro_text_events_page | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Health services intro text  | field_clinical_health_care_servi | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Health Care Region Landing Page  | Leadership | field_leadership | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Paragraph type | Health Care Region Landing Page  | Our Locations intro text  | field_locations_intro_blurb  | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Paragraph type | Health Care Region Landing Page  | Patient & Family Services  | field_patient_family_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Paragraph type | Health Care Region Landing Page  | Patient & Family Services intro text  | field_patient_family_services_in | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Health Care Region Landing Page  | Short name | field_nickname_for_this_facility  | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | Health Care Region Landing Page  | Social media links  | field_social_media_links | Social Media Links Field  |  | 1 | List with all available platforms |  |
| Paragraph type | Health Care Region Landing Page  | URL for signing up for email news and announcements | field_email_subscription_links | Link |  | 1 | Link |  |
| Paragraph type | Health Care Region Landing Page  | URL for signing up for emergency email alerts  | field_sign_up_for_emergency_emai | Link |  | 1 | Link |  |
| Paragraph type | Health Care Regional Service Description | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Paragraph type | Health Care Regional Service Description | Region page | field_region_page | Entity reference | Required | 1 | Select list | Translatable |
| Paragraph type | Health Care Regional Service Description | Regional description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Health Care Regional Service Description | Service location | field_service_location | Entity reference | Required | Unlimited | Check boxes/radio buttons |  |
| Paragraph type | Health Care Regional Service Description | Service name and description | field_service_name_and_descripti | Entity reference | Required | 1 | Select list |  |
| Vocabulary | Health Care Service taxonomy  | Also known as | field_also_known_as | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Health Care Service taxonomy  | Owner | field_owner | Entity reference | Required | 1 | Client-side hierarchical select |  |
| Vocabulary | Health Care Service taxonomy  | Type of service (for clinical services)  | field_service_type_clinical  | List (text) |  | 1 | Select list |  |
| Vocabulary | Health Care Service taxonomy  | Type of service (for non-clinical services)  | field_service_type_nonclinical  | List (text) |  | 1 | Select list |  |
| Content type | Hub landing page | Administration  | field_administration | Entity reference | Required | 1 | Select list |  |
| Content type | Hub landing page | Alert | field_alert | Entity reference |  | 1 | Select list | Translatable |
| Content type | Hub landing page | Description | field_description | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Hub landing page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Hub landing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Hub landing page | Page last built  | field_page_last_built  | Date |  | 1 | Date and time | Translatable |
| Content type | Hub landing page | Plain language Certified Date | field_plainlanguage_date | Date |  | 1 | Date and time | Translatable |
| Content type | Hub landing page | Promo | field_promo | Entity reference |  | 1 | Select list |  |
| Content type | Hub landing page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Hub landing page | Spokes | field_spokes | Entity reference revisions | Required | 4 | Paragraphs EXPERIMENTAL |  |
| Content type | Hub landing page | Support Services | field_support_services  | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Media type | Image | Image | image | Image | Required | 1 | ImageWidget crop |  |
| Media type | Image | Image caption | field_image_caption | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Media type | Image | Show in media library | field_media_in_library | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Link teaser | Link | field_link | Link |  | 1 | Link |  |
| Paragraph type | Link teaser | Link summary | field_link_summary  | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | List of link teasers | Link teasers | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | List of link teasers | Title | field_title | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | News story | Author byline | field_author | Entity reference |  | 1 | Inline entity form - Complex |  |
| Content type | News story | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | News story | Full text of Story | field_full_story | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | News story | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | News story | Image caption | field_image_caption | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | News story | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | News story | Link to the original story, if it’s not internal. | field_link | Link |  | 1 | -- Disabled -- | Translatable |
| Content type | News story | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | News story | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | News story | Related office or health care region | field_office | Entity reference | Required | 1 | Select list |  |
| Paragraph type | Number callout | Additional information | field_wysiwyg | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Number callout | Short phrase with a number, or time element   | field_short_phrase_with_a_number | Text (plain) | Required | 1 | Textfield |  |
| Content type | Office | Body | body | Text (formatted, long, with summary) |  | 1 | Text area with a summary | Translatable |
| Content type | Outreach asset  | A brief (ideally one sentence) summary of this asset | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Outreach asset  | File or video | field_media | Entity reference |  | 1 | Inline entity form - Complex |  |
| Content type | Outreach asset  | Format | field_format | List (text) | Required | 1 | Select list |  |
| Content type | Outreach asset  | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Outreach asset  | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Outreach asset  | Related Benefits | field_benefits | List (text) | Required | Unlimited | Check boxes/radio buttons |  |
| Content type | Person profile | Bio | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Person profile | Email address | field_email_address | Email |  | 1 | Email |  |
| Content type | Person profile | First name | field_name_first | Text (plain) |  | 1 | Textfield |  |
| Content type | Person profile | High-resolution photo should be available for download by site visitors | field_photo_allow_hires_download | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Person profile | Introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Person profile | Job Title | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Person profile | Last name | field_last_name | Text (plain) |  | 1 | Textfield |  |
| Content type | Person profile | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Person profile | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Person profile | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Person profile | Photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Person profile | Related office or health care region    | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Person profile | Suffix | field_suffix | Text (plain) |  | 1 | Textfield |  |
| Content type | Press release | Full text of the Press Release  | field_press_release_fulltext | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | Press release | Healthcare system or related office  | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Press release | Introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Press release | Location | field_address | Address | Required | 1 | Address | Translatable |
| Content type | Press release | Media assets | field_press_release_downloads | Entity reference |  | Unlimited | Media library |  |
| Content type | Press release | Media Contact(s) | field_press_release_contact | Entity reference |  | Unlimited | Inline entity form - Complex | Translatable |
| Content type | Press release | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Press release | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Press release | PDF of Press Release | field_pdf_version | Entity reference |  | 1 | Media library |  |
| Content type | Press release | Release date | field_release_date | Date |  | 1 | Date and time |  |
| Content type | Process | Steps | field_steps | Text (formatted, long) | Required | Unlimited | Text area (multiple rows) |  |
| Custom block type | Promo | Image | field_image | Entity reference | Required | 1 | Entity browser |  |
| Custom block type | Promo | Link | field_promo_link | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Custom block type | Promo | Owner | field_owner | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Paragraph type | Q&A | Answer | field_answer | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A | Question | field_question | Text (plain) | Required | 1 | Textfield |  |
| Paragraph type | Q&A Section | Display this set of Q&As as a group of accordions.  | field_accordion_display | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Q&A Section | Questions | field_questions | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A Section | Section Header | field_section_header | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | Q&A Section | Section Intro | field_section_intro | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | React Widget | Call To Action Widget | field_cta_widget | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Default Link | field_default_link | Link |  | 1 | Link |  |
| Paragraph type | React Widget | Display default link as button | field_button_format | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Error Message | field_error_message | Text (formatted) |  | 1 | Text field |  |
| Paragraph type | React Widget | Loading Message | field_loading_message | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | React Widget | Timeout | field_timeout | Number (integer) |  | 1 | Number field |  |
| Paragraph type | React Widget | Widget Type | field_widget_type | Text (plain) | Required | 1 | Textfield |  |
| Vocabulary | Sections | Acronym | field_acronym | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Description | field_description | Text (plain) | Required | 1 | Textfield |  |
| Vocabulary | Sections | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) |  |
| Vocabulary | Sections | Link | field_link | Link |  | 1 | Link |  |
| Vocabulary | Sections | Link text | field_email_updates_link_text  | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Metatags | field_metatags | Meta tags |  | 1 | Advanced meta tags form |  |
| Vocabulary | Sections | Social media links | field_social_media_links | Social Media Links Field  |  | 1 | List with all available platforms |  |
| Vocabulary | Sections | URL | field_email_updates_url  | Text (plain) |  | 1 | Textfield |  |
| Custom block type | Spanish summary   | Instructions for obtaining more information in Spanish  | field_text_expander | Text (plain) |  | 1 | Textfield | Translatable |
| Custom block type | Spanish summary   | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Support Service | Link | field_link | Link |  | 1 | Link |  |
| Content type | Support Service | Owner | field_administration | Entity reference | Required | 1 | Client-side hierarchical select | Translatable |
| Content type | Support Service | Page last built  | field_page_last_built  | Date |  | 1 | Date and time | Translatable |
| Content type | Support Service | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number |  |
| Content type | Support Service | Related office | field_office | Entity reference | Required | 1 | Autocomplete | Translatable |
| Media type | Video | Show in media library | field_media_in_library | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Media type | Video | Show in media library | field_media_in_library | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Media type | Video | Video URL | field_media_video_embed_field | Video Embed | Required | 1 | Video Textfield | Translatable |
| Paragraph type | WYSIWYG | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
