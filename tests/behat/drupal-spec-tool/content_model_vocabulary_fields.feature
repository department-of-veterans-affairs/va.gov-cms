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
| Vocabulary | Sections | Description | field_description | Text (plain) |  | 1 | Textfield |  |
| Vocabulary | Sections | Product | field_product | Entity Reference |  | 1 | Select list |  |
| Vocabulary | VA Services | Common conditions | field_commonly_treated_condition | Text (plain) |  |  | Textfield |  |
| Vocabulary | VA Services | Health Service API ID | field_health_service_api_id | Text (plain) |  |  | Textfield |  |
| Vocabulary | VA Services | Section | field_owner | Entity Reference | Required |  | -- Disabled -- |  |
| Vocabulary | VA Services | Patient-friendly name | field_also_known_as | Text (plain) |  |  | Textfield |  |
| Vocabulary | VA Services | Type of care | field_service_type_of_care | List (text) |  |  | Select list |  |
| Vocabulary | VA Services | VHA Stop code | field_vha_healthservice_stopcode | Number (integer) |  |  | Number field |  |
| Vocabulary | VA Services | Common conditions | field_vet_center_com_conditions  | Text (plain) |  |  | Textfield |  |
| Vocabulary | VA Services | Patient friendly name | field_vet_center_friendly_name | Text (plain) |  |  | Textfield |  |
| Vocabulary | VA Services | Service description | field_vet_center_service_descrip | Text (plain, long) |  |  | Text area (multiple rows) |  |
| Vocabulary | VA Services | Type of Care | field_vet_center_type_of_care | List (text) |  |  | Select list |  |
| Vocabulary | VA Services | This is a required Vet Center service | field_vet_center_required_servic | Boolean |  |  | Single on/off checkbox |  |
| Vocabulary | VA Services | Enforce unique combo service | field_enforce_unique_combo_servi | Allow Only One |  | 1 | Allow Only One widget |  |
| Vocabulary | Products | Knowledge base landing page | field_kb_landing_page | Entity Reference |  | 1 | Autocomplete |  |
