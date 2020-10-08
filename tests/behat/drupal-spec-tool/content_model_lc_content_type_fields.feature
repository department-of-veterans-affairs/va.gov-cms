@api
Feature: Content model: LC Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "checklist,faq_multiple_q_a,support_resources_detail_page,media_list_images,media_list_videos,q_a,step_by_step" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Checklist | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons |  |
| Content type | Checklist | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Checklist | Checklist | field_checklist | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Checklist | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Checklist | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Checklist | Meta description | field_description | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Checklist | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Checklist | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Checklist | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Checklist | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Checklist | Primary category | field_primary_category | Entity reference | Required | 1 | Select list |  |
| Content type | Checklist | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Checklist | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | Checklist | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Checklist | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Checklist | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | FAQ - multiple Q&As | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | FAQ - multiple Q&As | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | FAQ - multiple Q&As | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs Classic | Translatable |
| Content type | FAQ - multiple Q&As | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | FAQ - multiple Q&As | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | FAQ - multiple Q&As | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | FAQ - multiple Q&As | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | FAQ - multiple Q&As | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | FAQ - multiple Q&As | Page introduction | field_intro_text_limited_html  | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | FAQ - multiple Q&As | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | FAQ - multiple Q&As | Q&A groups | field_q_a_groups | Entity reference revisions | Required | Unlimited | Paragraphs Classic |  |
| Content type | FAQ - multiple Q&As | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | FAQ - multiple Q&As | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | FAQ - multiple Q&As | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | FAQ - multiple Q&As | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | FAQ - multiple Q&As | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Learning Center Detail Page | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Learning Center Detail Page | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Learning Center Detail Page | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Learning Center Detail Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Learning Center Detail Page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
| Content type | Learning Center Detail Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Learning Center Detail Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Learning Center Detail Page | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Learning Center Detail Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Learning Center Detail Page | Page introduction | field_intro_text_limited_html  | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Learning Center Detail Page | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Learning Center Detail Page | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL |  |
| Content type | Learning Center Detail Page | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Learning Center Detail Page | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Learning Center Detail Page | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table |  |
| Content type | Media list - Images | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Media list - Images | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Media list - Images | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Media list - Images | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Media list - Images | Media list - Images | field_media_list_images | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Media list - Images | Meta description | field_description | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Media list - Images | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Media list - Images | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Media list - Images | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Media list - Images | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Media list - Images | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Media list - Images | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Media list - Images | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | Media list - Images | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Media list - Images | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Media list - Images | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Media list - Videos | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Media list - Videos | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Media list - Videos | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Media list - Videos | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Media list - Videos | Meta description | field_description | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Media list - Videos | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield | Translatable |
| Content type | Media list - Videos | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Media list - Videos | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Media list - Videos | Page introduction | field_intro_text_limited_html | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Media list - Videos | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Media list - Videos | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Media list - Videos | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | Media list - Videos | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Media list - Videos | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Media list - Videos | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Media list - Videos | Videos | field_media_list_videos | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Q&A | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Q&A | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Q&A | Answer | field_answer | Entity reference revisions | Required | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Q&A | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs Classic | Translatable |
| Content type | Q&A | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Q&A | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Q&A | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Q&A | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Q&A | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Q&A | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Q&A | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | Q&A | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Q&A | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
| Content type | Step-by-Step | Additional categories (optional) | field_other_categories | Entity reference |  | 6 | Check boxes/radio buttons | Translatable |
| Content type | Step-by-Step | Alert | field_alert_single | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Step-by-Step | CTA buttons | field_buttons | Entity reference revisions | Required | 2 | Paragraphs Classic |  |
| Content type | Step-by-Step | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Step-by-Step | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Step-by-Step | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Step-by-Step | Need more help? | field_contact_information | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Step-by-Step | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Step-by-Step | Page introduction | field_intro_text_limited_html | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Step-by-Step | Primary category | field_primary_category | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Step-by-Step | Related Information | field_related_information | Entity reference revisions |  | 5 | Paragraphs EXPERIMENTAL | Translatable |
| Content type | Step-by-Step | Related Links | field_related_links | Entity reference revisions |  | 1 | -- Disabled -- | Translatable |
| Content type | Step-by-Step | Repeat CTA buttons | field_buttons_repeat | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | Step-by-Step | Step-by-step | field_steps | Entity reference revisions | Required | Unlimited | Paragraphs Classic |  |
| Content type | Step-by-Step | Tags | field_tags | Entity reference revisions |  | 1 | Paragraphs Classic |  |
| Content type | Step-by-Step | VA Benefit Hubs | field_related_benefit_hubs | Entity reference | Required | 3 | Entity Browser - Table | Translatable |
