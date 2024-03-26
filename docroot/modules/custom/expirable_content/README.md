## INTRODUCTION

The Expirable Content module is a module that allows any content entity to have an expiration date. The expiration date can be calculated using any date field or token value. Similarly, there is a configurable warning date that can trigger an event for the set number of days before the expirationn date.

When an expiration date is configured for a content entity bundle, a new field is added to that entity to track the expiration date. Each time an entity is saved, the system will attempt to populate the field if there is a configured expirable content entity for that entity.

The primary use case for this module is:

- Email an author to warn them of content that is expiring.
- Show the expiration date of the node
- Expose the expiration date to a Schema.org property for SEO purposes.

## REQUIREMENTS

Field module.

## INSTALLATION

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

## CONFIGURATION

None.

## MAINTAINERS

Current maintainers for Drupal 10:

- Daniel Sasser (coderdan) - https://www.drupal.org/u/coderdan

