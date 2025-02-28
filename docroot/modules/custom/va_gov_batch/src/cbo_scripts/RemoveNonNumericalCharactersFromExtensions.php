<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;

/**
 * @file
 * For non-numerical characters in the phone_number paragraph extension field.
 *
 * For VACMS-20371.
 * This file should be run first.
 * drush codit-batch-operations:run RemoveNonNumericalCharactersFromExtensions .
 * Then, run the next file.
 * drush codit-batch-operations:run SplitExtensionWithTwoNumbers .
 */
/**
 * Remove non-numerical characters from most extensions.
 */
class RemoveNonNumericalCharactersFromExtensions extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-20371: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20371.
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Updates the phone extension field in the Phone number paragraph
    to remove non-numerical characters.
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Non-numerical characters have been removed with a total @total processed and @completed completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {
    return \Drupal::database()
      ->select('paragraph__field_phone_extension', 'p')
      ->fields('p', ['entity_id'])
      ->condition('field_phone_extension_value', '[^0-9]', 'REGEXP')
      ->execute()
      ->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function processOne(string $key, mixed $item, array &$sandbox): string {
    $phone_paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->load($item);
    if (empty($phone_paragraph)) {
      return "No paragraph was found to update for item $item.";
    }
    try {
      $original_extension = $phone_paragraph->get('field_phone_extension')->value;
      if (empty($original_extension)) {
        return "No extension found for paragraph id $item";
      }
      $phone_parent_field_name = $phone_paragraph->get('parent_field_name')->value;
      // Don't try to change 'field_phone_numbers_paragraph'.
      if ($phone_parent_field_name === 'field_phone_numbers_paragraph') {
        return "The phone data from 'field_phone_numbers_paragraph' '$item'  on 'health_care_local_health_service' has already been migrated to the Service location paragraph previously. This is a vestigial field that is unused.";
      }
      $number_only_extension = $this->replaceNonNumerals($original_extension);
      if ($original_extension === $number_only_extension) {
        return "No change to extension '$original_extension' in paragraph id $item";
      }
      $phone_paragraph->set(name: 'field_phone_extension', value: $number_only_extension);
      $phone_paragraph->save();
      return "Extension updated for paragraph id $item from '$original_extension' to '$number_only_extension'";
    }
    catch (\Exception $e) {
      $message = "Exception during update of paragraph id $item with extension: '$original_extension'";
      return $message;
    }

  }

  /**
   * Replaces non-numerical characters in the extension field.
   *
   * @param string $extension
   *   The extension to be updated.
   *
   * @return string
   *   The changed extension
   */
  public static function replaceNonNumerals(string $extension): string {

    $pattern_to_ignore = '/(^\d+[,|;]\s?\d+)|(^\d+\sor\s\d+)|(^\d+\/\d+)|(^\d+\sthen\s\d+)/i';

    // If the extension contains two numbers separate numbers, don't change it.
    if (preg_match($pattern_to_ignore, $extension) > 0) {
      return $extension;
    }

    // Remove non-numerical characters from the extension.
    $just_numbers = trim(preg_replace('/[^0-9]/', '', $extension));

    return $just_numbers;
  }

}
