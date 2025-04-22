# Form Builder Paragraph Actions

This document describes how to work with paragraph actions in the VA.gov Form Builder system.

## Overview

The Form Builder uses a flexible action system for paragraphs that allows for:
- Standard actions (move up, move down, delete)
- Custom actions for specific paragraph types
- Access control for actions
- Extensible action definitions

## Adding a New Paragraph Type

To add a new paragraph type that works with the action system:

1. Create a new paragraph bundle in Drupal (e.g., `digital_form_my_type`)
2. Create a PHP class that extends `FormBuilderParagraphBase`:

```php
<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph;

/**
 * Paragraph of type digital_form_my_type.
 */
class MyTypeParagraph extends FormBuilderParagraphBase {}
```

3. Register the class in `va_gov_form_builder.module`:

```php
function va_gov_form_builder_entity_bundle_info_alter(array &$bundles): void {
  if (isset($bundles['paragraph']['digital_form_my_type'])) {
    $bundles['paragraph']['digital_form_my_type']['class'] = MyTypeParagraph::class;
  }
}
```

## Using Actions with Paragraphs

### Default Actions

By default, all paragraph types that extend `FormBuilderParagraphBase` get these actions:
- Move Up
- Move Down
- Delete

These are added in the `initializeActionCollection()` method of `FormBuilderParagraphBase`.

### Customizing Action Access

To customize when actions are available, override the `actionAccess()` method in your paragraph class:

```php
public function actionAccess(ActionInterface $action): AccessResult {
  $result = parent::actionAccess($action);
  
  // Add custom access logic here
  if ($action->getKey() === 'delete') {
    $result = $result->andIf(AccessResult::allowedIf($this->canBeDeleted()));
  }
  
  return $result;
}
```

### Customizing Sibling Access

If your paragraph type needs to filter which paragraphs it considers siblings (for move up/down operations), override `getFieldEntities()`:

```php
public function getFieldEntities(): array {
  $parentFieldEntities = parent::getFieldEntities();
  return array_filter($parentFieldEntities, function ($sibling) {
    // Add custom filtering logic here
    return $sibling->bundle() === 'digital_form_my_type';
  });
}
```

## Creating New Actions

To create a new action:

1. Create a new class that extends `ActionBase`:

```php
<?php

namespace Drupal\va_gov_form_builder\Entity\Paragraph\Action;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\va_gov_form_builder\Entity\Paragraph\FormBuilderParagraphInterface;

/**
 * Custom action for paragraphs.
 */
class CustomAction extends ActionBase {
  use StringTranslationTrait;

  public function getTitle(): string {
    return $this->t('Custom Action');
  }

  public function getKey(): string {
    return 'custom';
  }

  public function checkAccess(FormBuilderParagraphInterface $paragraph): bool {
    $result = parent::checkAccess($paragraph);
    // Add custom access logic here
    return $result->isAllowed();
  }

  public function execute(FormBuilderParagraphInterface $paragraph) {
    if (!$this->checkAccess($paragraph)) {
      return;
    }
    
    // Add action execution logic here
  }
}
```

2. Add the action to your paragraph's `initializeActionCollection()` method:

```php
protected function initializeActionCollection(): ActionCollection {
  $collection = parent::initializeActionCollection();
  $collection->add(new CustomAction());
  return $collection;
}
```

### Paragraph-Specific Action Logic

If an action needs different behavior for different paragraph types, you can implement paragraph-specific methods in your action class:

```php
public function executeForMyTypeParagraph(MyTypeParagraph $paragraph) {
  // Custom logic for MyTypeParagraph
}

public function executeForOtherTypeParagraph(OtherTypeParagraph $paragraph) {
  // Custom logic for OtherTypeParagraph
}
```

The system will automatically call the appropriate method based on the paragraph type.

## Using Actions in Controllers

To use actions in a controller:

```php
public function paragraphAction(NodeInterface $node, ParagraphInterface $paragraph, string $action): AjaxResponse {
  $response = new AjaxResponse();
  
  if (method_exists($paragraph, 'executeAction')) {
    $paragraph->executeAction($action);
  }
  
  // Add response handling
  return $response;
}
```

## Best Practices

1. Always check access before executing actions
2. Use the `StringTranslationTrait` for translatable strings
3. Implement proper error handling and user feedback
4. Consider performance when filtering siblings
5. Document custom access rules and action behaviors
6. Test actions thoroughly, especially with edge cases

## Common Patterns

### Moving Paragraphs

```php
public function execute(FormBuilderParagraphInterface $paragraph) {
  $siblings = $paragraph->getFieldEntities();
  $parentFieldName = $paragraph->get('parent_field_name')->value;
  $parentField = $paragraph->getParentEntity()->get($parentFieldName);
  
  // Find current and next positions
  // Swap positions
  // Save changes
}
```

### Deleting Paragraphs

```php
public function execute(FormBuilderParagraphInterface $paragraph) {
  $siblings = $paragraph->getFieldEntities();
  $parentFieldName = $paragraph->get('parent_field_name')->value;
  $parentField = $paragraph->getParentEntity()->get($parentFieldName);
  
  // Remove from parent field
  // Delete paragraph
  // Save parent
}
```

## Troubleshooting

Common issues and solutions:

1. **Action not appearing**
   - Check if the action is added to the collection
   - Verify access checks are passing
   - Ensure the paragraph class extends `FormBuilderParagraphBase`

2. **Access denied unexpectedly**
   - Review access check logic
   - Check parent entity access
   - Verify paragraph is not new

3. **Action not executing**
   - Check for proper method implementation
   - Verify error handling
   - Ensure parent entity is saved after changes 