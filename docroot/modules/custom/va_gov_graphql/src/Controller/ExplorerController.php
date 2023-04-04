<?php

namespace Drupal\va_gov_graphql\Controller;

use Drupal\graphql\Entity\ServerInterface;
use Drupal\graphql\Controller\ExplorerController as GraphQLExplorerController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Custom GraphQL Explorer.
 */
class ExplorerController extends GraphQLExplorerController {

  /**
   * Controller for the GraphiQL query builder IDE.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $graphql_server
   *   The server.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer(ServerInterface $graphql_server, Request $request) : array {
    $url = $this->urlGenerator->generate("graphql.query.{$graphql_server->id()}");
    $introspectionData = $this->introspection->introspect($graphql_server);

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
