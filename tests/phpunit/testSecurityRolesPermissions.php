<?php

namespace tests\phpunit;

use weitzman\DrupalTestTraits\ExistingSiteBase;
use Drupal\user\Entity\Role;

/**
 * A test to confirm that roles are associated with the correct permissions.
 */
class SecurityRolesPermissions extends ExistingSiteBase {

  /**
   * Test method to deterine role are associated with the expected permissions.
   *
   * @group security
   *
   * @dataProvider expectedPerms
   */
  public function testSecurityRolesPermissions($roleMatch, $expectedPerms) {
    $role = Role::load($roleMatch);
    $permissions = NULL;

    if (isset($role)) {
      $message = "The permissions for the " . $roleMatch . " do not match the expected permissions.\n";
      $permissions = $role->getPermissions();
    }
    else {
      $message = 'The ' . $roleMatch . ' role is missing from the system.';
    }

    // Test assertion.
    $match = ($expectedPerms == $permissions);

    $this->assertTrue($match, $message);
  }

  /**
   * Returns expected roles amd associated permissions.
   *
   * @return array
   *   Array containing all the roles in the system as an array
   */
  public function expectedPerms() {
    return [
      [
        'anonymous',
        [
          'access content',
        ],
      ],
      [
        'authenticated',
        [
          'access content',
          'access site-wide contact form',
          'execute graphql requests',
          'execute persisted graphql requests',
          'view media',
        ],
      ],
      [
        'content_api_consumer',
        [
          'use graphql explorer',
          'use graphql voyager',
          'view any unpublished content',
          'view own unpublished content',
        ],
      ],
      [
        'content_editor',
        [
          'access administration pages',
          'access content overview',
          'access image_browser entity browser pages',
          'access media_browser entity browser pages',
          'access toolbar',
          'access user profiles',
          'create landing_page content',
          'create media',
          'create page content',
          'create support_service content',
          'delete media',
          'edit any landing_page content',
          'edit any page content',
          'edit any support_service content',
          'edit own landing_page content',
          'edit own page content',
          'edit own support_service content',
          'schedule editorial transition create_new_draft',
          'update any media',
          'use workbench access',
          'view all revisions',
          'view landing_page revisions',
          'view own unpublished content',
          'view page revisions',
          'view the administration theme',
          'view unpublished paragraphs',
        ],
      ],
      [
        'content_reviewer',
        [
          'access administration pages',
          'access content overview',
          'access image_browser entity browser pages',
          'access media_browser entity browser pages',
          'access toolbar',
          'access user profiles',
          'create landing_page content',
          'create media',
          'create page content',
          'create support_service content',
          'delete media',
          'edit any landing_page content',
          'edit any page content',
          'edit any support_service content',
          'edit own landing_page content',
          'edit own page content',
          'edit own support_service content',
          'schedule editorial transition create_new_draft',
          'update any media',
          'use editorial transition archived_published',
          'use editorial transition create_new_draft',
          'use editorial transition review',
          'use editorial transition stage_for_publishing',
          'use workbench access',
          'view all revisions',
          'view any unpublished content',
          'view landing_page revisions',
          'view latest version',
          'view own unpublished content',
          'view page revisions',
          'view the administration theme',
          'view unpublished paragraphs',
          'view workbench access information',
        ],
      ],
      [
        'content_publisher',
        [
          'access administration pages',
          'access content overview',
          'access image_browser entity browser pages',
          'access media_browser entity browser pages',
          'access toolbar',
          'access user profiles',
          'create landing_page content',
          'create media',
          'create page content',
          'create support_service content',
          'delete any landing_page content',
          'delete any media',
          'delete any page content',
          'delete media',
          'delete own landing_page content',
          'delete own page content',
          'edit any landing_page content',
          'edit any page content',
          'edit any support_service content',
          'edit own landing_page content',
          'edit own page content',
          'edit own support_service content',
          'revert all revisions',
          'revert landing_page revisions',
          'revert page revisions',
          'schedule editorial transition archive',
          'schedule editorial transition archived_published',
          'schedule editorial transition create_new_draft',
          'schedule editorial transition publish',
          'update any media',
          'use editorial transition archive',
          'use editorial transition archived_published',
          'use editorial transition create_new_draft',
          'use editorial transition publish',
          'use editorial transition review',
          'use editorial transition stage_for_publishing',
          'use workbench access',
          'view all revisions',
          'view any unpublished content',
          'view landing_page revisions',
          'view latest version',
          'view own unpublished content',
          'view page revisions',
          'view the administration theme',
          'view unpublished paragraphs',
          'view workbench access information',
        ],
      ],
      [
        'admnistrator_users',
        [
          'access toolbar',
          'administer users',
          'assign selected workbench access',
          'batch update workbench access',
          'bypass workbench access',
          'create terms in administration',
          'delete terms in administration',
          'edit terms in administration',
          'view unpublished paragraphs',
          'view workbench access information',
        ],
      ],
    ];
  }

}
