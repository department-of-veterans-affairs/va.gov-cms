## Purpose

This document is designed to provide guidelines for the development and architecture of the CMS.  None of the guidelines are hard rules and this document is a constant work in progress.

## Intgrations

* All integrations should be documented and monitored.
* Integrations with external systems should not in the direct flow of an editor.

## Code Standards

* The Event based verions of a hook using the `hook_event_dispatcher` should be used instead of the direct hook implementation.
* All code which contains business logic must live in a class instead of a function.
* Classes and methods should follow [SOLID](https://en.wikipedia.org/wiki/SOLID) principles. 
* All classes containing business logic must have `phpunit` tests.
* Nested `if`s should be avoided.
* Nested `foreach` loops should be avoided.
* [Value](https://martinfowler.com/eaaCatalog/valueObject.html) objects should be used instead of arrays to store data.

## Resources

* https://phptherightway.com/
* https://designpatternsphp.readthedocs.io/en/latest/README.html
* Book: https://smile.amazon.com/Objects-Patterns-Practice-MATT-ZANDSTRA/dp/1484219953
* Video: https://laracasts.com/series/design-patterns-in-php
* Video: https://symfonycasts.com/tracks/drupal
