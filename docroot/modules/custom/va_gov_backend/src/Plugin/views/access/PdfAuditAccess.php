<?php

namespace Drupal\va_gov_backend\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\views\access\AccessPluginBase;
use Symfony\Component\Routing\Route;

/**
 * Special access for pdf audit.
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *   id = "php_audit_access",
 *   title = @Translation("PHP Audit Access"),
 *   help = @Translation("Access will be granted to only to super admins and VHA DM users.")
 * )
 */
class PdfAuditAccess extends AccessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function summaryTitle() {
    return $this->t('Customised Settings for the PDF Audit');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    $vha_ids = [
      '29',
      '80',
      '2046',
      // Erika washburn.
      '4433',
      // Dave conlon.
      '1203',
      // Michelle middaugh.
      '1342',
      // Danielle Thierry.
      '1341',
    ];
    $account_roles = $account->getRoles();
    $account_id = $account->id();
    $is_super_admin = in_array('administrator', $account_roles);
    if ($is_super_admin || in_array($account_id, $vha_ids)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route) {
    $route->setRequirement('_access', 'TRUE');
  }

}
