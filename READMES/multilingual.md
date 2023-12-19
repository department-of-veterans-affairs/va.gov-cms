# Multilingual content in the VA.gov CMS
## Setting up new content types for translation
Which content types are available for translation is managed at [Content language](https://prod.cms.va.gov/admin/config/regional/content-language). In order to enable a content type for translation, simply check its box. This will expand to show all the various fields that could be translated. Each of these fields will need to be marked as translatable or not translatable, again by checking their box or not.

When choosing whether a field should or should not be translated, it's important to decide whether the data needs to be different on the translation. Fields that will usually be translated:
* Most text, including text that contains HTML
* Images
* Published status & moderation state
* Fields that track regulatory status, such as 'Last saved by an editor', 'Plain language certification'

Fields that will usually not be translated:
* Reference fields to things like taxonomy, author, or nodes (though, see below about references)
* Dates when the content was created
* Text that is used as machine names, i.e. a text field that selects a banner as having an 'info' or 'warning' type

Fields that will *never* be translated:
* Paragraph fields cannot be translated (but see below about references)

### Dealing with references
In almost all cases, references fields should not be set to be translatable. The fact that a piece of content references a 'Disability benefits' taxonomy term should not be different between English and Tagalog. When it is translated into Tagalog, it will still be about Disability benefits. It will be rare that one would want to change the data.

However, the thing that is pointed to *will* be need to be translated. The taxonomy term 'Disability benefits' will need to be translated to other languages.

When setting up a content type for translation, you will need to identify each entity that is referenced from the 'parent'. For any field that references another entity, it is necessary to catalog each type of entity that is referenced, and set it up for translation as well. So for example:
* For a taxonomy field, any vocabularies referenced will need to be set up to be translated
* Any paragraph type that is allowed in a given paragraph field will need to be set up to be translated
* If the 'parent' references a node type, that node type will need to be set up for translation.

Each of these child entity types needs to go through the same translation setup as the parent, if it is not already set up. The process is the same as setting up the parent, and the considerations for which fields should be translated or not are the same.

It is important also to remember that these child entities will often reference entities themselves. Paragraph entities often contain paragraph fields. These 'grandchildren' *also* need to be set up for translation, and any child entities they have will also need to be translated. It is something like Russian nesting dolls - every level needs to be set up.

Fortunately, if you are setting up a new entity type for translation, frequently some or all of the referenced entity types will already be set up for translation.
