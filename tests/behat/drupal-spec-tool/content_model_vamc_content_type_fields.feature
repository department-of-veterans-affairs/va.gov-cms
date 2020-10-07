@api
Feature: Content model: VAMC Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "health_care_region_detail_page,event,event_listing,health_services_listing,leadership_listing,locations_listing,press_release,press_releases_listing,person_profile,story_listing,news_story,health_care_local_facility,health_care_local_health_service,health_care_region_page,full_width_banner_alert,regional_health_care_service_des,vamc_operating_status_and_alerts" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
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
| Content type | Event | Building, floor, or room | field_location_humanreadable | Text (plain) |  | 1 | Textfield |  |
| Content type | Event | Additional registration  information | field_additional_information_abo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Address | field_address | Address |  | 1 | Address |  |
| Content type | Event | Cost | field_event_cost | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Event | Date and time | field_date | Date range |  | 1 | Date and time range |  |
| Content type | Event | Where should the event be listed? | field_listing | Entity reference | Required | 1 | Select list |  |
| Content type | Event | Facility location | field_facility_location | Entity reference |  | 1 | Select list |  |
| Content type | Event | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Event | Full event description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Event | Event image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Event | Location type | field_location_type | List (text) |  | 1 | Select list |  |
| Content type | Event | Teaser description | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Event | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Event | Order | field_order | List (integer) |  | 1 | Select list |  |
| Content type | Event | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Event | Registration required | field_event_registrationrequired | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Event | Label | field_event_cta | List (text) |  | 1 | Select list |  |
| Content type | Event | URL | field_link | Link |  | 1 | Link | Translatable |
| Content type | Event | Online event link | field_url_of_an_online_event | Link |  | 1 | Link |  |
| Content type | Events List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Events List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Events List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Events List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Events List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Events List | Office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Health Services List | Featured content on health-services page | field_featured_content_healthser | Entity reference revisions |  | 3 | Paragraphs Classic | Translatable |
| Content type | Health Services List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Health Services List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Health Services List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Health Services List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Health Services List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Health Services List | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Leadership List | Leadership team | field_leadership | Entity reference |  | Unlimited | Autocomplete | Translatable |
| Content type | Leadership List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Leadership List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Leadership List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Leadership List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Leadership List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Leadership List | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Locations List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Locations List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Locations List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Locations List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Locations List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Locations List | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News Release | Full text of the Press Release | field_press_release_fulltext | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | News Release | Introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | News Release | Location | field_address | Address | Required | 1 | Address | Translatable |
| Content type | News Release | Media assets | field_press_release_downloads | Entity reference |  | Unlimited | Media library |  |
| Content type | News Release | Media Contact(s) | field_press_release_contact | Entity reference |  | Unlimited | Autocomplete | Translatable |
| Content type | News Release | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | News Release | News releases listing | field_listing | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News Release | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News Release | PDF of Press Release | field_pdf_version | Entity reference |  | 1 | Media library |  |
| Content type | News Release | Release date | field_release_date | Date |  | 1 | Date and time |  |
| Content type | News Releases List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | News Releases List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | News Releases List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | News Releases List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | News Releases List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | News Releases List | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | News Releases List | Related office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff Profile | Body text | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Staff Profile | Complete Biography | field_complete_biography | File |  | 1 | File |  |
| Content type | Staff Profile | Email address | field_email_address | Email |  | 1 | Email |  |
| Content type | Staff Profile | First name | field_name_first | Text (plain) |  | 1 | Textfield |  |
| Content type | Staff Profile | High-resolution photo should be available for download by site visitors | field_photo_allow_hires_download | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Staff Profile | First sentence | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Staff Profile | Last name | field_last_name | Text (plain) |  | 1 | Textfield |  |
| Content type | Staff Profile | Job title | field_description | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Staff Profile | Meta tags | field_meta_tags | Meta tags |  | 1 | -- Disabled -- | Translatable |
| Content type | Staff Profile | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff Profile | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Staff Profile | Photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Staff Profile | Related office or health care region | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Staff Profile | Suffix | field_suffix | Text (plain) |  | 1 | Textfield |  |
| Content type | Story | Author | field_author | Entity reference |  | 1 | Autocomplete |  |
| Content type | Story | Featured | field_featured | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Story | Body text | field_full_story | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | Story | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Story | Caption | field_image_caption | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | Story | First sentence (lede) | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Story | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Story | Order | field_order | List (integer) |  | 1 | Select list | Translatable |
| Content type | Story | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Story | Where should the story be listed? | field_listing | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Stories List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Stories List | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Stories List | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Stories List | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Stories List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Stories List | Office or health care system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC Facility | Address | field_address | Address |  | 1 | Address | Translatable |
| Content type | VAMC Facility | Classification | field_facility_classification | List (text) |  | 1 | Select list |  |
| Content type | VAMC Facility | Email Subscription | field_email_subscription | Link |  | 1 | Linkit | Translatable |
| Content type | VAMC Facility | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
| Content type | VAMC Facility | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield |  |
| Content type | VAMC Facility | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
| Content type | VAMC Facility | Hours | field_facility_hours  | Table Field |  | 1 | Table Field |  |
| Content type | VAMC Facility | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | VAMC Facility | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
| Content type | VAMC Facility | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VAMC Facility | Health services | field_local_health_care_service_ | Entity reference |  | Unlimited | -- Disabled -- | Translatable |
| Content type | VAMC Facility | Location services | field_location_services | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
| Content type | VAMC Facility | Main location | field_main_location | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC Facility | Mental Health Phone | field_mental_health_phone | Telephone number |  | 1 | Telephone number |  |
| Content type | VAMC Facility | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC Facility | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | VAMC Facility | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC Facility | Nickname for this facility | field_nickname_for_this_facility | Text (plain) | Required | 1 | -- Disabled -- |  |
| Content type | VAMC Facility | Status | field_operating_status_facility | List (text) | Required | 1 | Select list |  |
| Content type | VAMC Facility | Details | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC Facility | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC Facility | Phone Number | field_phone_number  | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | VAMC Facility | What health care system does the facility belong to? | field_region_page | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC Facility | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
| Content type | VAMC Facility Health Service | Facility | field_facility_location | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC Facility Health Service | Facility description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC Facility Health Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC Facility Health Service | Service locations | field_service_location | Entity reference revisions |  | 3 | Paragraphs EXPERIMENTAL |  |
| Content type | VAMC Facility Health Service | VAMC system health service | field_regional_health_service | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC System | Appointments can be scheduled and viewed online | field_appointments_online | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System | Banner image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | VAMC System | Common Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | VAMC System | Community stories intro text | field_intro_text_news_stories | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC System | Events page intro text | field_intro_text_events_page | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC System | Facebook | field_facebook | Link |  | 1 | Link | Translatable |
| Content type | VAMC System | Featured content on health-services page | field_featured_content_healthser | Entity reference revisions |  | 3 | Paragraphs Classic |  |
| Content type | VAMC System | Flickr | field_flickr | Link |  | 1 | Link | Translatable |
| Content type | VAMC System | GovDelivery ID for Emergency updates email | field_govdelivery_id_emerg | Text (plain) | Required | 1 | Textfield |  |
| Content type | VAMC System | GovDelivery ID for News and Announcements | field_govdelivery_id_news | Text (plain) | Required | 1 | Textfield |  |
| Content type | VAMC System | Health services intro text | field_clinical_health_care_servi | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC System | Instagram | field_instagram | Link |  | 1 | Link | Translatable |
| Content type | VAMC System | Leadership page intro text | field_intro_text_leadership | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC System | Leadership team | field_leadership | Entity reference |  | Unlimited | Autocomplete |  |
| Content type | VAMC System | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC System | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | VAMC System | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | VAMC System | Operating status | field_operating_status | Link |  | 1 | Linkit |  |
| Content type | VAMC System | Other VA Locations | field_other_va_locations | Text (plain) |  | Unlimited | Textfield |  |
| Content type | VAMC System | Our Locations intro text | field_locations_intro_blurb | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | VAMC System | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC System | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | VAMC System | Press Release Blurb | field_press_release_blurb | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Content type | VAMC System | Press releases intro text | field_intro_text_press_releases | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC System | Regional Health Service Offerings. | field_clinical_health_services | Entity reference |  | Unlimited | Select list |  |
| Content type | VAMC System | Twitter | field_twitter | Link |  | 1 | Link | Translatable |
| Content type | VAMC System | VAMC system official name | field_vamc_system_official_name | Text (plain) |  | 1 | Textfield |  |
| Content type | VAMC System | VAMC system short name | field_nickname_for_this_facility | Text (plain) |  | 1 | -- Disabled -- | Translatable |
| Content type | VAMC System Banner Alert with Situation Updates | Alert body | field_body | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC System Banner Alert with Situation Updates | Alert dismissable? | field_alert_dismissable | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System Banner Alert with Situation Updates | Alert type | field_alert_type | List (text) | Required | 1 | Select list |  |
| Content type | VAMC System Banner Alert with Situation Updates | Any information that is additional to any time-sensitive situation updates | field_banner_alert_situationinfo | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VAMC System Banner Alert with Situation Updates | Computed values for alerts | field_banner_alert_computdvalues | Computed (text, long) |  | 1 | -- Disabled -- |  |
| Content type | VAMC System Banner Alert with Situation Updates | Display "Find other VA facilities near you" link? | field_alert_find_facilities_cta | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System Banner Alert with Situation Updates | Display "Get updates on affected services and facilities" link | field_alert_operating_status_cta | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System Banner Alert with Situation Updates | Display "Subscribe to email updates" link? | field_alert_email_updates_button | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System Banner Alert with Situation Updates | Limit banner display to the home and the Operating Status page(s) | field_alert_inheritance_subpages | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VAMC System Banner Alert with Situation Updates | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC System Banner Alert with Situation Updates | Send email update on this situation | field_operating_status_sendemail | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | VAMC System Banner Alert with Situation Updates | Situation updates | field_situation_updates | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | VAMC System Banner Alert with Situation Updates | Pages for the following VAMC systems | field_banner_alert_vamcs | Entity reference | Required | Unlimited | Check boxes/radio buttons |  |
| Content type | VAMC System Health Service | Facility services | field_local_health_care_service_ | Entity reference |  | Unlimited | -- Disabled -- |  |
| Content type | VAMC System Health Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC System Health Service | VAMC system | field_region_page | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC System Health Service | VAMC system description of service | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | VAMC System Health Service | VHA service name and description | field_service_name_and_descripti | Entity reference | Required | 1 | Select list |  |
| Content type | VAMC System Operating Status | System-wide alerts | field_banner_alert | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC System Operating Status | Patient resources | field_operating_status_emerg_inf | Text (formatted, long) | Required | 1 | Text area (multiple rows) |  |
| Content type | VAMC System Operating Status | Local emergency resources | field_links | Link |  | Unlimited | Link | Translatable |
| Content type | VAMC System Operating Status | Meta tags | field_meta_tags | Meta tags |  | 1 | -- Disabled -- | Translatable |
| Content type | VAMC System Operating Status | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC System Operating Status | Facility operating statuses | field_facility_operating_status | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC System Operating Status | VAMC system | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VAMC Facility Health Service | Is online scheduling available for this service? | field_online_scheduling_availabl | List (text) | Required | 1 | Select list |  |
| Content type | VAMC Facility Health Service | Phone number for appointments | field_phone_numbers_paragraph | Entity reference revisions |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | VAMC Facility Health Service | Is a referral required? | field_referral_required | List (text) | Required | 1 | Select list |  |
| Content type | VAMC Facility Health Service | Are walkins accepted? | field_walk_ins_accepted | List (text) | Required | 1 | Select list |  |
| Content type | VAMC Facility Health Service | Appointments help text | field_appointments_help_text | Markup |  | 1 | Markup |  |
| Content type | VAMC Facility Health Service | Service locations help text | field_facility_service_loc_help | Markup |  | 1 | Markup |  |
