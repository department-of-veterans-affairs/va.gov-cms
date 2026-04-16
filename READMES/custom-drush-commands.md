This list represents all custom drush commands currently active as of September 2024. Please feel free to add or remove commands as needed or to add notes for existing commands.

### Site Status commands

See [SiteStatusCommands.php](../docroot/modules/custom/va_gov_build_trigger/src/Commands/SiteStatusCommands.php).

- `va-gov:disable-deploy-mode` -- Sets the Deploy Mode flag to FALSE. It is not normally necessary to perform this operation manually.
- `va-gov:enable-deploy-mode` -- Sets the Deploy Mode flag to TRUE. It is not normally necessary to perform this operation manually.
- `va-gov:get-deploy-mode` -- Indicates whether the CMS is currently in Deploy Mode, which is a precautionary measure used to prevent content changes while content is being deployed.
- 

### Content release commands

The current system's commands mostly relate to continuous builds and management of the content release state machine:

See [ContentReleaseCommands.php](../docroot/modules/custom/va_gov_build_trigger/src/Commands/ContentReleaseCommands.php).

- `va-gov:content-release:advance-state` -- Advance the state like an external system would do through HTTP.
- `va-gov:content-release:check-scheduled` -- Make sure builds are going out at least hourly during business hours.
- `va-gov:content-release:check-stale` -- If the state is stale, reset the state.
- `va-gov:content-release:get-state` -- Get the current release state.
- `va-gov:content-release:is-continuous-release-enabled` -- Check continuous release state.
- `va-gov:content-release:reset-state` -- Reset the content release state.
- `va-gov:content-release:toggle-continuous` -- Toggle continuous release (enabled vs disabled states)

See [RequestCommands.php](../docroot/modules/custom/va_gov_content_release/src/Commands/RequestCommands.php).

- `va-gov-content-release:request:submit` -- Request a frontend build (but do not initiate it).

See [FrontendVersionCommands.php](../docroot/modules/custom/va_gov_content_release/src/Commands/FrontendVersionCommands.php).

- `va-gov-content-release:frontend-version:get` -- Get the currently selected version of the selected frontend (defaults to `content_build`).
- `va-gov-content-release:frontend-version:reset` -- Reset (to default, or `main`) the currently selected version of the selected frontend (defaults to `content_build`).
- `va-gov-content-release:frontend-version:set` -- Set the currently selected version of the selected frontend (defaults to `content_build`).


### Repository commands

See [RepositorySettingsCommands.php](docroot/modules/custom/va_gov_git/src/Commands/RepositorySettingsCommands.php).
- `va-gov-git:repository-settings:get-names` -- Display the current repositories.
- `va-gov-git:repository-settings:get-path` -- add repository name to end (i.e. va.gov-cms) -- Get the path for the given repository.
- `va-gov-git:repository-settings:get-path-key` -- add repository name to end (i.e. va.gov-cms) -- Get the path key for the given repository.
- `va-gov-git:repository-settings:list` -- List the available repositories and their corresponding paths.

See [RepositoryCommands.php](docroot/modules/custom/va_gov_git/src/Commands/RepositoryCommands.php).
- `va-gov-git:repository:get-last-commit-hash` -- add repository name to end (i.e. va.gov-cms) -- Display the last commit hash for the current branch.
- `va-gov-git:repository:list-remote-branches` -- add repository name to end (i.e. va.gov-cms) -- List remote branches.
- `va-gov-git:repository:search-remote-branches` -- add repository name to end (i.e. va.gov-cms) and search string/term (i.e. discovery) -- List remote branches containing the specified string.


### API commands (these commands require you to shell in to tugboat)

See [ApiClientCommands.php](docroot/modules/custom/va_gov_github/src/Commands/ApiClientCommands.php).
- `va-gov-github:api-client:repository-dispatch` -- add $owner $repository $eventType $apiToken -- Send a repository dispatch event.
- `va-gov-github:api-client:search-issues` -- add $owner $repository $searchString $apiToken $sortField $sortOrder -- Search issues for a repository.
- `va-gov-github:api-client:search-pull-requests` (alias va-gov-github-api-client-search-prs) -- add $owner $repository $searchString $apiToken $sortField $sortOrder -- Search pull requests for a repository.
- `va-gov-github:api-client:workflow-dispatch` -- add $owner $repository $workflowId $reference $apiToken --Send a workflow dispatch event.
- `va-gov-github:api-client:workflow-runs` -- add $owner $repository $workflowId $apiToken -- List workflow runs for a repository and workflow.

See [RawApiClientCommands.php](docroot/modules/custom/va_gov_github/src/Commands/RawApiClientCommands.php).
- `va-gov-github:raw-api-client:current-user:organizations` -- add $apiToken -- List organizational memberships of the current user.
- `va-gov-github:raw-api-client:current-user:repositories` -- add $apiToken -- List repositories accessible by the current user.
- `va-gov-github:raw-api-client:request:get` (alias va-gov-github:raw-api-client:request) -- add $route and $apiToken -- Request any route.


### Migration commands

See [Commands.php](docroot/modules/custom/va_gov_live_field_migration/src/Commands/Commands.php).
- `va-gov-live-field-migration:find` -- add $entityType and $bundle as params -- Find fields that haven't been migrated yet.
- `va-gov-live-field-migration:migrate-field` -- add $entityType and $bundle and $fieldName as params -- Migrate a specific field on a specific content type.
- `va-gov-live-field-migration:rollback-field` -- add $entityType and $bundle and $fieldName as params -- Rollback a specific field on a specific content type.
- `va-gov-live-field-migration:verify` -- add $entityType and $bundle and $fieldName as params


### Global commands

See [Commands.php](docroot/modules/custom/va_gov_live_field_migration/src/Commands/Commands.php).
- `va:gov-clean-revs` (vg-cr) -- Clean up bad node revisions.
- `va_gov_migrate:flag-missing-facilities` -- (alias va-gov-flag-missing-facilities) -- Flag any facilities that no longer exist in Facilty API.
