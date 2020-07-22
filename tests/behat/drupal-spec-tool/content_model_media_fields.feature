@api
Feature: Content model: Media fields
  In order to enter structured content into my site
  As a content editor
  I want to have media fields that reflect my content model.

  @dst @field_type @media_fields @dstfields
  Scenario: Fields
    Then exactly the following fields should exist for entity type media
      | Type | Bundle | Field label | Machine name | Field type | Required | Cardinality | Form widget | Translatable |
      | Media type | Document | Document | field_document | File | Required | 1 | File |  |
      | Media type | Document | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup |  |
      | Media type | Document | Owner | field_owner | Entity reference | Required | 1 | Select list |  |
      | Media type | Document | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- | Translatable |
      | Media type | Image | Image | image | Image | Required | 1 | ImageWidget crop |  |
      | Media type | Image | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup | Translatable |
      | Media type | Image | Owner | field_owner | Entity reference | Required | 1 | Select list | Translatable |
      | Media type | Image | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- |  |
      | Media type | Video | Media submission guidelines | field_media_submission_guideline | Markup |  | 1 | Markup | Translatable |
      | Media type | Video | Owner | field_owner | Entity reference | Required | 1 | Select list | Translatable |
      | Media type | Video | Reusable | field_media_in_library | Boolean |  | 1 | -- Disabled -- | Translatable |
      | Media type | Video | Video URL | field_media_video_embed_field | Video Embed | Required | 1 | Video Textfield | Translatable |
