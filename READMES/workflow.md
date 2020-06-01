# Workflow
1. [Project](#project)
1. [Git](#git)
    1. Branches
    1. Example Workflow
    1. Pull Request Norms
    1. Merge Conflicts
1. [Drupal SpecTool](#drupal-spectool)
1. [Patching](#patching)
1. [Updates](#updates)
    1. [Updating Drupal/Lightning](#updating-lightning)
    1. [Updating Contrib Modules](#updating-contrib-modules)

## Project
1. Pull a ticket, move to 'In Development'
1. Review the ticket's story, Acceptance Criteria, and Implementation Notes. Raise any questions/concerns/risks in the ticket comments and mention the relevant/appropriate people.
Write a manual test in the Jira ticket to test ticket completion.

## Git
To avoid cluttering up the main repo with lots of branches, fork the repo and push your branches to your fork and make your pull request from your fork to the upstream repo. You can use [`hub`](https://github.com/github/hub) to do this from the command line. Or after you push you will see a link in the output to ctrl + click and create a new PR from the branch you just pushed.

### Branches
 We are currently working off a single `master` branch system. `master` is protected and requires both approval from code review and passing tests to be merged. Commits within pull requests are squashed and merged when they are accepted so that the only relate to one git commit, even if they originally contained multiple commits, the commit messages are added as a bulleted list so they are retained in the merge commit.

### Example Git workflow:

1. `git fetch --all`
1. `git checkout --branch <VAGOV-000-name> origin/master`
1. `lando composer install`
1. `./scripts/sync-db.sh`
1. `./scripts/sync-files.sh` # (optional)
1. Running `lando test` will build the frontend web and run all tests (PHPUnit, Behat, accessibility, FE web) See [testing](testing.md) for additional details.
1. If possible, write your test, before you write code.  The test should fail initially and not pass until you succeed.
1. Fix code formatting issues with CodeSniffer, Drupal 8 standard.
1. Commit your changes. Each commit should be logically atomic (e.g. module adds in one commit, config in another, custom code in additional logical commits), and your commit messages should follow the pattern: "VAGOV-123: A grammatically correct sentence starting with an action verb and ending with punctuation."
_Example: VAGOV-1234 Add configuration for menu reduction._
1. Push work to your fork of the repository so a Pull Request may be created
`git push --set-upstream <fork name> HEAD`
1. Once your PR is merged it will be automatically deployed to dev.cms.va.gov and staging.cms.va.gov. If it is merged before 2:30pm ET it will be in the daily, scheduled deploy to prod.cms.va.gov at 3:30pm ET.

### Pull Request Norms
* Pull requests should be made against the `master` branch.
* Pull Request title should be in the format: "VAGOV-123: Jira ticket title, starting with an action verb and ending with punctuation."
* If your PR is a work in progress or should not be merged, prefix the pull request title with "WIP: " and use the Draft feature.
* Put a link to the ticket at the top of the PR description.
* Add required notes in the PR description:
  1. If the PR requires manual deployment steps (this should never happen).
  1. If the timing of the merging of the PR affects or has dependencies on additional PRs in this or other repositories.
  1. f the PR removes existing functionality that may impact other developers.


### Resolving merge conflicts
Merge conflicts result when multiple developers submit PRs modifying the same code and Git cannot automatically resolve the conflict. For instance, if two developers add update hooks to the same module at the same time, these will necessarily conflict, because update hooks must be numbered in a defined sequence.

Developers are responsible for fixing merge conflicts on their own PRs. Follow this process to resolve a merge conflict:

1.  Fetch upstream history: git fetch upstream
Check out the branch against which you opened your PR (e.g., develop): git checkout develop
1.  Make sure it matches upstream: git reset --hard upstream/develop
1.  Check out your feature branch: git checkout feature/VAGOV-123-short-desc-mi
1.  Merge develop: `git rebase develop`
At this point, Git will complain about a merge conflict. Run git status to find the conflicting file(s).
1.  Edit the files to fix the conflict. The resources at the end of this section provide more information on this process.
1.  Use git add to add all of the files you fixed. (Do not commit them)
Finally, run `git rebase --continue` to finish the merge, and `git push origin VAGOV-123-short-desc-mi -f` to update the PR.
Additional resources:

https://confluence.atlassian.com/bitbucket/resolve-merge-conflicts-704414003.html
https://githowto.com/resolving_conflicts


## Merge Conflict on Composer
If your composer.lock ends up with a conflict due to incoming changes, these steps should safely resolve the conflict.
  1.  Make note of what new packages are coming in from master.
  1.  Make note of what package(s) you were adding.
  1.  Checkout the the incoming changes to composer.
  `git checkout upstream/master -- composer.lock composer.json`
  1.  Replay your package addition(s).
  `composer require {new/package} --update-with-dependencies`
  1.  Run the new updates to make sure you have them locally.
  `composer update {incoming/package}`  - repeat for each incoming package addition
  `composer update {your/package}`  - repeat for each package you were adding
  Your environment can now be tested with the new code.
  Commit the changes to composer.json and composer.lock.


## Drupal SpecTool

* We use the [Drupal SpecTool](https://github.com/acquia/drupal-spec-tool) to keep track of config changes, and generate tests related to roles, content types, fields, menus views.
* If you are modifying configuration of roles, content types, fields, menus views, Go update the appropritate tab(s) in our version of the [SpecTool](https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit?usp=sharing).
* Keep the Bundles and Fields tab sorted with the following columns: 
  1. Entity label (eg first "Content type", then "Custom block type", etc.). 
  2. Bundle label (eg first "Benefits detail page", then "Benefits hub landing page", etc.).
  3. Field label (for the Fields tab, eg first "Alert", then "Featured content", then "Main content").
* Once all the modifications are made to the appropriate tab(s) and cell(s) go to the [Behat](https://docs.google.com/spreadsheets/d/1vL8rqLqcEVfESnJJK_GWQ7nf3BPe4SSevYYblisBTOI/edit#gid=624373408) tab and copy the cell with the tests that would have changed based on what you changed.
* Open the related file in [/tests/behat/drupal/drupal-spec-tool/](../tests/behat/drupal/drupal-spec-too/)
* Delete all the existing text in the file and paste in what you copied from the SpecTool.  (Do not format the output in any way. Disable any Behat beautifier plugins.)
* After updating config, run `lando behat --tags=spec` to run just the spec tool tests. Discrepancies between code and config will be reflected in test output
* If needed, run tests again, correcting and updating the spreadsheet, and exporting accordingly until tests and spreadsheet are in sync.
* Export config to code: `lando drush config:export` then commit test and config changes and make your Pull Request.   Your newly updated Behat tests will run along with the other tests and passing or failure will be indicated on your PR.   Please make sure they are passing locally before sending the PR for code review.


## Patching

Apply patches:
* Get the patch file:
  * example" https://patch-diff.githubusercontent.com/raw/drupal-graphql/graphql/pull/726.patch
  * for Github, you can usually type in `.patch` at the end of the PR url to get the patch file
  * some people use github, some use drupal.org. drupal is moving to gitlab
* In the "`patches`" property of `composer.json`, make an entry for the package you are patching, if not already there, write an explanation lando sync-dbas to what the patch does, and then put the url to the patch
  * ex:
  * ```
    "patches": {
                   "drupal/migration_tools": {
                       "Add changeHtmlContents DomModifier method": "https://www.drupal.org/files/issues/2018-11-26/change_html_contents-3015381-3.patch",
    ```

## Updates
### Updating Core

1. ```lando composer update drupal/core --with-dependencies --dry-run```
This will show you what is to change, without actually changing anything.
2. ```lando composer update drupal/core --with-dependencies```
3. ```lando composer update --lock```
4. ```lando drush updatedb --yes```
5. ```lando drush cache:rebuild```
6. ```lando test```
7. Commit and review your changes.

### Updating Contrib Modules
1. ```lando composer update drupal/MODULE_NAME --with-dependencies```  That will update the composer.lock
2. ```lando drush updatedb --yes```
3. ```lando drush cache:rebuild```
4.  ```lando test``` to make sure nothing broke.
5. Commit your work.

[Table of Contents](../README.md)
