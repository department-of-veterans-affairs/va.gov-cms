@api
Feature: Workflow
  In order to ensure its readiness and compliance
  As a site owner
  I want my content to go through workflow prior to publication.

  @spec @dst @workflow
  Scenario: Workflow
    Then exactly the following workflows should exist
      | Label              | Machine name | Type               |
      | Editorial workflow | editorial    | Content moderation |

  @spec @dst @workflow_states
  Scenario: Workflow states
    Then exactly the following workflow states should exist
      | Workflow           | Label     | Machine name         |
      | Editorial workflow | Draft     | draft                |
      | Editorial workflow | In review | review               |
      | Editorial workflow | Published | published            |
      | Editorial workflow | Archived  | archived             |

  @spec @dst @workflow_transitions
  Scenario: Workflow transitions
    Then exactly the following workflow transitions should exist
      | Workflow           | Label                | Machine name         | From state | To state  |
      | Editorial workflow | Create New Draft     | create_new_draft     | Draft      | Draft     |
      | Editorial workflow | Create New Draft     | create_new_draft     | In review  | Draft     |
      | Editorial workflow | Create New Draft     | create_new_draft     | Published  | Draft     |
      | Editorial workflow | Create New Draft     | create_new_draft     | Archived   | Draft     |
      | Editorial workflow | Send to review       | review               | Draft      | In review |
      | Editorial workflow | Send to review       | review               | In review  | In review |
      | Editorial workflow | Publish              | publish              | Draft      | Published |
      | Editorial workflow | Publish              | publish              | In review  | Published |
      | Editorial workflow | Publish              | publish              | Published  | Published |
      | Editorial workflow | Archive              | archive              | Published  | Archived  |
      | Editorial workflow | Restore from archive | archived_published   | Archived   | Published |
