<?php

namespace Drupal\va_gov_post_api\Service;

use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a common interface va gov post api push services.
 */
interface PostServiceInterface {

  /**
   * Determines if the entity is such that it is covered by the push.
   *
   * Usually this is a content type covered by the push.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity (usually a node, but not required).
   *
   * @return bool
   *   TRUE if a pushable entity, FALSE otherwise.
   */
  public static function isPushAble(EntityInterface $entity);

  /**
   * Determines if the entity is such should be assembled and pushed.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity (usually a node, but not required).
   * @param bool $forcePush
   *   Process due to referenced entity updates.
   *
   * @return bool
   *   TRUE if should be pushed, FALSE otherwise.
   */
  public static function shouldPush(EntityInterface $entity, bool $forcePush = FALSE);

}
