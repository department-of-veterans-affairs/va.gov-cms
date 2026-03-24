<?php

namespace Drupal\va_gov_notifications;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\va_gov_notifications\Entity\ProductOwnerContact;
use Drupal\va_gov_notifications\Entity\ProductOwnerContactInterface;

/**
 * Lists product owner contact config entities.
 */
class ProductOwnerContactListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Name');
    $header['email'] = $this->t('Email');
    $header['products'] = $this->t('Products');
    $header['status'] = $this->t('Status');
    $header['notes'] = $this->t('Notes');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    if (!$entity instanceof ProductOwnerContactInterface) {
      return parent::buildRow($entity);
    }

    $row['label'] = $entity->label();
    $row['email'] = $entity->getEmail();
    $products = $entity->getProducts();
    if (empty($products)) {
      $row['products'] = $this->t('All products');
    }
    else {
      $labels = [];
      foreach ($products as $product_id) {
        $labels[] = ProductOwnerContact::PRODUCT_OPTIONS[$product_id] ?? $product_id;
      }
      $row['products'] = implode(', ', $labels);
    }
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    $row['notes'] = $entity->getNotes();
    return $row + parent::buildRow($entity);
  }

}
