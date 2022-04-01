@api
Feature: Content model: LC Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "checklist,faq_multiple_q_a,media_list_images,media_list_videos,q_a,support_resources_detail_page,step_by_step" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Checklist | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons |  |
| Content type | Checklist | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | Checklist | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs (stable) | Translatable |
| Content type | Checklist | Checklist | field_checklist | Entity reference revisions |  | 1 | Paragraphs (stable) |  |
| Content type | Checklist | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Checklist | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Checklist | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | Checklist | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Checklist | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Checklist | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | Checklist | Primary category | field_primary_category | Entity reference | Required | 1 | Select list |  |
| Content type | Checklist | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Checklist | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | FAQ page | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | FAQ page | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs Legacy | Translatable |
| Content type | FAQ page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | FAQ page | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | FAQ page | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | FAQ page | Page introduction | field_intro_text_limited_html  | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | FAQ page | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | FAQ page | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | FAQ page | Q&A groups | field_q_a_groups | Entity reference revisions | Required | Unlimited | Paragraphs Legacy |  |
| Content type | FAQ page | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | FAQ page | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | FAQ page | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | FAQ page | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Resources and support Detail Page | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs (stable) | Translatable |
| Content type | Resources and support Detail Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Resources and support Detail Page | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Resources and support Detail Page | Page introduction | field_intro_text_limited_html  | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Resources and support Detail Page | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Resources and support Detail Page | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | Resources and support Detail Page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
| Content type | Resources and support Detail Page | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table |  |
| Content type | Resources and support Detail Page | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) |  |
| Content type | Resources and support Detail Page | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Resources and support Detail Page | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Resources and support Detail Page | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Resources and support Detail Page | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | Image list | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Image list | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Image list | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Image list | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | Image list | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs (stable) | Translatable |
| Content type | Image list | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Image list | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | Image list | Media list - Images | field_media_list_images | Entity reference revisions |  | 1 | Paragraphs (stable) |  |
| Content type | Image list | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Image list | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Image list | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | Image list | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Image list | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Video list | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Video list | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Video list | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Video list | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy |  |
| Content type | Video list | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs (stable) | Translatable |
| Content type | Video list | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Video list | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | Video list | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Video list | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Video list | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | Video list | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Video list | Videos | field_media_list_videos | Entity reference revisions |  | 1 | Paragraphs (stable) |  |
| Content type | Video list | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Q&A - single | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Q&A - single | Answer | field_answer | Entity reference revisions | Required | 1 | Paragraphs (stable) |  |
| Content type | Q&A - single | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | Q&A - single | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Q&A - single | Enable standalone Resources and support page for this Q&A. | field_standalone_page | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Q&A - single | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs Legacy | Translatable |
| Content type | Q&A - single | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Q&A - single | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | Q&A - single | Primary category | field_primary_category | Entity reference |  | 1 | Select list | Translatable |
| Content type | Q&A - single | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Q&A - single | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Step-by-Step | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Step-by-Step | Calls to action | field_buttons | Entity reference revisions |  | 2 | Paragraphs Legacy |  |
| Content type | Step-by-Step | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Step-by-Step | Alert | field_alert_single | Entity reference revisions | Required | 1 | Paragraphs Legacy | Translatable |
| Content type | Step-by-Step | Page introduction | field_intro_text_limited_html | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Step-by-Step | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Legacy | Translatable |
| Content type | Step-by-Step | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Step-by-Step | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs (stable) | Translatable |
| Content type | Step-by-Step | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Step-by-Step | Step-by-step | field_steps | Entity reference revisions | Required | Unlimited | Paragraphs Legacy |  |
| Content type | Step-by-Step | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Step-by-Step | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Legacy |  |
| Content type | Step-by-Step | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
