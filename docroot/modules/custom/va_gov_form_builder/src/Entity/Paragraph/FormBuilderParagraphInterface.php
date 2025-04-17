<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

use Drupal\Core\Access\AccessResult;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionCollection;
use Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface;

/**
 * An interface for Form Builder Paragraphs that may have executable actions.
 */
interface FormBuilderParagraphInterface extends ParagraphInterface {

  /**
   * Retrieve the “group” of related field items from the same parent field.
   *
   * This method collects field items from the parent field
   * (e.g. `field_chapters`) that are considered part of the same group as this
   * item, based on the grouping logic you implement (for example, matching
   * paragraph bundle/type or other criteria). The returned set will always
   * include the current item; if no items meet the grouping criteria, it falls
   * back to returning the full set of siblings in the parent field.
   *
   * @return \Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList
   *   Grouped results as field item list.
   */
  public function getFieldItemGroup(): EntityReferenceRevisionsFieldItemList;

  /**
   * Dispatch to the given Action.
   *
   * @param \Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface $action
   *   The action to perform.
   */
  public function accept(ActionInterface $action);

  /**
   * Gets the ActionCollection for this Paragraph.
   *
   * @return null|ActionCollection
   *   An ActionCollection or NULL if no ActionCollection has been initialized.
   */
  public function getActionCollection(): ?ActionCollection;

  /**
   * Check if the paragraph allows an action.
   *
   * @param \Drupal\va_gov_form_builder\Entity\Paragraph\Action\ActionInterface $action
   *   The action taking place.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   An AccessResult object. This provides easy chaining of access checks.
   */
  public function actionAccess(ActionInterface $action): AccessResult;

  /**
   * Handler for accepting and then executing an action by action key.
   *
   * This will be called by controllers to perform the action triggered by AJAX.
   *
   * The action will be plucked from the ActionCollection on the Paragraph, if
   * it has been set, and if so, process the action execution.
   *
   * The workflow invokes the Visitor and Command patterns:
   * - AJAX controller calls $paragraph->actionAccess('action-name')
   * - $Paragraph->actionAccess() pulls the Action object from the
   * ActionCollection and passes the Action to the accept() method
   * - The Paragraph (Visitee) dispatches to either an execution method on the
   * Action (Visitor) object for the particular Paragraph
   * (eg: $Action->executeForCustomStep(), or directly to Action->execute().
   * - Action->execute() performs access checks on both the Action (Command)
   * itself and the Paragraph, allowing encapsulation and extensibility.
   * - The executed Action will perform all needed operations including
   * persisting changes to the parent Entity (Node or Paragraph). Drupal
   * Messages can also be set, allowing them to be rendered automatically by
   * Drupal AJAX. To render the messages where desired, the status_messages
   * element type can be used in the AJAX response output.
   *
   * @code
   *   '#type' => 'status_messages'
   * @endcode
   *
   * @param string $action
   *   The action key.
   */
  public function executeAction(string $action): void;

}
