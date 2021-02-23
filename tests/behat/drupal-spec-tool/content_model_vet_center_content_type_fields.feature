@api
Feature: Content model: Vet Center Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "vet_center" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Vet Center | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Vet Center | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Vet Center | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Address | field_address | Address |  | 1 | Address | Translatable |
| Content type | Vet Center | Hours | field_facility_hours | Table Field |  | 1 | Table Field | Translatable |
| Content type | Vet Center | Image | field_media | Entity reference |  | 1 | Autocomplete | Translatable |
| Content type | Vet Center | Non-traditional hours | field_non_traditional_hours | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Vet Center | Vet Center call center | field_vet_center_call_center | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
