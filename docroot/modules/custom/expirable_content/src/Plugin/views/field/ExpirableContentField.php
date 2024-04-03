<?php

namespace Drupal\expirable_content\Plugin\views\field;

use Drupal\expirable_content\Plugin\views\ExpirableContentJoinViewsHandlerTrait;
use Drupal\views\Plugin\views\field\EntityField;

/**
 * Field handler for the expirable content warning and expiration fields.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("expirable_content_field")
 */
class ExpirableContentField extends EntityField {

  use ExpirableContentJoinViewsHandlerTrait;

}
