<?php

namespace Drupal\va_gov_form_builder\Element;

use Drupal\Core\Render\Element\Radio;

/**
 * Defines a custom expanded-radio input for a single radio.
 *
 * This is an empty class. The definition needs to be here to define
 * a form element of type `va_gov_form_builder__expanded_radio`,
 * but the class itself does not need to do anything different than
 * the parent class. When this form element is called for an individual
 * radio item of type `va_gov_form_builder__expanded_radios`, it will
 * be rendered by the appropriate template due to theme suggestions
 * defined in `hook_theme` of `va_gov_form_builder.module`.
 *
 * The template used to render this element is
 * `va_gov_form_builder/templates/expanded-radio.html.twig`.
 *
 * @FormElement("va_gov_form_builder__expanded_radio")
 */
class ExpandedRadio extends Radio {
}
