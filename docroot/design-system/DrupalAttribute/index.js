import _DrupalAttribute from 'drupal-attribute';

class DrupalAttribute {
  constructor() {
    this.drupalAttribute = new _DrupalAttribute();
  }

  addClass(stringOrMap) {
    let stringOrArray;

    if (stringOrMap instanceof Map) {
      stringOrArray = Array.from(stringOrMap.values());
    } else {
      stringOrArray = stringOrMap;
    }

    this.drupalAttribute.addClass(stringOrArray);
    return this;
  }

  hasClass(value) {
    return this.drupalAttribute.hasClass(value);
  }

  removeAttribute(key) {
    this.drupalAttribute.removeAttribute(key);
    return this;
  }

  removeClass(value) {
    this.drupalAttribute.removeClass(value);
    return this;
  }

  setAttribute(key, value) {
    this.drupalAttribute.setAttribute(key, value);
    return this;
  }

  toString() {
    return this.drupalAttribute.toString();
  }
}

export default DrupalAttribute;
