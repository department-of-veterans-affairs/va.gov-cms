@api
Feature: Content model: Benefits hubs Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "page,landing_page,basic_landing_page,support_service" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Benefits Detail Page | Alert | field_alert | Entity reference |  | 1 | Entity browser |  |
| Content type | Benefits Detail Page | Featured content | field_featured_content | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits Detail Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Benefits Detail Page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL |  |
| Content type | Benefits Detail Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits Detail Page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form |  |
| Content type | Benefits Detail Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter |  |
| Content type | Benefits Detail Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Benefits Detail Page | Page introduction | field_intro_text_limited_html | Text (formatted, long) | Required | 1 | -- Disabled -- |  |
| Content type | Benefits Detail Page | Page introduction | field_intro_text  | Text (plain, long) | Required | 1 | Textarea (multiple rows) with counter |  |
| Content type | Benefits Detail Page | Page last built | field_page_last_built | Date |  | 1 | -- Disabled -- |  |
| Content type | Benefits Detail Page | Plain Language Certification Date | field_plainlanguage_date | Date |  | 1 | Date and time |  |
| Content type | Benefits Detail Page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits Hub Landing Page | Alert | field_alert | Entity reference |  | 1 | Entity browser | Translatable |
| Content type | Benefits Hub Landing Page | Hub Icon | field_title_icon | List (text) |  | 1 | Select list |  |
| Content type | Benefits Hub Landing Page | Hub label | field_home_page_hub_label | Text (plain) |  | 1 | Textfield |  |
| Content type | Benefits Hub Landing Page | Hub teaser text | field_teaser_text | Text (plain) |  | 1 | Textfield with counter |  |
| Content type | Benefits Hub Landing Page | Links for non-veterans | field_links | Link |  | Unlimited | Linkit |  |
| Content type | Benefits Hub Landing Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits Hub Landing Page | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | Benefits Hub Landing Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Benefits Hub Landing Page | Owner | field_administration | Entity reference | Required | 1 | Select list |  |
| Content type | Benefits Hub Landing Page | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Benefits Hub Landing Page | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits Hub Landing Page | Plain language Certified Date | field_plainlanguage_date | Date |  | 1 | Date and time | Translatable |
| Content type | Benefits Hub Landing Page | Promo | field_promo | Entity reference |  | 1 | Select list |  |
| Content type | Benefits Hub Landing Page | Related Links | field_related_links | Entity reference revisions |  | 1 | Paragraphs Classic | Translatable |
| Content type | Benefits Hub Landing Page | Spokes | field_spokes | Entity reference revisions | Required | 4 | Paragraphs EXPERIMENTAL |  |
| Content type | Benefits Hub Landing Page | Support Services | field_support_services | Entity reference |  | Unlimited | Inline entity form - Complex |  |
| Content type | Landing Page | Generate a table of contents from major headings | field_table_of_contents_boolean | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Content type | Landing Page | Main content | field_content_block | Entity reference revisions | Required | Unlimited | Paragraphs Browser EXPERIMENTAL | Translatable |
| Content type | Landing Page | Meta description | field_description | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Landing Page | Meta title tag | field_meta_title | Text (plain) | Required | 1 | Textfield with counter | Translatable |
| Content type | Landing Page | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Landing Page | Page introduction | field_intro_text_limited_html  | Text (formatted, long) | Required | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Landing Page | Product | field_product | Entity reference | Required | 1 | Select list |  |
| Content type | Support Service | Link | field_link | Link |  | 1 | Link |  |
| Content type | Support Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Support Service | Page last built | field_page_last_built | Date |  | 1 | Date and time | Translatable |
| Content type | Support Service | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number |  |
| Content type | Support Service | Related office | field_office | Entity reference | Required | 1 | Select list | Translatable |
