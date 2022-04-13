@api
Feature: Media
  In order to present media appropriately
  As a site owner
  I want to have image styles for various contexts.

  @dst @image_styles
     Scenario: Image styles
       Then exactly the following image styles should exist
       | Style name | Machine name |
| 1:1 square medium thumbnail | 1_1_square_medium_thumbnail |
| 2:1 large | 2_1_large |
| 2:1 medium thumbnail | 2_1_medium_thumbnail |
| 2:3 medium thumbnail | 2_3_medium_thumbnail |
| 3:2 medium thumbnail | 3_2_medium_thumbnail |
| 7:2 medium thumbnail | 7_2_medium_thumbnail |
| Cropped: 2:1 | crop_2_1 |
| Cropped: 2:3 | crop_2_3 |
| Cropped: 3:2 | crop_3_2 |
| Cropped: 7:2 | crop_7_2 |
| Cropped: Freeform | crop_freeform |
| Cropped: Square | crop_square |
| Crop thumbnail | crop_thumbnail |
| Full content width with no upscaling | full_content_width |
| GeofieldMap Default Icon Style (34x34) | geofield_map_default_icon_style |
| Large (480×480) | large |
| Linkit result thumbnail | linkit_result_thumbnail |
| media_library | media_library |
| Medium (220×220) | medium |
| Original | original |
| Thumbnail (100×100) | thumbnail |
| Viewport width | viewport_width |

@dst @image_effects
     Scenario: Image effects
       Then exactly the following image effects should exist
       | Image style | Effect | Summary |
| 1:1 square medium thumbnail | Manual crop | uses Square crop type |
| 1:1 square medium thumbnail | Scale and crop | 240×240 |
| 1:1 square medium thumbnail | Scale | 240×240 |
| 2:1 large | Manual crop | uses 2:1 crop type |
| 2:1 large | Scale and crop | 1024×512 |
| 2:1 medium thumbnail | Manual crop | uses 2:1 crop type |
| 2:1 medium thumbnail | Scale and crop | 480×240 |
| 2:3 medium thumbnail | Manual crop | uses 2:3 crop type |
| 2:3 medium thumbnail | Scale and crop | 320×480 |
| 3:2 medium thumbnail | Scale and crop | 480×320 |
| 3:2 medium thumbnail | Manual crop | uses 3:2 crop type |
| 7:2 medium thumbnail | Scale and crop | 1050×300 |
| 7:2 medium thumbnail | Manual crop | uses 7:2 crop type |
| Crop thumbnail | Scale | width 400 |
| Cropped: 2:1 | Manual crop | uses 2:1 crop type |
| Cropped: 2:3 | Manual crop | uses 2:3 crop type |
| Cropped: 3:2 | Manual crop | uses 3:2 crop type |
| Cropped: 7:2 | Manual crop | uses 7:2 crop type |
| Cropped: Freeform | Manual crop | uses Freeform crop type |
| Cropped: Square | Manual crop | uses Square crop type |
| Full content width with no upscaling | Scale | width 1400 |
| GeofieldMap Default Icon Style (34x34) | Resize | 34×34 |
| Large (480×480) | Scale | 480×480 |
| Linkit result thumbnail | Scale and crop | 50×50 |
| Medium (220×220) | Scale | 220×220 |
| Original | Manual crop | uses Original crop type |
| Thumbnail (100×100) | Scale | 100×100 |
| Viewport width | Scale | width 2500 |
