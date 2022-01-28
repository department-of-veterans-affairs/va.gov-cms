<?php

namespace Drupal\va_gov_graphql\Controller;

use Drupal\graphql\Controller\ExplorerController as GraphQLExplorerController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom GraphQL Explorer.
 */
class ExplorerController extends GraphQLExplorerController {

  /**
   * Controller for the GraphiQL query builder IDE.
   *
   * @param string $schema
   *   The name of the schema.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer($schema, Request $request) : array {
    $url = $this->urlGenerator->generate("graphql.query.$schema");
    $introspectionData = $this->introspection->introspect($schema);

    return [
      '#type' => 'page',
      '#theme' => 'page__va_gov_graphql_explorer',
      '#attached' => [
        'library' => ['va_gov_graphql/explorer'],
        'drupalSettings' => [
          'graphqlRequestUrl' => $url,
          'graphqlIntrospectionData' => $introspectionData,
          'graphqlQuery' => $request->get('query'),
          'graphqlVariables' => $request->get('variables'),
        ],
      ],
    ];
  }

}
