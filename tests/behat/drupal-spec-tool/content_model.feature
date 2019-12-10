@api
Feature: Content model
  In order to enter structured content into my site
  As a content editor
  I want to have content entity types that reflect my content model.

  @dst @content_type
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
| VAMC system | health_care_region_page | Content type | A landing page for a VAMC system, which typically includes multiple facilities. |
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

  @dst @field_type
     Scenario: Fields
       Then exactly the following fields should exist
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Benefits detail page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Benefits detail page | Alert | field_alert | Entity reference |  | 1 | Select list |  |
| Content type | Benefits detail page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL |  |
| Content type | Benefits detail page | Description | field_description | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits detail page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits detail page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
| Content type | Benefits detail page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form |  |
| Content type | Benefits detail page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits detail page | Page last built | field_page_last_built | Date |  | 1 | -- Disabled -- |  |
| Content type | Benefits detail page | Plain Language Certification Date | field_plainlanguage_date | Date |  | 1 | Date and time |  |
| Content type | Benefits detail page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits hub landing page | Owner | field_administration | Entity reference | Required | 1 | Select list |  |
| Content type | Benefits hub landing page | Alert | field_alert | Entity reference |  | 1 | Select list | Translatable |
| Content type | Benefits hub landing page | Description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits hub landing page | Home page hub label | field_home_page_hub_label | Text (plain) |  | 1 | Textfield |  |
| Content type | Benefits hub landing page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Benefits hub landing page | Links for non-veterans | field_links | Link |  | Unlimited | Linkit |  |
| Content type | Benefits hub landing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Benefits hub landing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits hub landing page | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits hub landing page | Plain language Certified Date | field_plainlanguage_date | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits hub landing page | Promo | field_promo | Entity reference |  | 1 | Select list |  |
| Content type | Benefits hub landing page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Benefits hub landing page | Spokes | field_spokes | Entity reference revisions | Required | 4 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits hub landing page | Support Services | field_support_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Content type | Benefits hub landing page | Teaser text | field_teaser_text | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Benefits hub landing page | Hub Icon | field_title_icon | List (text) |  | 1 | Select list |  |
| Content type | Detail Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Detail Page | Alert | field_alert | Entity reference |  | 1 | Select list | Translatable |
| Content type | Detail Page | Main content | field_content_block | Entity reference revisions |  | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
| Content type | Detail Page | Summary | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Detail Page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Detail Page | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Detail Page | Downloads | field_media | Entity reference |  | 1 | Entity browser | Translatable |
| Content type | Detail Page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Detail Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Detail Page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Detail Page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Detail Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Documentation page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Documentation page | Main content | field_content_block | Entity reference revisions |  | Unlimited | Paragraphs Browser Classic | Translatable |
| Content type | Event | Additional information about registration | field_additional_information_abo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Address | field_address | Address |  | 1 | Address |  |
| Content type | Event | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Event | Full event description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Date and time | field_date | Date range |  | 1 | Date and time range |  |
| Content type | Event | A brief (ideally one sentence) summary of the event | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Event | Cost | field_event_cost | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Event | URL Link Label | field_event_cta | List (text) |  | 1 | Select list |  |
| Content type | Event | Registration required | field_event_registrationrequired | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Event | Facility location | field_facility_location | Entity reference |  | 1 | Select list |  |
| Content type | Event | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Event | URL of an external page or registration link for this event | field_link | Link |  | 1 | Link | Translatable |
| Content type | Event | A human-readable label for the event location. | field_location_humanreadable | Text (plain) |  | 1 | Textfield |  |
| Content type | Event | Location type | field_location_type | List (text) |  | 1 | Select list |  |
| Content type | Event | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Event | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Event | Event listing | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Event | Order | field_order | List (integer) |  | 1 | Select list |  |
| Content type | Event | URL of an online event | field_url_of_an_online_event | Link |  | 1 | Linkit |  |
| Content type | Event listing | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Event listing | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Event listing | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Event listing | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Event listing | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Event listing | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News release | Location | field_address | Address | Required | 1 | Address | Translatable |
| Content type | News release | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News release | Introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | News release | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | News release | Healthcare system or related office | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News release | PDF of Press Release | field_pdf_version | Entity reference |  | 1 | Media library |  |
| Content type | News release | Media Contact(s) | field_press_release_contact | Entity reference |  | Unlimited | Autocomplete | Translatable |
| Content type | News release | Media assets | field_press_release_downloads | Entity reference |  | Unlimited | Media library |  |
| Content type | News release | Full text of the Press Release | field_press_release_fulltext | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | News release | Release date | field_release_date | Date |  | 1 | Date and time |  |
| Content type | Office | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Office | Body | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Office | Description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Office | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Office | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Publication | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication | Related Benefits | field_benefits | List (text) |  | 1 | Select list |  |
| Content type | Publication | A brief (ideally one sentence) summary of this asset | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Publication | Format | field_format | List (text) | Required | 1 | Select list |  |
| Content type | Publication | File or video | field_media | Entity reference |  | 1 | Media library |  |
| Content type | Publication | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Publication | Healthcare system or related office | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication listing | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Publication listing | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Publication listing | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Publication listing | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Publication listing | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Publication listing | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff profile | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff profile | Bio | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Staff profile | Complete Biography | field_complete_biography | File |  | 1 | File |  |
| Content type | Staff profile | Job Title | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Staff profile | Email address | field_email_address | Email |  | 1 | Email |  |
| Content type | Staff profile | Introduction | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Staff profile | Last name | field_last_name | Text (plain) |  | 1 | Textfield |  |
| Content type | Staff profile | Photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Staff profile | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Staff profile | First name | field_name_first | Text (plain) |  | 1 | Textfield |  |
| Content type | Staff profile | Related office or health care region | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff profile | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Staff profile | High-resolution photo should be available for download by site visitors | field_photo_allow_hires_download | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Staff profile | Suffix | field_suffix | Text (plain) |  | 1 | Textfield |  |
| Content type | Story | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Story | Author byline | field_author | Entity reference |  | 1 | Autocomplete |  |
| Content type | Story | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Story | Full text of Story | field_full_story | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Story | Image caption | field_image_caption | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | Story | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Story | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Story | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Story | Related office or health care region | field_office | Entity reference | Required | 1 | Select list |  |
| Content type | Story | Order | field_order | List (integer) |  | 1 | Select list | Translatable |
| Content type | Support Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Support Service | Link | field_link | Link |  | 1 | Link |  |
| Content type | Support Service | Related office | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Support Service | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
| Content type | Support Service | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number |  |
| Content type | VAMC facility | Address | field_address | Address |  | 1 | Address | Translatable |
| Content type | VAMC facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC facility | Description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC facility | Email Subscription | field_email_subscription | Link |  | 1 | Linkit | Translatable |
| Content type | VAMC facility | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
| Content type | VAMC facility | Classification | field_facility_classification | List (text) |  | 1 | Select list |  |
| Content type | VAMC facility | Hours | field_facility_hours  | Table Field |  | 1 | Table Field |  |
| Content type | VAMC facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield |  |
| Content type | VAMC facility | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
| Content type | VAMC facility | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
| Content type | VAMC facility | Intro text | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VAMC facility | Local health care service offerings | field_local_health_care_service_ | Entity reference |  | Unlimited | -- Disabled -- | Translatable |
| Content type | VAMC facility | Location services | field_location_services | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
| Content type | VAMC facility | Main location | field_main_location | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC facility | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | VAMC facility | Mental Health Phone | field_mental_health_phone | Telephone number |  | 1 | Telephone number |  |
| Content type | VAMC facility | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | VAMC facility | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC facility | Nickname for this facility | field_nickname_for_this_facility | Text (plain) | Required | 1 | Textfield |  |
| Content type | VAMC facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list |  |
| Content type | VAMC facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC facility | Phone Number | field_phone_number  | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | VAMC facility | Region page | field_region_page | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC facility | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
| Content type | VAMC facility health service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC facility health service | Facility description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC facility health service | Facility | field_facility_location | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC facility health service | VAMC system health service | field_regional_health_service | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC system | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system | Appointments can be scheduled and viewed online | field_appointments_online | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system | Health services intro text | field_clinical_health_care_servi | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC system | Regional Health Service Offerings. | field_clinical_health_services | Entity reference |  | Unlimited | Select list |  |
| Content type | VAMC system | Description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC system | Email Subscription | field_email_subscription | Link |  | 1 | -- Disabled -- | Translatable |
| Content type | VAMC system | URL for signing up for email news and announcements | field_email_subscription_links | Link |  | 1 | -- Disabled -- |  |
| Content type | VAMC system | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
| Content type | VAMC system | Featured content on health-services page | field_featured_content_healthser | Entity reference revisions |  | 3 | Paragraphs Classic |  |
| Content type | VAMC system | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
| Content type | VAMC system | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
| Content type | VAMC system | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VAMC system | Events page intro text | field_intro_text_events_page | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC system | Leadership page intro text | field_intro_text_leadership | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC system | Community stories intro text | field_intro_text_news_stories | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC system | Press releases intro text | field_intro_text_press_releases | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC system | Leadership team | field_leadership | Entity reference |  | Unlimited | Autocomplete |  |
| Content type | VAMC system | Emergency updates email list               | field_link_facility_emerg_list | Link         |          | 1 | -- Disabled -- |  |
| Content type | VAMC system | News and announcements email list          | field_link_facility_news_list  | Link         |          | 1 | -- Disabled -- |  |
| Content type | VAMC system | GovDelivery ID for News and Announcements  | field_govdelivery_id_news      | Text (plain) | Required | 1 | Textfield      |  |
| Content type | VAMC system | GovDelivery ID for Emergency updates email | field_govdelivery_id_emerg     | Text (plain) | Required | 1 | Textfield      |  |
| Content type | VAMC system | Email lists | field_links | Link |  | Unlimited | -- Disabled -- | Translatable |
| Content type | VAMC system | Our Locations intro text | field_locations_intro_blurb | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | VAMC system | Banner image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | VAMC system | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | VAMC system | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC system | Short name | field_nickname_for_this_facility | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | VAMC system | Operating status | field_operating_status | Link |  | 1 | Linkit |  |
| Content type | VAMC system | Other VA Locations | field_other_va_locations | Text (plain) |  | Unlimited | Textfield |  |
| Content type | VAMC system | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC system | Common Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | VAMC system | URL for signing up for emergency email alerts | field_sign_up_for_emergency_emai | Link |  | 1 | -- Disabled -- |  |
| Content type | VAMC system | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
| Content type | VAMC system banner alert with situation updates | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system banner alert with situation updates | Alert dismissable? | field_alert_dismissable | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system banner alert with situation updates | Display "Subscribe to email updates" link? | field_alert_email_updates_button | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system banner alert with situation updates | Display "Find other VA facilities near you" link? | field_alert_find_facilities_cta | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system banner alert with situation updates | Only show on VAMC System Page & Operating Status Page | field_alert_inheritance_subpages | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system banner alert with situation updates | Computed values for alerts | field_banner_alert_computdvalues | Computed (text, long) |  | 1 | -- Disabled -- |  |
| Content type | VAMC system banner alert with situation updates | Display "Get updates on affected services and facilities" link | field_alert_operating_status_cta | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC system banner alert with situation updates | Alert type | field_alert_type | List (text) | Required | 1 | Select list |  |
| Content type | VAMC system banner alert with situation updates | Any information that is additional to any time-sensitive situation updates | field_banner_alert_situationinfo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC system banner alert with situation updates | VAMC system(s) | field_banner_alert_vamcs | Entity reference | Required | Unlimited | Check boxes/radio buttons |  |
| Content type | VAMC system banner alert with situation updates | Alert body | field_body | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC system banner alert with situation updates | Send email to subscribers via GovDelivery? | field_operating_status_sendemail | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | VAMC system banner alert with situation updates | Situation updates | field_situation_updates | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | VAMC system health service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system health service | VAMC system description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC system health service | Facility-specific descriptions of this service | field_local_health_care_service_ | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC system health service | VAMC system | field_region_page | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system health service | Service name and description | field_service_name_and_descripti | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC system operating status | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system operating status | Banner alert and situation updates | field_banner_alert | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC system operating status | Update individual facility statuses | field_facility_operating_status | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC system operating status | Links | field_links | Link |  | Unlimited | Link | Translatable |
| Content type | VAMC system operating status | Meta tags | field_meta_tags | Meta tags |  | 1 | -- Disabled -- | Translatable |
| Content type | VAMC system operating status | VAMC system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC system operating status | Emergency information | field_operating_status_emerg_inf | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Custom block type | Alert | Alert body | field_alert_content | Entity reference revisions | Required | 1 | Paragraphs Classic |  |
| Custom block type | Alert | Alert dismissable? | field_alert_dismissable | Boolean |  | 1 | Single on/off checkbox |  |
| Custom block type | Alert | Persistence (for dismissable alerts only) | field_alert_frequency | List (text) | Required | 1 | Select list |  |
| Custom block type | Alert | Alert title | field_alert_title | Text (plain) | Required | 1 | Textfield |  |
| Custom block type | Alert | Alert Type | field_alert_type | List (text) | Required | 1 | Select list |  |
| Custom block type | Alert | Banner or in-page alert? | field_is_this_a_header_alert_ | List (text) |  | 1 | Select list |  |
| Custom block type | Alert | Scope | field_node_reference | Entity reference |  | Unlimited | Autocomplete |  |
| Custom block type | Alert | Owner | field_owner | Entity reference | Required | 1 | Select list |  |
| Custom block type | Alert | Reusability | field_reusability | List (text) | Required | 1 | -- Disabled -- |  |
| Custom block type | Promo | Image | field_image | Entity reference | Required | 1 | Entity browser |  |
| Custom block type | Promo | Instructions | field_instructions | Markup |  | 1 | Markup |  |
| Custom block type | Promo | Owner | field_owner | Entity reference | Required | 1 | Select list | Translatable |
| Custom block type | Promo | Link | field_promo_link | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Media type | Document | Document | field_document | File | Required | 1 | File |  |
| Media type | Document | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- | Translatable |
| Media type | Document | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup |  |
| Media type | Document | Owner | field_owner | Entity reference | Required | 1 | Select list |  |
| Media type | Image | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- |  |
| Media type | Image | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup | Translatable |
| Media type | Image | Owner | field_owner | Entity reference | Required | 1 | Select list | Translatable |
| Media type | Image | Image | image | Image | Required | 1 | ImageWidget crop |  |
| Media type | Video | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- | Translatable |
| Media type | Video | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup | Translatable |
| Media type | Video | Video URL | field_media_video_embed_field | Video Embed | Required | 1 | Video Textfield | Translatable |
| Media type | Video | Owner | field_owner | Entity reference | Required | 1 | Select list | Translatable |
| Paragraph type | Accordion group | Add border around items | field_collapsible_panel_bordered | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Start expanded | field_collapsible_panel_expand | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Allow more than one item to expand at a time | field_collapsible_panel_multi | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Accordion Items | field_va_paragraphs | Entity reference revisions | Required | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Accordion Item | Title | field_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | Accordion Item | Content block(s) | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Accordion Item | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Additional information | Instructions for obtaining more information in Spanish | field_text_expander | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Additional information | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Address | Address | field_address | Address |  | 1 | Address |  |
| Paragraph type | Alert | Reusable alert | field_alert_block_reference | Entity reference |  | 1 | Select list |  |
| Paragraph type | Alert | Alert Heading | field_alert_heading | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Alert | Alert Type | field_alert_type | List (text) |  | 1 | Select list |  |
| Paragraph type | Alert | Alert content | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Embedded image | Allow clicks on this image to open it in new tab | field_allow_clicks_on_this_image | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Embedded image | Markup | field_markup | Markup |  | 1 | Markup |  |
| Paragraph type | Embedded image | Select an image | field_media | Entity reference |  | 1 | Media library |  |
| Paragraph type | Expandable Text | Text Expander | field_text_expander | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Expandable Text | Full Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Link teaser | Link | field_link | Link |  | 1 | Linkit |  |
| Paragraph type | Link teaser | Link summary | field_link_summary | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Link to file or video | Markup | field_markup | Markup |  | 1 | Markup | Translatable |
| Paragraph type | Link to file or video | Link to a file or video | field_media | Entity reference | Required | 1 | Media library | Translatable |
| Paragraph type | Link to file or video | Link text | field_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Paragraph type | List of link teasers | Title | field_title | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | List of link teasers | Link teasers | field_va_paragraphs | Entity reference revisions | Required | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Number callout | Short phrase with a number, or time element | field_short_phrase_with_a_number | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Number callout | Additional information | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Process list | Steps | field_steps | Text (formatted, long) | Required | Unlimited | Text area (multiple rows) |  |
| Paragraph type | Q&A | Answer | field_answer | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A | Question | field_question | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Q&A Section | Display this set of Q&As as a group of accordions. | field_accordion_display | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Q&A Section | Questions | field_questions | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A Section | Section Header | field_section_header | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | Q&A Section | Section Intro | field_section_intro | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | React Widget | Display default link as button | field_button_format | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Call To Action Widget | field_cta_widget | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Default Link | field_default_link | Link |  | 1 | Linkit |  |
| Paragraph type | React Widget | Error Message | field_error_message | Text (formatted) |  | 1 | Text field |  |
| Paragraph type | React Widget | Loading Message | field_loading_message | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | React Widget | Timeout | field_timeout | Number (integer) |  | 1 | Number field |  |
| Paragraph type | React Widget | Widget Type | field_widget_type | Text (plain) | Required | 1 | Textfield |  |
| Paragraph type | Situation update | Date and time | field_date_and_time | Date | Required | 1 | Date and time |  |
| Paragraph type | Situation update | Send email to subscribers via GovDelivery? | field_send_email_to_subscribers | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Situation update | Update | field_wysiwyg | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Staff profile | Staff profile | field_staff_profile | Entity reference | Required | 1 | Select list |  |
| Paragraph type | Table | Table | field_table | Table Field | Required | 1 | Table Field |  |
| Paragraph type | VAMC facility service (non-healthcare service) | Service name | field_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | VAMC facility service (non-healthcare service) | Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | WYSIWYG | Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Vocabulary | Sections | Acronym | field_acronym | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Description | field_description | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Link text | field_email_updates_link_text | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | URL | field_email_updates_url | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Intro text | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Vocabulary | Sections | Link | field_link | Link |  | 1 | Linkit |  |
| Vocabulary | Sections | Metatags | field_metatags | Meta tags |  | 1 | Advanced meta tags form |  |
| Vocabulary | Sections | Social media links | field_social_media_links | Social Media Links Field  |  | 1 | List with all available platforms |  |
| Vocabulary | VHA health service taxonomy | Patient-friendly name | field_also_known_as | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VHA health service taxonomy | Common conditions | field_commonly_treated_condition | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VHA health service taxonomy | Health Service API ID | field_health_service_api_id | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VHA health service taxonomy | Owner | field_owner | Entity reference | Required | 1 | Select list |  |
| Vocabulary | VHA health service taxonomy | VHA Stop code | field_vha_healthservice_stopcode | Number (integer) |  | 1 | Number field |  |
