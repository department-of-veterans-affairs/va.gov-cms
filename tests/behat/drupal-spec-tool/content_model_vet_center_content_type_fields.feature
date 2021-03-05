@api
Feature: Content model: Vet Center Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "vet_center,vet_center_cap,vet_center_facility_health_servi,vet_center_locations_list,vet_center_satelite_facility" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Vet Center | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Vet Center | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Vet Center | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Address | field_address | Address |  | 1 | Address | Translatable |
| Content type | Vet Center | Hours | field_office_hours | Office hours |  | Unlimited | Office hours (week) |  |
| Content type | Vet Center | Image | field_media | Entity reference |  | 1 | Autocomplete | Translatable |
| Content type | Vet Center | Non-traditional hours | field_non_traditional_hours | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center | Phone Number | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Vet Center | Prepare for your visit accordions | field_prepare_for_visit | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Vet Center | Vet Center call center | field_vet_center_call_center | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Featured content | field_vet_center_feature_content | Entity reference revisions |  | 2 | Paragraphs EXPERIMENTAL |  |
| Content type | Vet Center Community Access Point | Address | field_address | Address | Required | 1 | Address | Translatable |
| Content type | Vet Center Community Access Point | Geographical identifier | field_geographical_identifier | Text (plain) | Required | 1 | Textfield |  |
| Content type | Vet Center Community Access Point | Hours | field_facility_hours | Table Field |  | 1 | Table Field | Translatable |
| Content type | Vet Center Community Access Point | Image | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Vet Center Community Access Point | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Community Access Point | Vet Center | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Community Access Point | Vet center community access point description of service | field_body | Text (formatted, long) |  | 1 | -- Disabled -- | Translatable |
| Content type | Vet Center Facility Health Service | Description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center Facility Health Service | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Facility Health Service | Service | field_service_name_and_descripti | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Facility Health Service | Vet Center | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Locations List | Owner | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center Locations List | Page introduction | field_intro_text | Text (plain, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center Locations List | Select nearby Vet Centers | field_nearby_vet_centers | Entity reference |  | Unlimited | Autocomplete |  |
| Content type | Vet Center Locations List | Vet Center | field_office | Entity reference | Required | 1 | Select list | Translatable |
