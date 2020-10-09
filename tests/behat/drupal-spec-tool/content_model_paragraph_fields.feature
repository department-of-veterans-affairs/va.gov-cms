@api
Feature: Content model: Paragraph fields
  In order to enter structured content into my site
  As a content editor
  I want to have paragraph fields that reflect my content model.

  @dst @field_type @paragraph_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for entity type paragraph
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Paragraph type | Accordion group | Accordion Items | field_va_paragraphs | Entity reference revisions | Required | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Accordion group | Add border around items | field_collapsible_panel_bordered | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Allow more than one item to expand at a time | field_collapsible_panel_multi | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion group | Start expanded | field_collapsible_panel_expand | Boolean |  | 1 | -- Disabled -- |  |
| Paragraph type | Accordion Item | Content block(s) | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Accordion Item | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Accordion Item | Title | field_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | Additional information | Trigger text | field_text_expander | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Additional information | Text | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Address | Address | field_address | Address |  | 1 | Address |  |
| Paragraph type | Alert | Alert content | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Alert | Alert Heading | field_alert_heading | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Alert | Alert Type | field_alert_type | List (text) |  | 1 | Select list |  |
| Paragraph type | Alert | Reusable alert | field_alert_block_reference | Entity reference |  | 1 | Entity browser |  |
| Paragraph type | Alert (single) | Which Alert would you like to add? | field_alert_selection | List (text) | Required | 1 | Check boxes/radio buttons |  |
| Paragraph type | Alert (single) | Markup | field_markup | Markup |  | 1 | Markup | Translatable |
| Paragraph type | Alert (single) | Non-reusable Alert | field_alert_non_reusable_ref | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Paragraph type | Alert (single) | Reusable alert | field_alert_block_reference | Entity reference |  | 1 | Entity browser | Translatable |
| Paragraph type | Audience & Topics | Audience | field_audience_selection | List (text) |  | 1 | Select list |  |
| Paragraph type | Audience & Topics | Beneficiares | field_audience_beneficiares | Entity reference |  | 1 | Check boxes/radio buttons |  |
| Paragraph type | Audience & Topics | Markup | field_markup | Markup |  | 1 | Markup | Translatable  |
| Paragraph type | Audience & Topics | Topics | field_topics | Entity reference |  | 4 | Check boxes/radio buttons |  |
| Paragraph type | Button | Button Label | field_button_label | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Button | Button Link | field_button_link | Link |  | 1 | Link |  |
| Paragraph type | Checklist | Checklist sections | field_checklist_sections | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Checklist | Section Header | field_section_header | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | Checklist | Section Intro | field_section_intro | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Checklist section | Checklist items | field_checklist_items | Text (plain) |  | Unlimited | Textfield |  |
| Paragraph type | Checklist section | Section Header | field_section_header | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Checklist section | Section Intro | field_section_intro | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Contact information | Additional contact | field_additional_contact | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Paragraph type | Contact information | Benefit Hub contacts | field_benefit_hub_contacts | Entity reference |  | 1 | Entity Browser - Table |  |
| Paragraph type | Contact information | Default contact | field_contact_default | Entity reference |  | 1 | Entity browser |  |
| Paragraph type | Contact information | Markup | field_markup | Markup |  | 1 | Markup | Translatable |
| Paragraph type | Contact information | Select contacts | field_contact_info_switch | List (text) | Required | 1 | Check boxes/radio buttons |  |
| Paragraph type | Email address | Email address | field_email_address | Email | Required | 1 | Email |  |
| Paragraph type | Email address | Label | field_email_label | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Service location | Email contacts | field_email_contacts | Entity reference revisions |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Paragraph type | Embedded image | Allow clicks on this image to open it in new tab | field_allow_clicks_on_this_image | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Embedded image | Markup | field_markup | Markup |  | 1 | Markup |  |
| Paragraph type | Embedded image | Select an image | field_media | Entity reference |  | 1 | Media library |  |
| Paragraph type | Expandable Text | Full Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Expandable Text | Text Expander | field_text_expander | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Link teaser | Link | field_link | Link |  | 1 | Linkit |  |
| Paragraph type | Link teaser | Link summary | field_link_summary | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Link to file or video | Link text | field_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Paragraph type | Link to file or video | Link to a file or video | field_media | Entity reference | Required | 1 | Media library | Translatable |
| Paragraph type | Link to file or video | Markup | field_markup | Markup |  | 1 | Markup | Translatable |
| Paragraph type | List of link teasers | Link teasers | field_va_paragraphs | Entity reference revisions | Required | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | List of link teasers | Title | field_title | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | List of links | Section Header | field_section_header | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | List of links | Links | field_links | Link |  | Unlimited | Linkit |  |
| Paragraph type | List of links | Final link | field_link | Link |  | 1 | Linkit | Translatable |
| Paragraph type | Lists of links | List of links | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Lists of links | Section Header | field_section_header | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | Media list - Videos | Section Header | field_section_header | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Media list - Videos | Videos | field_videos | Entity reference | Required | Unlimited | Media library |  |
| Paragraph type | Media list - Images | Images | field_images | Entity reference | Required | Unlimited | Media library |  |
| Paragraph type | Media list - Images | Section Header | field_section_header | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Number callout | Additional information | field_wysiwyg | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Number callout | Short phrase with a number, or time element | field_short_phrase_with_a_number | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Non-reusable Alert | Alert Content | field_va_paragraphs | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL | Translatable |
| Paragraph type | Non-reusable Alert | Alert Heading | field_alert_heading | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Non-reusable Alert | Alert Type | field_alert_type | List (text) |  | 1 | Select list | Translatable |
| Paragraph type | Phone number | Extension number | field_phone_extension | Text (plain) |  | 1 | Textfield | Translatable |
| Paragraph type | Phone number | Label | field_phone_label | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | Phone number | Phone number | field_phone_number | Text (plain) | Required | 1 | Textfield | Translatable |
| Paragraph type | Phone number | Type | field_phone_number_type | List (text) | Required | 1 | Select list | Translatable |
| Paragraph type | Process list | Steps | field_steps | Text (formatted, long) | Required | Unlimited | Text area (multiple rows) |  |
| Paragraph type | Q&A | Answer | field_answer | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A | Question | field_question | Text (plain) | Required | 1 | Textfield with counter |  |
| Paragraph type | Q&A group | Section Header | field_section_header | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | Q&A group | Q&As | field_q_as | Entity reference | Required | Unlimited | Entity Browser - Table |  |
| Paragraph type | Q&A Section | Display this set of Q&As as a group of accordions. | field_accordion_display | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Q&A Section | Questions | field_questions | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Q&A Section | Section Intro | field_section_intro | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Paragraph type | Q&A Section | Section Header | field_section_header | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | React Widget | Call To Action Widget | field_cta_widget | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Default Link | field_default_link | Link |  | 1 | Linkit |  |
| Paragraph type | React Widget | Display default link as button | field_button_format | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | React Widget | Error Message | field_error_message | Text (formatted) |  | 1 | Text field |  |
| Paragraph type | React Widget | Loading Message | field_loading_message | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | React Widget | Timeout | field_timeout | Number (integer) |  | 1 | Number field |  |
| Paragraph type | React Widget | Widget Type | field_widget_type | Text (plain) | Required | 1 | Textfield |  |
| Paragraph type | Service location | Additional Hours options (e.g. On-Call, Appointments may be available outside these hours, please call.) | field_additional_hours_info | Text (plain) |  | 1 | Textfield |  |
| Paragraph type | Service location | Address | field_service_location_address | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Paragraph type | Service location | Hours | field_facility_service_hours | Table Field |  | 1 | Table Field |  |
| Paragraph type | Service location | Use the facility's hours | field_hours | List (text) | Required | 1 | Select list |  |
| Paragraph type | Service location | Other phone numbers | field_phone | Entity reference revisions |  | 5 | Inline entity form - Complex - Table View Mode |  |
| Paragraph type | Service location | Use the general facility phone number | field_use_main_facility_phone | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Service location address | Address | field_address | Address |  | 1 | Address | Translatable |
| Paragraph type | Service location address | Building name/number | field_building_name_number | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Service location address | Clinic name | field_clinic_name | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Service location address | Use the facility's street address? | field_use_facility_address | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Service location address | Wing, Floor, or Room Number | field_wing_floor_or_room_number | Text (plain) |  | 1 | Textfield with counter |  |
| Paragraph type | Situation update | Date and time | field_date_and_time | Date | Required | 1 | Date and time |  |
| Paragraph type | Situation update | Date and time | field_datetime_range_timezone | Smart date range |  | 1 | -- Disabled -- |  |
| Paragraph type | Situation update | Send email to subscribers via GovDelivery? | field_send_email_to_subscribers | Boolean |  | 1 | Single on/off checkbox |  |
| Paragraph type | Situation update | Update | field_wysiwyg | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Staff profile | Staff profile | field_staff_profile | Entity reference | Required | 1 | Select list |  |
| Paragraph type | Step | Alert | field_alert | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Paragraph type | Step | Select an image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Paragraph type | Step | Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Paragraph type | Step by step | Add Step | field_step | Entity reference revisions |  | Unlimited | Paragraphs Classic | Translatable |
| Paragraph type | Step by step | Section Header | field_section_header | Text (plain) |  | 1 | Textfield with counter | Translatable |
| Paragraph type | Table | Table | field_table | Table Field |  | 1 | Table Field |  |
| Paragraph type | VAMC facility service (non-healthcare service) | Service name | field_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Paragraph type | VAMC facility service (non-healthcare service) | Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Paragraph type | Rich text | Text | field_wysiwyg | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |

