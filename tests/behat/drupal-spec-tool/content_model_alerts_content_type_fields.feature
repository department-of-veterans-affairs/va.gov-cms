@api
Feature: Content model: Alerts Content Type fields
  In order to enter structured content into my site
  As a content editor
  I want to have content type fields that reflect my content model.

  @dst @field_type @content_type_fields @dstfields
     Scenario: Fields
       Then exactly the following fields should exist for bundles "banner,promo_banner" of entity type node
       | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
| Content type | Full Width Alert | Body | body | Text (formatted, long, with summary) | Required | 1 | Textarea with a summary and counter | Translatable |
| Content type | Full Width Alert | Alert type | field_alert_type | List (text) | Required | 1 | Select list | Translatable |
| Content type | Full Width Alert | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Full Width Alert | Paths | field_target_paths | Text (plain) |  | Unlimited | Textfield |  |
| Content type | Full Width Alert | Persistence | field_dismissible_option | List (text) | Required | 1 | Check boxes/radio buttons |  |
| Content type | Promo Banner | Promo type | field_promo_type | List (text) |  | 1 | Select list |  |
| Content type | Promo Banner | URL | field_link | Link | Required | 1 | Linkit | Translatable |
| Content type | Promo Banner | Section | field_administration | Entity reference | Required | 1 | Select list | Translatable |
| Content type | Promo Banner | Paths | field_target_paths | Text (plain) |  | Unlimited | Textfield | Translatable |
