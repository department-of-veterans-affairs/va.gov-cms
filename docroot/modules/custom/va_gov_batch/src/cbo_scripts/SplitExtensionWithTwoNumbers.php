<?php

namespace Drupal\va_gov_batch\cbo_scripts;

use Drupal\codit_batch_operations\BatchOperations;
use Drupal\codit_batch_operations\BatchScriptInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * @file
 * For dual numbers in the phone_number paragraph extension field.
 *
 * For VACMS-20371.
 * This file should be run SECOND, after you run
 * drush codit-batch-operations:run RemoveNonNumericalCharactersFromExtensions .
 * Then, run this file
 * drush codit-batch-operations:run SplitExtensionWithTwoNumbers .
 */
/**
 * Split extensions with two numbers.
 */
class SplitExtensionWithTwoNumbers extends BatchOperations implements BatchScriptInterface {

  /**
   * {@inheritdoc}
   */
  public function getTitle(): string {
    return <<<TITLE
    For:
      - VACMS-20371: https://github.com/department-of-veterans-affairs/va.gov-cms/issues/20371
      - Splitting extensions when there are two
    TITLE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription():string {
    return <<<ENDHERE
    Splits extensions when there are two by keeping the first and
    using the second to create a new phone paragraph
    ENDHERE;
  }

  /**
   * {@inheritdoc}
   */
  public function getCompletedMessage(): string {
    return 'Phone extensions have been processed with a total @total processed and @completed completed.';
  }

  /**
   * {@inheritdoc}
   */
  public function gatherItemsToProcess(): array {

    // Get all extensions that have non-numerical characters.
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
    $message = '';

    // Get phone paragraph information.
    $phone_paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->load($item);
    $phone_parent_id = $phone_paragraph->get('parent_id')->value;
    $phone_parent_field_name = $phone_paragraph->get('parent_field_name')->value;
    // We only need to change two fields.
    if ($phone_parent_field_name !== 'field_phone' && $phone_parent_field_name !== 'field_other_phone_numbers') {
      if ($phone_parent_field_name === 'field_phone_numbers_paragraph') {
        return "The phone data from 'field_phone_numbers_paragraph' '$item'  on 'health_care_local_health_service' has already been migrated to the Service location paragraph previously. This is a vestigial field that is unused.";
      }
      else {
        return "There is no change necessary for paragraph $item.";
      }
    }

    // Do the extension work.
    $original_extension = $phone_paragraph->get('field_phone_extension')->value;
    $separated_extensions = $this->splitExtensions($original_extension);
    // There is something amiss with the extensions.
    if (empty($separated_extensions)) {
      return "Either no change is necessary for paragraph $item or '$original_extension' cannot be successfully separated.";
    }
    $original_label = $phone_paragraph->get('field_phone_label')->value;

    try {
      // Update the first extension and phone.
      if (!empty($separated_extensions[0])) {
        if (!empty($original_label)) {
          $phone_paragraph->set(name: 'field_phone_label', value: "$original_label #1");
        }
        $phone_paragraph->set(name: 'field_phone_extension', value: $separated_extensions[0]);
        $phone_paragraph->save();
        $message = "1st extension for paragraph $item changed from '$original_extension' to '$separated_extensions[0]'. ";
      }

      // Create the second extension and phone.
      if (!empty($separated_extensions[1])) {
        $second_phone = Paragraph::create([
          'type' => 'phone_number',
          'field_phone_number_type' => 'tel',
          'field_phone_number' => $phone_paragraph->get('field_phone_number')->value,
          'field_phone_extension' => $separated_extensions[1],
          'field_phone_label' => "$original_label #2",
          'status' => 1,
          'revision_translation_affected' => 1,
        ]);
        $second_phone->save();
        $second_phone_id = $second_phone->id();
        // Add the 2nd phone to the parent field.
        $phone_parent_paragraph = \Drupal::entityTypeManager()->getStorage('paragraph')->load($phone_parent_id);
        $phone_parent_paragraph->get($phone_parent_field_name)->appendItem($second_phone);
        $phone_parent_paragraph->save();
        $message .= "2nd extension created for paragraph $second_phone_id from '$original_extension' to '$separated_extensions[1]'.";
      }
    }
    catch (\Exception $e) {
      $message = "Exception during update of paragraph id $item with extension '$original_extension'. Reason provided: " . $e->getMessage();
      return $message;
    }

    return $message;
  }

  /**
   * Splits extensions.
   *
   * @param string $dual_extension
   *   The two-part extension.
   *
   * @return array|bool
   *   Array with two extensions (or empty).
   *   E.g. '2132,2995' becomes ['2132','2995']
   */
  public static function splitExtensions(string $dual_extension): array {
    $pattern = '/(^\d+[,|;]\s?\d+)|(^\d+\sor\s\d+)|(^\d+\/\d+)|(^\d+\sthen\s\d+)/i';
    if (!preg_match($pattern, $dual_extension)) {
      return [];
    }
    $first_extension = [];
    preg_match('/^\d+/', $dual_extension, $first_extension);
    $second_extension = [];
    preg_match('/\d+$/', $dual_extension, $second_extension);
    // If either of these are empty, we want to bail out of this.
    if (empty($first_extension[0]) or empty($second_extension[0])) {
      return [];
    }

    return [trim($first_extension[0]), trim($second_extension[0])];
  }

}
