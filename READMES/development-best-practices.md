## Purpose

This document is designed to provide guidelines for the development and architecture of the CMS.  None of the guidelines are hard rules and this document is a constant work in progress.

## Intgrations

* All upstream integrations should be [documented](https://github.com/department-of-veterans-affairs/va.gov-cms/blob/master/READMES/upstream-dependencies.md) and monitored.
* All downstream integrations should be documented.
* Integrations with external systems should not occur in the direct flow of an editor.

## Code Strucutre

* The Event based verions of a hook using the `hook_event_dispatcher` should be used instead of the direct hook implementation.
* All code which contains business logic must live in a class instead of a function.
* Classes and methods should follow [SOLID](https://en.wikipedia.org/wiki/SOLID) principles. 
* Methods containing business logic must have `phpunit` tests.
* * [Value](https://martinfowler.com/eaaCatalog/valueObject.html) objects should be used instead of arrays to store data.

## Code Readabilty

* Code should be read as a story.
* Comments should explian the `why` while the code explains the what/how.
* Nested `if`s should be avoided.
* Nested `foreach` loops should be avoided.

_inspired by [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)_


## Resources

* https://phptherightway.com/
* https://designpatternsphp.readthedocs.io/en/latest/README.html
* Book: https://smile.amazon.com/Objects-Patterns-Practice-MATT-ZANDSTRA/dp/1484219953
* Book: [Clean Code](https://www.amazon.com/Clean-Code-Handbook-Software-Craftsmanship/dp/0132350882)
* Video: https://laracasts.com/series/design-patterns-in-php
* Video: https://symfonycasts.com/tracks/drupal
