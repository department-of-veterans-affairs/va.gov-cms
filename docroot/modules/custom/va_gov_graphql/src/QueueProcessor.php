<?php

namespace Drupal\va_gov_graphql;

use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use GraphQL\Executor\Promise\PromiseAdapter;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Server\OperationParams;
use GraphQL\Server\ServerConfig;

/**
 * A Queue processor class to override default behavior of caching.
 */
class QueueProcessor extends QueryProcessor {

  /**
   * Override Caching operation to not cache.
   *
   * @param \GraphQL\Executor\Promise\PromiseAdapter $adapter
   *   Adapter.
   * @param \GraphQL\Server\ServerConfig $config
   *   Serverconfig.
   * @param \GraphQL\Server\OperationParams $params
   *   Operation Params.
   * @param \GraphQL\Language\AST\DocumentNode $document
   *   Documetn Node.
   * @param bool $validate
   *   Should validate?
   *
   * @return \GraphQL\Executor\Promise\Promise|mixed
   *   The promise.
   */
  protected function executeCacheableOperation(PromiseAdapter $adapter, ServerConfig $config, OperationParams $params, DocumentNode $document, $validate = TRUE) {
    return $this->executeUncachableOperation($adapter, $config, $params, $document, $validate);
  }

}
