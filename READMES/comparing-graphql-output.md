## Comparing GraphQL Output

The front-end project serializes a representation of the CMS data using a GraphQL query.  It is therefore important for the CMS team to be able to evaluate whether a given change that they've made has impacted the consistency of the CMS' response to that query.

The data is serialized as JSON in a file named `pages.json`.  When the command `composer va:web:build` is executed, this file will be deposited in the folder (relative from the repository root) `web/.cache/vagovdev/drupal/`.

So, by running the build command before and after making a change, we can compare the effect (if any) of the change on the data presented to the front-end by the CMS.

Lists of nodes as returned by the query are not always ordered; consequently even back-to-back queries of an unchanged database can result in wildly different data.  It is therefore necessary to process the `pages.json` file to make it more deterministic and yield results that can be usefully compared.  `jq` is an excellent tool for doing so.

The general workflow is just to create the diff (using e.g. `diff pages-before.json pages-after.json`) and manually review the first few lines of differences, using one copy of `pages.json` to review the context of each difference.  Ideally, it should quickly become apparent that many of the differences are the same objects presented in a different order.  By using a `jq` expression, we can sort those objects consistently to create a more uniform result.

For instance, if we have a structure like this (greatly simplified):

```json
{ "data": {
    "alerts": {
      "entities": [
        {
          "id": "324",
          "name": "Foo"
        },
        {
          "id": "523423",
          "name": "Bar"
        }
      ]
    }
  }
}
```

and the `entities` objects appear in a different order, then we can use the following command to re-sort those objects by their "id" property:

```
# -S sorts the keys of objects upon output, which makes object-level diffs deterministic
#
# '.data.alerts.entities|=sort_by(.id)' sorts the objects within `data.alerts.entities`
# by their `id` property
jq -S '.data.alerts.entities|=sort_by(.id)'
```

And then these conflicts will disappear.

At the time of writing, the following commands were sufficient to create a "consistent" format with few differences from test to test:

```
jq -S '.data.alerts.entities|=sort_by(.id)' pages.json > pages2.json
jq -S '.data.nodeQuery.entities|=sort_by(.entityId)' pages2.json > pages3.json
```

After processing both the "before" and "after" `pages.json` in this way, diffing the two should yield a far more useful visualization of the changes.
