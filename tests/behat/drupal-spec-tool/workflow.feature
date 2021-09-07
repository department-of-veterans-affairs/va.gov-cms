@api
Feature: Workflow
  In order to ensure its readiness and compliance
  As a site owner
  I want my content to go through workflow prior to publication.

  @dst @workflow
     Scenario: Workflow
       Then exactly the following workflows should exist
       | Label | Machine name | Type |
       | Content publisher can archive  | editorial     | Content moderation |
       | Only Content Admin can archive | content_admin | Content moderation |

  @dst @workflow_states
     Scenario: Workflow states
       Then exactly the following workflow states should exist
       | Workflow | Label | Machine name |
       | Content publisher can archive  | Approved  | approved  |
       | Content publisher can archive  | Archived  | archived  |
       | Content publisher can archive  | Draft     | draft     |
       | Content publisher can archive  | In review | review    |
       | Content publisher can archive  | Published | published |
       | Only Content Admin can archive | Approved  | approved  |
       | Only Content Admin can archive | Archived  | archived  |
       | Only Content Admin can archive | Draft     | draft     |
       | Only Content Admin can archive | In review | review    |
       | Only Content Admin can archive | Published | published |

  @dst @workflow_transitions
     Scenario: Workflow transitions
       Then exactly the following workflow transitions should exist
       | Workflow | Label | Machine name | From state | To state |
       | Content publisher can archive  | Approve              | approve            | In review | Approved  |
       | Content publisher can archive  | Archive              | archive            | Approved  | Archived  |
       | Content publisher can archive  | Archive              | archive            | Draft     | Archived  |
       | Content publisher can archive  | Archive              | archive            | In review | Archived  |
       | Content publisher can archive  | Archive              | archive            | Published | Archived  |
       | Content publisher can archive  | Edit                 | create_new_draft   | Archived  | Draft     |
       | Content publisher can archive  | Edit                 | create_new_draft   | Draft     | Draft     |
       | Content publisher can archive  | Edit                 | create_new_draft   | In review | Draft     |
       | Content publisher can archive  | Edit                 | create_new_draft   | Published | Draft     |
       | Content publisher can archive  | Publish              | publish            | Approved  | Published |
       | Content publisher can archive  | Publish              | publish            | Draft     | Published |
       | Content publisher can archive  | Publish              | publish            | In review | Published |
       | Content publisher can archive  | Publish              | publish            | Published | Published |
       | Content publisher can archive  | Restore from archive | archived_published | Archived  | Published |
       | Content publisher can archive  | Review               | review             | Draft     | In review |
       | Content publisher can archive  | Review               | review             | In review | In review |
       | Only Content Admin can archive | Approve              | approve            | In review | Approved  |
       | Only Content Admin can archive | Archive              | archive            | Approved  | Archived  |
       | Only Content Admin can archive | Archive              | archive            | Draft     | Archived  |
       | Only Content Admin can archive | Archive              | archive            | In review | Archived  |
       | Only Content Admin can archive | Archive              | archive            | Published | Archived  |
       | Only Content Admin can archive | Edit                 | create_new_draft   | Archived  | Draft     |
       | Only Content Admin can archive | Edit                 | create_new_draft   | Draft     | Draft     |
       | Only Content Admin can archive | Edit                 | create_new_draft   | In review | Draft     |
       | Only Content Admin can archive | Edit                 | create_new_draft   | Published | Draft     |
       | Only Content Admin can archive | Publish              | publish            | Approved  | Published |
       | Only Content Admin can archive | Publish              | publish            | Draft     | Published |
       | Only Content Admin can archive | Publish              | publish            | In review | Published |
       | Only Content Admin can archive | Publish              | publish            | Published | Published |
       | Only Content Admin can archive | Restore from archive | archived_published | Archived  | Published |
       | Only Content Admin can archive | Review               | review             | Draft     | In review |
       | Only Content Admin can archive | Review               | review             | In review | In review |
