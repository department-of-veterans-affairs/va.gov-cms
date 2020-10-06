# Graph QL

The site uses GraphQL (https://www.drupal.org/project/graphql) as the mechanism for delivering JSON to Metalsmith for the static site build (see https://github.com/department-of-veterans-affairs/vets-website for info and setup).

The GraphQL endpoint is at `/graphql`. GraphQL Explorer to assist in writing queries is available via the CMS admin at: `/graphql/explorer`. Sample GraphQL query to grab all entities in the system:

```
query {
  nodeQuery()  {
    entities {
      entityLabel
      entityType
    }
  }
}
```


## Drupal + GraphQL

The Drupal GraphQL module automatically generates a schema that includes every entity type in Drupal - both default and created by us. Every single node, paragraph, field, menu, block, bundle, config, etc. on the site is queryable out of the box after enabling the module without any additional configuration. We certainly have the option to extend that generated schema if there’s a need to do so, but in terms of getting access to the site’s entities and their data for use in Metalsmith - what we need is already built out for us by the GraphQL module: https://www.drupal.org/project/graphql

The GraphQL Drupal module also comes with an explorer (accessible on the DEV site at https://dev.cms.va.gov/graphql/explorer when logged in) that has extremely robust automatically generated documentation for querying (and modifying if you want) every entity on the site. You can search for any entity and find what data is available for querying and what methods and variables you can use to filter and sort results. You can also create test queries - with autocomplete functionality included - and see their results in the Drupal admin or see why they aren’t working as expected thanks to helpful errors.

Using GraphQL would not preclude us from also using JSONAPI if we want to on this project or on a future application that consumes Drupal’s content API. They can both be enabled at once and can even be used side by side if we have a burning desire to do so. This isn’t a call that’s going to lock us into a particular technology stack now or in the future.

This series of blog posts is helpful for wrapping your head around Drupal + GraphQL: https://www.amazeelabs.com/en/blog/introduction-graphql & https://www.amazeelabs.com/en/blog/drupal-and-graphql-react-and-apollo. Note that these posts are a bit old and reference Drupal modules that are longer actively developed because their functionality has been integrated into the main Drupal GraphQL & GraphQL core modules. The core concepts are the same though.



## GraphQL Endpoint Authentication

We can use the Basic Auth module in Drupal 8 core to restrict just the Graphql endpoint path (/graphql) to a specific user.

Credentials are sent in header of request. The Basic Auth module takes a username and password out of the request and authenticates them against Drupal. Docs: https://www.drupal.org/docs/8/core/modules/basic_auth/overview



### Example GraphQL query

This query retrieves pages from Drupal with field_introtext and field_ContentBlock:
```
gql`
        {
            nodeQuery{
                count
                entities {
                    ... on NodePage {
                        nid
                        entityBundle
                        entityPublished
                        title
                        fieldIntroText
                        fieldContentBlock {
                            entity {
                                ... on Paragraph {
                                    id
                                    entityBundle
                                    entityRendered
                                }
                            }
                        }
                    }
                }
            }
        }
        `;
```

The response to any query is in JSON.


## Metalsmith GraphQL plugin

Code: https://github.com/department-of-veterans-affairs/va.gov-cms/blob/develop/metalsmith_app/custom_plugins/metalsmith-graphql/index.js

This plugin connects to the Drupal content API and retrieves node and entity data using the GraphQL endpoint.

To run, from project root: cd metalsmith_app && node index.js

[Table of Contents](../README.md)
