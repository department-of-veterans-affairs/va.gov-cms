<?php

namespace Drupal\va_gov_backend\Plugin\views\access;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
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
  public function summaryTitle(): TranslatableMarkup {
    return $this->t('Customised Settings for the PDF Audit');
  }

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account): bool {
    $account_roles = $account->getRoles();
    $is_super_admin = in_array('administrator', $account_roles);
    $has_pdf_role = in_array('pdf_audit_access', $account_roles);
    if ($is_super_admin || $has_pdf_role) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRouteDefinition(Route $route): void {
    $route->setRequirement('_access', 'TRUE');
  }

}
