<?php

namespace Drupal\va_gov_migrate\Obtainer;

use Drupal\migration_tools\Obtainer\ObtainTitle;
use Drupal\migration_tools\StringTools;

/**
 * Like ObtainTitle, but doesn't affect case.
 *
 * @package Drupal\va_gov_migrate\Obtainer
 */
class ObtainTitleNoCaseChange extends ObtainTitle {

  /**
   * {@inheritdoc}
   */
  public static function cleanString($text) {
    // Breaks need to be converted to spaces to avoid lines running together.
    // @codingStandardsIgnoreStart
    $break_tags = ['<br>', '<br/>', '<br />', '</br>'];
    // @codingStandardsIgnoreEnd
    $text = str_ireplace($break_tags, ' ', $text);
    $text = strip_tags($text);
    // Titles can not have html entities.
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // There are also numeric html special chars, let's change those.
    $text = StringTools::decodeHtmlEntityNumeric($text);

    // We want out titles to be only digits and ascii chars so we can produce
    // clean aliases.
    $text = StringTools::convertNonASCIItoASCII($text);
    // Remove undesirable chars and strings.
    $remove = [
      '&raquo;',
      '&nbsp;',
      '»',
      // Weird space character.'.
      ' ',
    ];
    $text = str_ireplace($remove, ' ', $text);

    // Remove white space-like things from the ends and decodes html entities.
    $text = StringTools::superTrim($text);
    // Remove multiple spaces.
    $text = preg_replace(['/\s{2,}/', '/[\t\n]/'], ' ', $text);

    return $text;
  }

}
