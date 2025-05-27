<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

/**
 * A collection of paragraph actions.
 */
class ActionCollection implements \IteratorAggregate {

  /**
   * The list of actions in the collection, keyed by action key.
   *
   * @var ActionInterface[]
   */
  private array $actions = [];

  /**
   * Add an action to the collection.
   *
   * @throws \InvalidArgumentException
   *   Thrown if an action with the same key already exists.
   */
  public function add(ActionInterface $action): void {
    $key = $action->getKey();
    if (isset($this->actions[$key])) {
      throw new \InvalidArgumentException("An action with key '$key' is already registered.");
    }
    $this->actions[$key] = $action;
  }

  /**
   * Get an action by its key.
   *
   * @param string $key
   *   The action key.
   *
   * @return ActionInterface|null
   *   The action if found in the collection or NULL otherwise.
   */
  public function get(string $key): ?ActionInterface {
    return $this->actions[$key] ?? NULL;
  }

  /**
   * Check if an action exists.
   *
   * @param string $key
   *   The action key.
   *
   * @return bool
   *   TRUE if an action exists with the provided key.
   */
  public function has(string $key): bool {
    return isset($this->actions[$key]);
  }

  /**
   * Remove an action from the collection.
   *
   * @param string $key
   *   The action key.
   */
  public function remove(string $key): void {
    unset($this->actions[$key]);
  }

  /**
   * Implement IteratorAggregate to allow foreach iteration.
   *
   * @return \Traversable
   *   The traversable collection.
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->actions);
  }

}
