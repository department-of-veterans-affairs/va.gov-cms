@api
Feature: Workflow
  In order to ensure its readiness and compliance
  As a site owner
  I want my content to go through workflow prior to publication.

  @dst @workflow
     Scenario: Workflow
       Then exactly the following workflows should exist
       | Label | Machine name | Type |
| Editorial workflow | editorial | Content moderation |

  @dst @workflow_states
     Scenario: Workflow states
       Then exactly the following workflow states should exist
       | Workflow | Label | Machine name |
| Editorial workflow | Approved | approved |
| Editorial workflow | Archived | archived |
| Editorial workflow | Draft | draft |
| Editorial workflow | Published | published |
| Editorial workflow | In review | review |

  @dst @workflow_transitions
     Scenario: Workflow transitions
       Then exactly the following workflow transitions should exist
       | Workflow | Label | Machine name | From state | To state |
| Editorial workflow | Approve | approve | In review | Approved |
| Editorial workflow | Archive | archive | Published | Archived |
| Editorial workflow | Archive | archive | Approved | Archived |
| Editorial workflow | Archive | archive | Draft | Archived |
| Editorial workflow | Archive | archive | In review | Archived |
| Editorial workflow | Restore from archive | archived_published | Archived | Published |
| Editorial workflow | Edit | create_new_draft | Published | Draft |
| Editorial workflow | Edit | create_new_draft | Draft | Draft |
| Editorial workflow | Edit | create_new_draft | Archived | Draft |
| Editorial workflow | Edit | create_new_draft | In review | Draft |
| Editorial workflow | Publish | publish | Published | Published |
| Editorial workflow | Publish | publish | In review | Published |
| Editorial workflow | Publish | publish | Approved | Published |
| Editorial workflow | Publish | publish | Draft | Published |
| Editorial workflow | Review | review | In review | In review |
| Editorial workflow | Review | review | Draft | In review |
