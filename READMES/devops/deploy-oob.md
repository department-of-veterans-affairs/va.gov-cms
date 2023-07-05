# Out-of-Band Deployment

## Introduction

Under certain dire situations, we cannot afford to wait for the daily production deploy to fix an issue that is occurring in production.

Example situations include issues preventing or impairing:

- content release
- editor login
- content workflow
- risk of data loss

In this case, we might elect to perform an out-of-band deployment – a manually-initiated deployment of the CMS codebase to the production environment outside of the normal deployment schedule.

Out-of-band deployments are disruptive, as they:

- require oversight, attention, and manual actions
- cause some downtime for all editors
- require communication with multiple teams
- necessitate other engineers not merging PRs for a time

They also require approval from the product owner, and normally a postmortem procedure to address the inadequacies in processes or tools that led to the issue.

This document should provide guidance and reduce the stress, uncertainty, and likelihood of error while executing an out-of-band deployment.

## Prerequisites

Completing this procedure requires the following:

- an engineer or team member with permissions to view and execute jobs within [Jenkins](https://jenkins.vfs.va.gov)
- approval from the CMS product owner prior to deployment
- a commit resolving the root issue that has passed staging post-deploy tests

## Timeline

It can take a substantial amount of time to fix an issue in production – equal to our full lead time, since we need to diagnose the issue, develop a solution, and then deploy it.

For ease of reference, the following is a breakdown of our current product deployment lead time, with  estimates of how long each step might take to complete and how much time might remain before deployment.

| Step | Time to Complete | Time Remaining After Completion |
| ---- | ---------------- | ------------------------------- |
| Create a PR | ~5 minutes | ~3 hours, 30 minutes |
| Pull Request CI Tests | ~50 minutes | ~2 hours, 30 minutes |
| [`testing/cms`](http://jenkins.vfs.va.gov/job/testing/job/cms/) Jenkins job | ~25 minutes | ~2 hours, 10 minutes |
| [`deploys/cms-vagov-staging`](http://jenkins.vfs.va.gov/job/deploys/job/cms-vagov-staging/) Jenkins job | ~35 minutes | ~90 minutes |
| [`cms/deploy-live-staging`](http://jenkins.vfs.va.gov/job/cms/job/deploy-live-staging/) Jenkins job | ~1 minute | ~90 minutes |
| [`testing/cms-post-deploy-tests-staging`](http://jenkins.vfs.va.gov/job/testing/job/cms-post-deploy-tests-staging/) Jenkins job | ~55 minutes | ~35 minutes |
| [`deploys/cms-auto-deploy`](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/) Jenkins job | ~15 minutes | ~20 minutes |
| [`cms/cms-full-pipeline`](http://jenkins.vfs.va.gov/job/cms/job/cms-full-pipeline/) Jenkins job | ~7 minutes | ~10 minutes |
| [`releases/cms`](http://jenkins.vfs.va.gov/job/releases/job/cms/) Jenkins job | ~1 minute | ~10 minutes |
| [`deploys/cms-vagov-prod`](http://jenkins.vfs.va.gov/job/deploys/job/cms-vagov-prod/) Jenkins job | ~7 minutes | |

## Out-of-Band Deployment Procedure

### Approval

The overarching OOB deployment process for VA.gov does not apply to CMS [1](https://dsva.slack.com/archives/CT4GZBM8F/p1652819017525619), but we need to ask approval for the deploy from the CMS Product Owner.

If the PO is not already aware, we should inform them of:

- the symptoms of the issue and whom it affects (e.g. "editors can't log in")
- a simple explanation of the root cause (e.g. "bad module update", "we still don't know")
- the fix (e.g. "reverted the PR", "applied a patch")
- estimated time to restore service (this may be three hours or more, depending on the current state; see Introduction and Timeline)

### Preparation

Verify that the `testing/cms-post-deploy-tests-staging` job containing the fix commit has completed successfully and that the combined status for the commit in GitHub is passing. 

This can be verified from the [commits listing page](https://github.com/department-of-veterans-affairs/va.gov-cms/commits/main) or with an HTTPS API URL like [this](https://api.github.com/repos/department-of-veterans-affairs/va.gov-cms/commits/bbb7e0e809e17766a5df478c95fb1266d1a654b1/status).

If this has not completed, the actual procedure can not take place.

If a test fails spuriously, the following command can be executed from CMS staging as the `cms` user to force a passing status for that test. Obviously, this should be avoided if at all possible.

```bash
# The test to blame is probably this one.
status_name="va/tests/cypress"
github-status-updater \
  -action=update_state \
  -state=success \
  -context="${status_name}" \
  -description="Success"
```

### Notification

First, notify the Sitewide team, Helpdesk, and Editors of an Out-of-Band deployment and the resulting downtime.

Notify all Drupal engineers in `@cms-engineers-group`  (product teams & CMS Team) in `#sitewide-program` and request that they hold off on merging anything until further notice, as that can delay the testing pipeline.

```slack
:alert: @cms-engineers-group We are preparing for an Out-of-Band deployment.  Please hold off on merging anything until further notice, so as not to delay testing and rollout.  
```

We want to notify the CMS team to minimize surprise and alarm (if they unexpectedly see an unscheduled deployment happen) and for general situational awareness.

### Trigger CMS Release

Only a single Jenkins job needs to be triggered to perform a prod deploy.

This job can be found in [Jenkins](jenkins.vfs.va.gov) at [`Deploys > cms release tag and auto deploy for prod.cms.va.gov`](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/).

To trigger it:

1. Navigate to the [auto-deploy job](http://jenkins.vfs.va.gov/job/deploys/job/cms-auto-deploy/).
2. From the left action banner, select **Build with Parameters**.
3. Change `release_wait` to `15` minutes.
4. Click **Build** and follow the logs.

Nothing will happen for about fifteen minutes, giving you a chance to cancel the job if further complications occur.

The `auto-deploy` job log should contain a line like the following:

```
Commit status: success : 0e1aa59-some-commit-sha-588c12b59
```

The hash mentioned should correspond to a commit containing the fix. If it does not, check the post-deploy tests job and combined status for the commit and confirm that they indicate success. If tests unexpectedly failed or did not complete, it is probably too late to cancel the deploy, and too risky to attempt, but a new deploy can be initiated with minimal delay.

### Verification

Once the deploy shield is lowered, perform these steps:

1. Notify CMS Helpdesk that the deploy has completed successfully, but that the fix has not yet been verified in-place.
2. Verify the fix, or get Helpdesk's help in verifying it.
3. Once the fix is verified, notify Helpdesk that the issue is resolved.

Helpdesk should notify editors and other stakeholders that the issue has been resolved successfully.

## Post-Deployment
Notify all Drupal engineers `@cms-engineers-group`  (product teams & CMS Team) in `#sitewide-program` and let them know they are clear to resume merging code as needed.

```slack
@cms-engineers-group The Out-of-Band deployment is complete.  You may resume merging as needed.  
```
### Postmortem

Chances are that any situation serious enough to require an out-of-band deploy will warrant a postmortem.

To create the postmortem, follow the procedure [here](https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/tree/master/Postmortems). Note that this involves a pull request and review process. Don't just create it in `master` :slightly_smiling_face:

Remember that the purpose of a postmortem is to determine the root causes – the deficits in processes and tools – that made this situation possible, and reduce the likelihood of it happening again. It is not to assign blame, express guilt, etc.
