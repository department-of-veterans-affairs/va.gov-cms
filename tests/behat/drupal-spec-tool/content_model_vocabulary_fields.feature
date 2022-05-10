@api
Feature: Content model: Vocabulary fields
  In order to enter structured content into my site
  As a content editor
  I want to have vocabulary fields that reflect my content model.

  @dst @field_type @vocabulary_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for entity type taxonomy_term
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Vocabulary | Audience - Beneficiaries | Promoted to Resources and Support Homepage | field_audience_rs_homepage | Boolean |  | 1 | Single on/off checkbox |  |
| Vocabulary | Audience - Non-beneficiaries | Promoted to Resources and Support Homepage | field_audience_rs_homepage | Boolean |  | 1 | Single on/off checkbox | Translatable |
| Vocabulary | Facility Supplemental Status | Section | field_administration | Entity reference | Required | 1 | Select list |  |
| Vocabulary | Facility Supplemental Status | Enforce Unique Id | field_enforce_unique_id | Allow Only One |  | 1 | Allow Only One widget |  |
| Vocabulary | Facility Supplemental Status | Guidance | field_guidance | Text (formatted, long) |  | 1 | Textarea (multiple rows) with counter |  |
| Vocabulary | Facility Supplemental Status | Status ID | field_status_id | Text (plain) | Required | 1 | Textfield |  |
| Vocabulary | Products | Knowledge base landing page | field_kb_landing_page | Entity reference |  | 1 | Autocomplete |  |
| Vocabulary | Sections | Description | field_description | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Product | field_product | Entity reference |  | 1 | Select list |  |
| Vocabulary | VA Services | Patient-friendly name | field_also_known_as | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VA Services | Common conditions | field_commonly_treated_condition | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VA Services | Enforce unique combo service | field_enforce_unique_combo_servi | Allow Only One |  | 1 | Allow Only One widget |  |
| Vocabulary | VA Services | Health Service API ID | field_health_service_api_id | Text (plain) | Required | 1 | Textfield |  |
| Vocabulary | VA Services | Section | field_owner | Entity reference | Required | 1 | -- Disabled -- |  |
| Vocabulary | VA Services | Type of care | field_service_type_of_care | List (text) |  | 1 | Select list |  |
| Vocabulary | VA Services | Common conditions | field_vet_center_com_conditions  | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VA Services | Patient friendly name | field_vet_center_friendly_name | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | VA Services | This is a required Vet Center service | field_vet_center_required_servic | Boolean |  | 1 | Single on/off checkbox |  |
| Vocabulary | VA Services | Service description | field_vet_center_service_descrip | Text (plain, long) |  | 1 | Text area (multiple rows) |  |
| Vocabulary | VA Services | Type of Care | field_vet_center_type_of_care | List (text) |  | 1 | Select list |  |
| Vocabulary | VA Services | VHA Stop code | field_vha_healthservice_stopcode | Number (integer) |  | 1 | Number field |  |
