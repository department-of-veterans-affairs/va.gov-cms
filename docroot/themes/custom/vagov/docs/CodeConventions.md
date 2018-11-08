# Code Conventions
## CSS class naming convention
Learn more: https://design.cms.gov/guidelines/code-conventions/
```
.namespace-prefix-BEM--SYNTAX
```

```
.vagov-c-breadcrumb--container
```


* CSS name space: *vagov*

* Prefixes
  * l- layout style ex: *.vagov-l-container*
  * c- component style ex: *.vagov-c-button*
  * u- utility ex: *.vagov-u-color--primary*
  * js- javascript hook *.vagov-js-expand*
  
* [BEM SYNTAX](http://getbem.com/introduction/)
  * Block - a standalone component that is meaningful on it's own. e.g. a button
  * Element - a part of a component that has no standalone meaning and is tied to the component e.g. button text
  * Modifier - a flag on a component or element used to change it's appearance or behavior.