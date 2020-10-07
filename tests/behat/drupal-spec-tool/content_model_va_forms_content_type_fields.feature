@api
Feature: Content model: VA Forms Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields                                                                                                                                                               
     Scenario: Fields
       Then exactly the following fields should exist for bundles "va_form" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | VA Form | Alert | field_alert | Entity reference |  | 1 | Entity browser | Translatable |
| Content type | VA Form | Benefits hub | field_benefit_categories | Entity reference |  | Unlimited | Check boxes/radio buttons |  |
| Content type | VA Form | Category | field_va_form_type | List (text) |  | 1 | Select list |  |
| Content type | VA Form | Deleted | field_va_form_deleted | Boolean |  | 1 | Single on/off checkbox |  |
| Content type | VA Form | Deleted date | field_va_form_deleted_date | Date |  | 1 | Date and time |  |
| Content type | VA Form | Form administration | field_va_form_administration | Entity reference |  | 1 | Select list |  |
| Content type | VA Form | Form name | field_va_form_name | Text (plain) |  | 1 | Textfield |  |
| Content type | VA Form | Form number | field_va_form_number | Text (plain) |  | 1 | Textfield |  |
| Content type | VA Form | Form title | field_va_form_title | Text (plain) |  | 1 | Textfield |  |
| Content type | VA Form | Helpful links | field_va_form_link_teasers | Entity reference revisions |  | Unlimited | Paragraphs Classic |  |
| Content type | VA Form | Issue date | field_va_form_issue_date | Date |  | 1 | Date and time |  |
| Content type | VA Form | Form Language | field_va_form_language | List (text) | Required | 1 | Select list |  |
| Content type | VA Form | Link to form | field_va_form_url | Link |  | 1 | Link |  |
| Content type | VA Form | Link to online tool | field_va_form_tool_url | Link |  | 1 | Link |  |
| Content type | VA Form | Meta tags | field_meta_tags | Meta tags |  | 1 | Advanced meta tags form | Translatable |
| Content type | VA Form | Number of pages | field_va_form_num_pages | Number (integer) |  | 1 | Number field |  |
| Content type | VA Form | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | VA Form | Related forms | field_va_form_related_forms | Entity reference |  | Unlimited | Autocomplete |  |
| Content type | VA Form | Revision date | field_va_form_revision_date | Date |  | 1 | Date and time |  |
| Content type | VA Form | Row ID | field_va_form_row_id | Number (integer) |  | 1 | Number field |  |
| Content type | VA Form | Tool intro | field_va_form_tool_intro | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Content type | VA Form | When to use | field_va_form_usage | Text (formatted, long) |  | 1 | Text area (multiple rows) |  |
