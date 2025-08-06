# Node Relationships GraphQL Implementation

This implementation adds a custom GraphQL field resolver to track and analyze node relationships in the VA.gov CMS.

## GraphQL Usage

Once the cache is cleared and GraphQL schema is rebuilt, you can query node relationships like this:

```graphql
query GetNodeRelationships($id: String!) {
  nodeById(id: $id) {
    ... on NodeInterface {
      id
      title
      
      # New relationships field
      relationships {
        # Entities this node references
        references {
          id
          title
          entityType
          entityBundle
          fieldName
        }
        
        # Entities that reference this node
        referencedBy {
          id
          title
          entityType
          entityBundle
          fieldName
        }
        
        # Total count of affected entities
        affectedCount
      }
    }
  }
}
```

## Drush Commands

### Analyze Node Relationships
```bash
# Analyze all relationships for a specific node
drush va-gov-graphql:analyze-node 123
drush vagql:analyze 123

# Find entities that would be affected when a node is updated
drush va-gov-graphql:find-affected 123
drush vagql:affected 123 --limit=20
```

## Service Usage

You can also use the service directly in your custom code:

```php
// Get the service
$analyzer = \Drupal::service('va_gov_graphql.node_relationship_analyzer');

// Load a node
$node = \Drupal\node\Entity\Node::load(123);

// Get all relationships
$relationships = $analyzer->getNodeRelationships($node);

// Get only entities that reference this node
$referencing = $analyzer->getReferencingEntities($node, ['limit' => 10]);

// Get counts by entity type
$counts = $analyzer->getAffectedEntityCounts($node);
```

## GraphQL Types

### NodeRelationshipInfo
- `references`: Array of `EntityRelationship` objects
- `referencedBy`: Array of `EntityRelationship` objects  
- `affectedCount`: Integer count of total affected entities

### EntityRelationship
- `id`: Entity ID
- `title`: Entity title/label
- `entityType`: Entity type (e.g., 'node', 'paragraph')
- `entityBundle`: Entity bundle (e.g., 'page', 'news_story')
- `fieldName`: Name of the reference field

## Performance Considerations

- The relationship analysis is performed on-demand and not cached
- Large sites should use the `limit` option in queries/commands
- Consider implementing caching for frequently accessed relationships
- The reverse reference lookup can be expensive on large datasets

## Installation

1. Clear cache: `drush cr`
2. Rebuild GraphQL schema if needed
3. The new `relationships` field will be available on all NodeInterface types

## Files Created

- **Types**: `NodeRelationshipInfo.php`, `EntityRelationship.php`
- **Fields**: `NodeRelationships.php`, `NodeRelationshipReferences.php`, etc.
- **Service**: `NodeRelationshipAnalyzer.php`
- **Commands**: `NodeRelationshipCommands.php`
