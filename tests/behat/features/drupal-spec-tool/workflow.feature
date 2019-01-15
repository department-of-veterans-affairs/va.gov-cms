@api
Feature: Workflow
  In order to ensure its readiness and compliance
  As a site owner
  I want my content to go through workflow prior to publication.

  @spec @workflow
  Scenario: Workflow
    Then exactly the following workflows should exist
      | Label     | Machine name | Type               |
      | Editorial | editorial    | Content moderation |

  @spec @workflow
  Scenario: Workflow states
    Then exactly the following workflow states should exist
      | Workflow  | Label     | Machine name         |
      | Editorial | Draft     | draft                |
      | Editorial | In review | review               |
      | Editorial | Published | published            |
      | Editorial | Archived  | archived             |
      | Editorial | Staged    | approved_by_reviewer |

  @spec @workflow
  Scenario: Workflow transitions
    Then exactly the following workflow transitions should exist
      | Workflow  | Label                | Machine name         | From state | To state  |
      | Editorial | Create New Draft     | create_new_draft     | Draft      | Draft     |
      | Editorial | Create New Draft     | create_new_draft     | In review  | Draft     |
      | Editorial | Create New Draft     | create_new_draft     | Published  | Draft     |
      | Editorial | Create New Draft     | create_new_draft     | Archived   | Draft     |
      | Editorial | Send to review       | review               | Draft      | In review |
      | Editorial | Send to review       | review               | In review  | In review |
      | Editorial | Publish              | publish              | Draft      | Published |
      | Editorial | Publish              | publish              | In review  | Published |
      | Editorial | Publish              | publish              | Published  | Published |
      | Editorial | Archive              | archive              | Published  | Archived  |
      | Editorial | Restore from archive | archived_published   | Archived   | Published |
      | Editorial | Create New Draft     | create_new_draft     | Staged     | Draft     |
      | Editorial | Publish              | publish              | Staged     | Published |
      | Editorial | Send to review       | review               | Staged     | In review |
      | Editorial | Stage for publishing | stage_for_publishing | Draft      | Staged    |
      | Editorial | Stage for publishing | stage_for_publishing | In review  | Staged    |
      | Editorial | Stage for publishing | stage_for_publishing | Staged     | Staged    |
