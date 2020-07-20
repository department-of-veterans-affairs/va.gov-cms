# EWA Rules of Engagement

Editorial Workflow & Assignments (EWA) module will be contributed to Drupal.org
as a part of a broader Drupal Workflow contrib ecosystem.

[Drupal.org now supports issue forks and branches](https://glamanate.com/blog/new-issue-forks-functionality-going-be-awesome),
but the collaboration process still involves creation of patch files in order
to support code merges. Even though development directly on Drupal.org is
preferred, the process of code reviews and feedback is still poorly supported.

We will use a public GitHub repository for EWA development in order to
streamline collaboration process.

https://github.com/agilesix/workflow_assignments 

EWA module machine name is `workflow_assignements` and it is currently included
in va.gov-cms codebase as `drupal/workflow_assignments` composer package.

Collaboration in GitHub vs. Drupal.org or in a custom module in va.gov-cms repo
offers several advantages:
* feedback loops are streamlined and happen in the same channel that we use
day-to-day - GitHub
* we don't spend extra time on managing Drupal.org repository and issue queue.
We'll start managing Drupal.org repo once development slows down
* commit authors are preserved and once a stable version of the module is moved
to Drupal.org, all committers will be credited automatically. Please ensure that
your github user email is what you use on your DO profile in order to support
work credits
* `workflow_assignments` module is included in the project codebase as a
contrib module. When the time comes to pull it from Drupal.org, the namespace and
files location will remain the same, so no additional effort is needed besides
updating composer version/config.

## Contributing to EWA

### 1. Verify Git remote configuration and version in `workflow_assignments`
contrib module directory

* ensure the latest version of `workflow_assignments` is pulled on local
development environment `lando composer install`
* `cd docroot/modules/contrib/workflow_assignments`
* `git status` will show current branch name
* `git remote -v` should show 
`https://github.com/agilesix/workflow_assignments.git` as remote origin

### 2. PR collaboration process

#### Code contributor

1. `cd docroot/modules/contrib/workflow_assignments`
1. switch to a new branch within EWA module directory - 
`git checkout -b VACMS-1234-branch-name`
1. add code changes and run `git status` in the same directory to verify your
changes are detected
1. run PHPCS check locally before committing
   * `cd ../../../../ `
   * `lando composer va:test:cs`
   * address errors in `workflow_assignments`, if any
1. `cd docroot/modules/contrib/workflow_assignments`
1. commit changes, push to `workflow_assignment` repo - 
`git push --set-upstream origin VACMS-1234-branch-name` and open a PR with QA
instructions in `https://github.com/agilesix/workflow_assignments`

**If your work is dependent on Drupal config that lives in va.gov-cms repo:**
* open a separate PR with updated configuration in va.gov-cms repo
* link to corresponding PR from `https://github.com/agilesix/workflow_assignments`
in PR description and provide any specific instructions for your peer to review

#### Peer reviewer

1. review related code and instructions in both github repos
   * `https://github.com/agilesix/workflow_assignments`
   * `https://github.com/department-of-veterans-affairs/va.gov-cms`
1. test functionality locally:
   * `cd docroot/modules/contrib/workflow_assignments`
   * `git fetch` to fetch any new PR branches
   * `git checkout branch-name-to-review`
1. follow QA instructions in the PR, e.g. import new configuration, etc.
1. **IMPORTANT**: once the work provided in `workflow_assignments` repo is
reviewed and merged, the composer.json/.lock files in va.gov-cms repo should be
updated to use latest version of `workflow_assignments` module.
   * `lando composer update drupal/workflow_assignments`
   * commit and merge to va.gov-cms `master` branch

NOTE: reach out to Oksana Cyrwus or Neil Hastings with any questions regarding
the outlined process.
