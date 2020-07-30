@api
Feature: Content model: Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
  Scenario: Fields
    Then exactly the following fields should exist for entity type node
      | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
      | Content type | Benefits detail page | Alert | field_alert | Entity reference |  | 1 | Entity browser |  |
      | Content type | Benefits detail page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
      | Content type | Benefits detail page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
      | Content type | Benefits detail page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL |  |
      | Content type | Benefits detail page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter |  |
      | Content type | Benefits detail page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form |  |
      | Content type | Benefits detail page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter |  |
      | Content type | Benefits detail page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Benefits detail page | Page introduction | field_intro_text_limited_html | Text (formatted, long) | Required | 1 | -- Disabled -- |  |
      | Content type | Benefits detail page | Page introduction | field_intro_text  | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
      | Content type | Benefits detail page | Page last built | field_page_last_built | Date |  | 1 | -- Disabled -- |  |
      | Content type | Benefits detail page | Plain Language Certification Date | field_plainlanguage_date | Date |  | 1 | Date and time |  |
      | Content type | Benefits detail page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
      | Content type | Benefits hub landing page | Alert | field_alert | Entity reference |  | 1 | Entity browser | Translatable |
      | Content type | Benefits hub landing page | Hub Icon | field_title_icon | List (text) |  | 1 | Select list |  |
      | Content type | Benefits hub landing page | Hub label | field_home_page_hub_label | Text (plain) |  | 1 | Textfield |  |
      | Content type | Benefits hub landing page | Hub teaser text | field_teaser_text | Text (plain) |  | 1 | Textfield with counter |  |
      | Content type | Benefits hub landing page | Links for non-veterans | field_links | Link |  | Unlimited | Linkit |  |
      | Content type | Benefits hub landing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Benefits hub landing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Benefits hub landing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Benefits hub landing page | Owner | field_administration | Entity reference | Required | 1 | Select list |  |
      | Content type | Benefits hub landing page | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
      | Content type | Benefits hub landing page | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
      | Content type | Benefits hub landing page | Plain language Certified Date | field_plainlanguage_date | Date |  | 1 | Date and time | Translatable |
      | Content type | Benefits hub landing page | Promo | field_promo | Entity reference |  | 1 | Select list |  |
      | Content type | Benefits hub landing page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
      | Content type | Benefits hub landing page | Spokes | field_spokes | Entity reference revisions | Required | 4 | Paragraphs EXPERIMENTAL |  |
      | Content type | Benefits hub landing page | Support Services | field_support_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
      | Content type | Detail Page | Alert | field_alert | Entity reference |  | 1 | Select list | Translatable |
      | Content type | Detail Page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
      | Content type | Detail Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | Detail Page | Main content | field_content_block | Entity reference revisions |  | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
      | Content type | Detail Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Detail Page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Detail Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Detail Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Detail Page | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | Detail Page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
      | Content type | Detail Page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | CMS help page | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
      | Content type | CMS help page | Related user guides | field_related_user_guides | Entity reference |  | 5 | Autocomplete |  |
      | Content type | CMS help page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | -- Disabled -- | Translatable |
      | Content type | CMS help page | Main content | field_content_block | Entity reference revisions |  | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
      | Content type | CMS help page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Event | A human-readable label for the event location. | field_location_humanreadable | Text (plain) |  | 1 | Textfield |  |
      | Content type | Event | Additional information about registration | field_additional_information_abo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | Event | Address | field_address | Address |  | 1 | Address |  |
      | Content type | Event | Cost | field_event_cost | Text (plain) |  | 1 | Textfield with counter |  |
      | Content type | Event | Date and time | field_date | Date range |  | 1 | Date and time range |  |
      | Content type | Event | Event listing | field_listing | Entity reference | Required | 1 | Select list |  |
      | Content type | Event | Facility location | field_facility_location | Entity reference |  | 1 | Select list |  |
      | Content type | Event | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox | Translatable |
      | Content type | Event | Full event description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | Event | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
      | Content type | Event | Location type | field_location_type | List (text) |  | 1 | Select list |  |
      | Content type | Event | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | Event | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Event | Order | field_order | List (integer) |  | 1 | Select list |  |
      | Content type | Event | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Event | Registration required | field_event_registrationrequired | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | Event | URL Link Label | field_event_cta | List (text) |  | 1 | Select list |  |
      | Content type | Event | URL of an external page or registration link for this event | field_link | Link |  | 1 | Link | Translatable |
      | Content type | Event | URL of an online event | field_url_of_an_online_event | Link |  | 1 | Link |  |
      | Content type | Event listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Event listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Event listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Event listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Event listing page | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Event listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Health services listing page | Featured content on health-services page | field_featured_content_healthser | Entity reference revisions |  | 3 | Paragraphs Classic | Translatable |
      | Content type | Health services listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Health services listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Health services listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Health services listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Health services listing page | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Health services listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Leadership listing page | Leadership team | field_leadership | Entity reference |  | Unlimited | Autocomplete | Translatable |
      | Content type | Leadership listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Leadership listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Leadership listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Leadership listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Leadership listing page | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Leadership listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Locations listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Locations listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Locations listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Locations listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Locations listing page | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Locations listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | NCA facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | NCA facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
      | Content type | NCA facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | NCA facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | News release | Full text of the Press Release | field_press_release_fulltext | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
      | Content type | News release | Introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | News release | Location | field_address | Address | Required | 1 | Address | Translatable |
      | Content type | News release | Media assets | field_press_release_downloads | Entity reference |  | Unlimited | Media library |  |
      | Content type | News release | Media Contact(s) | field_press_release_contact | Entity reference |  | Unlimited | Autocomplete | Translatable |
      | Content type | News release | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | News release | News releases listing | field_listing | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | News release | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | News release | PDF of Press Release | field_pdf_version | Entity reference |  | 1 | Media library |  |
      | Content type | News release | Release date | field_release_date | Date |  | 1 | Date and time |  |
      | Content type | News release listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | News release listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | News release listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | News release listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | News release listing page | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | News release listing page | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | News release listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Office | Body | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Office | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | Office | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Office | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Office | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Publication | File or video | field_media | Entity reference |  | 1 | Media library |  |
      | Content type | Publication | Format | field_format | List (text) | Required | 1 | Select list |  |
      | Content type | Publication | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | Publication | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Publication | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Publication | Publication listing | field_listing | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Publication | Related Benefits | field_benefits | List (text) |  | 1 | Select list |  |
      | Content type | Publication listing page | Intro text | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Publication listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Publication listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Publication listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Publication listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Publication listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Q&A | Answer | field_answer | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL | |
      | Content type | Q&A | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Q&A | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Q&A | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Q&A | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Staff profile | Bio | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Staff profile | Complete Biography | field_complete_biography | File |  | 1 | File |  |
      | Content type | Staff profile | Email address | field_email_address | Email |  | 1 | Email |  |
      | Content type | Staff profile | First name | field_name_first | Text (plain) |  | 1 | Textfield |  |
      | Content type | Staff profile | High-resolution photo should be available for download by site visitors | field_photo_allow_hires_download | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | Staff profile | Introduction | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | Staff profile | Last name | field_last_name | Text (plain) |  | 1 | Textfield |  |
      | Content type | Staff profile | Meta description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | Staff profile | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Staff profile | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Staff profile | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
      | Content type | Staff profile | Photo | field_media | Entity reference |  | 1 | Media library | Translatable |
      | Content type | Staff profile | Related office or health care region | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Staff profile | Suffix | field_suffix | Text (plain) |  | 1 | Textfield |  |
      | Content type | Step-by-Step | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs Classic |  |
      | Content type | Step-by-Step | Page introduction | field_intro_text_limited_html | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | Step-by-Step | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Step-by-Step | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Step-by-Step | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Step-by-Step | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | Step-by-Step | Step-by-step | field_steps | Entity reference revisions | Required | 1 | Paragraphs Classic |  |
      | Content type | Story | Author byline | field_author | Entity reference |  | 1 | Autocomplete |  |
      | Content type | Story | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | Story | Full text of Story | field_full_story | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | Story | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
      | Content type | Story | Image caption | field_image_caption | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
      | Content type | Story | Intro text | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | Story | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Story | Order | field_order | List (integer) |  | 1 | Select list | Translatable |
      | Content type | Story | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Story | Story listing | field_listing | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Story listing page | Intro text | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | Story listing page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Story listing page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | Story listing page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | Story listing page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Story listing page | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Support Service | Link | field_link | Link |  | 1 | Link |  |
      | Content type | Support Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Support Service | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
      | Content type | Support Service | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number |  |
      | Content type | Support Service | Related office | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VA form | Alert | field_alert | Entity reference |  | 1 | Entity browser | Translatable |
      | Content type | VA form | Benefits hub | field_benefit_categories | Entity reference |  | Unlimited | Check boxes/radio buttons |  |
      | Content type | VA form | Category | field_va_form_type | List (text) |  | 1 | Select list |  |
      | Content type | VA form | Deleted | field_va_form_deleted | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VA form | Deleted date | field_va_form_deleted_date | Date |  | 1 | Date and time |  |
      | Content type | VA form | Form administration | field_va_form_administration | Entity reference |  | 1 | Select list |  |
      | Content type | VA form | Form name | field_va_form_name | Text (plain) |  | 1 | Textfield |  |
      | Content type | VA form | Form number | field_va_form_number | Text (plain) |  | 1 | Textfield |  |
      | Content type | VA form | Form title | field_va_form_title | Text (plain) |  | 1 | Textfield |  |
      | Content type | VA form | Helpful links | field_va_form_link_teasers | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
      | Content type | VA form | Issue date | field_va_form_issue_date | Date |  | 1 | Date and time |  |
      | Content type | VA form | Link to form | field_va_form_url | Link |  | 1 | Link |  |
      | Content type | VA form | Link to online tool | field_va_form_tool_url | Link |  | 1 | Link |  |
      | Content type | VA form | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | VA form | Number of pages | field_va_form_num_pages | Number (integer) |  | 1 | Number field |  |
      | Content type | VA form | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VA form | Related forms | field_va_form_related_forms | Entity reference |  | Unlimited | Autocomplete |  |
      | Content type | VA form | Revision date | field_va_form_revision_date | Date |  | 1 | Date and time |  |
      | Content type | VA form | Tool intro | field_va_form_tool_intro | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VA form | When to use | field_va_form_usage | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VAMC facility | Address | field_address | Address |  | 1 | Address | Translatable |
      | Content type | VAMC facility | Classification | field_facility_classification | List (text) |  | 1 | Select list |  |
      | Content type | VAMC facility | Email Subscription | field_email_subscription | Link |  | 1 | Linkit | Translatable |
      | Content type | VAMC facility | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
      | Content type | VAMC facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield |  |
      | Content type | VAMC facility | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
      | Content type | VAMC facility | Hours | field_facility_hours  | Table Field |  | 1 | Table Field |  |
      | Content type | VAMC facility | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
      | Content type | VAMC facility | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
      | Content type | VAMC facility | Intro text | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | VAMC facility | Local health care service offerings | field_local_health_care_service_ | Entity reference |  | Unlimited | -- Disabled -- | Translatable |
      | Content type | VAMC facility | Location services | field_location_services | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
      | Content type | VAMC facility | Main location | field_main_location | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC facility | Mental Health Phone | field_mental_health_phone | Telephone number |  | 1 | Telephone number |  |
      | Content type | VAMC facility | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | VAMC facility | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | VAMC facility | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | VAMC facility | Nickname for this facility | field_nickname_for_this_facility | Text (plain) | Required | 1 | -- Disabled -- |  |
      | Content type | VAMC facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list |  |
      | Content type | VAMC facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
      | Content type | VAMC facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC facility | Phone Number | field_phone_number  | Telephone number |  | 1 | Telephone number | Translatable |
      | Content type | VAMC facility | Region page | field_region_page | Entity reference | Required | 1 | Select list |  |
      | Content type | VAMC facility | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
      | Content type | VAMC facility health service | Facility | field_facility_location | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC facility health service | Facility description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | VAMC facility health service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC facility health service | Service location | field_service_location | Entity reference revisions |  | 3 | -- Disabled -- |  |
      | Content type | VAMC facility health service | VAMC system health service | field_regional_health_service | Entity reference | Required | 1 | Select list |  |
      | Content type | VAMC system | Appointments can be scheduled and viewed online | field_appointments_online | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system | Banner image | field_media | Entity reference |  | 1 | Media library | Translatable |
      | Content type | VAMC system | Common Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
      | Content type | VAMC system | Community stories intro text | field_intro_text_news_stories | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
      | Content type | VAMC system | Events page intro text | field_intro_text_events_page | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
      | Content type | VAMC system | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
      | Content type | VAMC system | Featured content on health-services page | field_featured_content_healthser | Entity reference revisions |  | 3 | Paragraphs Classic |  |
      | Content type | VAMC system | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
      | Content type | VAMC system | GovDelivery ID for Emergency updates email | field_govdelivery_id_emerg | Text (plain) | Required | 1 | Textfield |  |
      | Content type | VAMC system | GovDelivery ID for News and Announcements | field_govdelivery_id_news | Text (plain) | Required | 1 | Textfield |  |
      | Content type | VAMC system | Health services intro text | field_clinical_health_care_servi | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
      | Content type | VAMC system | Leadership page intro text | field_intro_text_leadership | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system | Leadership team | field_leadership | Entity reference |  | Unlimited | Autocomplete |  |
      | Content type | VAMC system | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | VAMC system | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
      | Content type | VAMC system | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
      | Content type | VAMC system | Operating status | field_operating_status | Link |  | 1 | Linkit |  |
      | Content type | VAMC system | Other VA Locations | field_other_va_locations | Text (plain) |  | Unlimited | Textfield |  |
      | Content type | VAMC system | Our Locations intro text | field_locations_intro_blurb | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC system | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | VAMC system | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
      | Content type | VAMC system | Press releases intro text | field_intro_text_press_releases | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system | Regional Health Service Offerings. | field_clinical_health_services | Entity reference |  | Unlimited | Select list |  |
      | Content type | VAMC system | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
      | Content type | VAMC system | VAMC system official name | field_vamc_system_official_name | Text (plain) |  | 1 | Textfield |  |
      | Content type | VAMC system | VAMC system short name | field_nickname_for_this_facility | Text (plain) |  | 1 | -- Disabled -- | Translatable |
      | Content type | VAMC system banner alert with situation updates | Alert body | field_body | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
      | Content type | VAMC system banner alert with situation updates | Alert dismissable? | field_alert_dismissable | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system banner alert with situation updates | Alert type | field_alert_type | List (text) | Required | 1 | Select list |  |
      | Content type | VAMC system banner alert with situation updates | Any information that is additional to any time-sensitive situation updates | field_banner_alert_situationinfo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system banner alert with situation updates | Computed values for alerts | field_banner_alert_computdvalues | Computed (text, long) |  | 1 | -- Disabled -- |  |
      | Content type | VAMC system banner alert with situation updates | Display "Find other VA facilities near you" link? | field_alert_find_facilities_cta | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system banner alert with situation updates | Display "Get updates on affected services and facilities" link | field_alert_operating_status_cta | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system banner alert with situation updates | Display "Subscribe to email updates" link? | field_alert_email_updates_button | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system banner alert with situation updates | Only show on VAMC System page & Operating status page | field_alert_inheritance_subpages | Boolean |  | 1 | Single on/off checkbox |  |
      | Content type | VAMC system banner alert with situation updates | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC system banner alert with situation updates | Send email to subscribers via GovDelivery? | field_operating_status_sendemail | Boolean |  | 1 | Single on/off checkbox | Translatable |
      | Content type | VAMC system banner alert with situation updates | Situation updates | field_situation_updates | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
      | Content type | VAMC system banner alert with situation updates | VAMC system(s) | field_banner_alert_vamcs | Entity reference | Required | Unlimited | Check boxes/radio buttons |  |
      | Content type | VAMC system health service | Facility-specific descriptions of this service | field_local_health_care_service_ | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
      | Content type | VAMC system health service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC system health service | VAMC system | field_region_page | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC system health service | VAMC system description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
      | Content type | VAMC system health service | VHA service name and description | field_service_name_and_descripti | Entity reference | Required | 1 | Select list |  |
      | Content type | VAMC system operating status | Banner alert and situation updates | field_banner_alert | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
      | Content type | VAMC system operating status | Emergency information | field_operating_status_emerg_inf | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
      | Content type | VAMC system operating status | Links | field_links | Link |  | Unlimited | Link | Translatable |
      | Content type | VAMC system operating status | Meta tags | field_meta_tags | Meta tags |  | 1 | -- Disabled -- | Translatable |
      | Content type | VAMC system operating status | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC system operating status | Update individual facility statuses | field_facility_operating_status | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
      | Content type | VAMC system operating status | VAMC system | field_office | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VBA facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | VBA facility | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
      | Content type | VBA facility | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | VBA facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | Vet Center | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
      | Content type | Vet Center | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
      | Content type | Vet Center | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
      | Content type | Vet Center | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
      | Content type | VAMC facility health service | Online scheduling available? | field_online_scheduling_availabl | List (text) |  | 1 | Select list |  |
      | Content type | VAMC facility health service | Phone number | field_phone_numbers_paragraph | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
      | Content type | VAMC facility health service | Referral required? | field_referral_required | List (text) |  | 1 | Select list |  |
      | Content type | VAMC facility health service | Walk-ins accepted? | field_walk_ins_accepted | List (text) |  | 1 | Select list |  |
