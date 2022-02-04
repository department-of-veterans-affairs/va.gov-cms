INTRODUCTION
------------

The VA.gov Content Type Tour module enhances the usability of the tour_ui module and provides a quick method to create a tour for the
/node/add/{content_type} pages. It aims to accomplish this by providing several abilities. Firstly by enabling this module
the user has the ability to create unique tours for each content_type which isn't a standard tour option as each
/node/add/{content_type} page shares the same route of 'node.add'. Secondly this module makes it much easier for the user
to add tour tips for each field. When on the Manage Tour tab provided by this module, the user can select which fields
on the content_type to create tips for. Each tour tip is initially populated with the help text that is entered for each
field and can then be adjusted if needed through the tour_ui page.

REQUIREMENTS
------------

 This module uses Drupal core's Tour module requires the additional following modules:

 * Tour UI (https://www.drupal.org/project/tour_ui)

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
   https://www.drupal.org/docs/8/extending-drupal-8/overview
   for further information.

CONFIGURATION
------------

 - Permissions
   * The 'administer tour' permission is required to access the Manage Tour form. This is a permission defined by the tour_uid module.

   * Additional permissions are needed to access the tour_ui and tour modules.

 - General Usage

   1. Visit the Manage Tour tab for a content-type: /admin/structure/types/manage/{content_type}/tour.

   2. Enable tour.

   3. Select the fields that should be included on the tour.

   4. Save Configuration.

   5. You will be redirected to the tour UI (/admin/config/user-interface/tour/manage/{tour-id})where you can view the
   content-type tour you just generated. Tips have been generated with the correct data-id and the tip body field has been
   populated with the field helper text if it is present. On this tour UI you can further edit, add, and modify the tour
   tips. See the tour_ui module documentation for more information.

   6. To auto-generate more tips based on content-type fields return to the Manage Tour tab and select additional fields.
   Tour tips can only be deleted through the tour UI.

   7. To disable a tour for a content type simply uncheck the enable tour checkbox on the Manage Tour UI.
