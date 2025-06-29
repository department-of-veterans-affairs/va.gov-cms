<?php

namespace Drupal\va_gov_content_release\Frontend;

/**
 * Enum of the possible frontends.
 */
enum Frontend: string implements FrontendInterface {
  case ContentBuild = 'content_build';
  case VetsWebsite = 'vets_website';
  case NextBuild = 'next_build';
  case NextBuildVetsWebsite = 'next_build_vets_website';

  /**
   * {@inheritDoc}
   */
  public function getRawValue(): string {
    return $this->value;
  }

  /**
   * {@inheritDoc}
   */
  public function isContentBuild(): bool {
    return match ($this) {
      self::ContentBuild => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isVetsWebsite(): bool {
    return match ($this) {
      self::VetsWebsite => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isNextBuild(): bool {
    return match ($this) {
      self::NextBuild => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritDoc}
   */
  public function isNextBuildVetsWebsite(): bool {
    return match ($this) {
      self::NextBuildVetsWebsite => TRUE,
      default => FALSE,
    };
  }

}
