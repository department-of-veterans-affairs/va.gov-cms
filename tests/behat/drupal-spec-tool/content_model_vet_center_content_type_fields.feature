@api
Feature: Content model: Vet Center Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "vet_center,vet_center_cap,vet_center_facility_health_servi,vet_center_locations_list,vet_center_satelite_facility" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Vet Center | Facility ID | field_facility_locator_api_id | Text (plain) |  | 1 | Textfield | Translatable |
| Content type | Vet Center | Operating status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Operating status - more info | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Vet Center | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center | Main location address  | field_address | Address |  | 1 | Address | Translatable |
| Content type | Vet Center | Hours | field_office_hours | Office hours |  | Unlimited | Office hours (week) |  |
| Content type | Vet Center | Facility photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Vet Center | Hours details | field_cc_non_traditional_hours | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center | Direct line | field_phone_number | Telephone number |  | 1 | Telephone number | Translatable |
| Content type | Vet Center | Prepare for your visit accordions | field_prepare_for_visit | Entity reference revisions |  | Unlimited | Paragraphs EXPERIMENTAL |  |
| Content type | Vet Center | Call center information | field_cc_vet_center_call_center | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Featured content | field_vet_center_feature_content | Entity reference revisions |  | 2 | Paragraphs EXPERIMENTAL |  |
| Content type | Vet Center | Vet Center faqs | field_cc_vet_center_faqs | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Nationally featured Vet Center content | field_cc_vet_center_featured_con | Entity Field Fetch field |  | 1 | Entity Field Fetch widget |  |
| Content type | Vet Center | Services | field_health_services | Entity reference |  | Unlimited | Inline entity form - Complex - Table View Mode |  |
| Content type | Vet Center | Table of contents | field_table_of_contents | Markup |  | 1 | Markup |  |
| Content type | Vet Center - Community Access Point | Address | field_address | Address | Required | 1 | Address | Translatable |
| Content type | Vet Center - Community Access Point | Details | field_operating_status_more_info | Text (plain, long) |  | 1 | Textarea (multiple rows) with counter | Translatable |
| Content type | Vet Center - Community Access Point | Facility Locator API ID | field_facility_locator_api_id | Text (plain) |  | 1 | -- Disabled -- | Translatable |
| Content type | Vet Center - Community Access Point | Status | field_operating_status_facility | List (text) | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Community Access Point | Access point name | field_geographical_identifier | Text (plain) | Required | 1 | Textfield |  |
| Content type | Vet Center - Community Access Point | Hours | field_office_hours | Office hours |  | Unlimited | Office hours (week) | Translatable |
| Content type | Vet Center - Community Access Point | Facility photo | field_media | Entity reference |  | 1 | Media library | Translatable |
| Content type | Vet Center - Community Access Point | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Community Access Point | Main Vet Center location | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Community Access Point | Table of contents | field_table_of_contents | Markup |  | 1 | Markup | Translatable |
| Content type | Vet Center - Community Access Point | How should CAP hours be communicated? | field_vetcenter_cap_hours_opt_in | Boolean | Required | 1 | Check boxes/radio buttons |  |
| Content type | Vet Center - Facility Service | Description | field_body | Text (formatted, long) |  | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center - Facility Service | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Facility Service | Service | field_service_name_and_descripti | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Facility Service | Vet Center | field_office | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Locations List | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Vet Center - Locations List | Page introduction | field_intro_text | Text (plain, long) | Required | 1 | Text area (multiple rows) | Translatable |
| Content type | Vet Center - Locations List | Nearby Mobile Vet Centers | field_nearby_mobile_vet_centers | Entity reference |  | Unlimited | Entity Browser - Table |  |
| Content type | Vet Center - Locations List | Nearby Vet Centers and Outstations | field_nearby_vet_centers | Entity reference |  | Unlimited | Entity Browser - Table |  |
| Content type | Vet Center - Locations List | Vet Center | field_office | Entity reference | Required | 1 | Select list | Translatable |
