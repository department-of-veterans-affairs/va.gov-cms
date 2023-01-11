# VA.gov-CMS Default Branch Rename

## Purpose

In an effort to use more inclusive language in technology and recognizing that words have meaning and connotation beyond how they are used technically, VA.gov-CMS repository will change the default branch name from `master` to the generally accepted `main`.

## Changes to Tugboat Previews

Once the default branch is renamed the `master` branch will effectively disappear. This will cause the Base Preview built off the `master` branch to fail.

1. In Git UI Rename `master` branch to `main`
1. Build Preview from `main` branch.
1. Uncheck Base Preview on `master`
1. Set Base Preview on `main`

Considerations:

- `master` preview should not be rebuilt as it will fail. 
- Previews that use `master` as a base can be rebuilt but won't include new changes. 
- New PR Previews will use 'main' as the base.
- `master` Preview shouldn't be deleted until all other Previews no longer use it as a base.
- Deleting 'master' Preview will cause all Previews to grow in size which can be problematic.
- Previews that use `master` as a base can be deleted and then built again if they need a `rebuild` 

### Pull-Request Previews

The final consideration point above affects existing PR Previews. Since they are based off the `master` branch base preview they will need to be deleted and recreated. This will ensure that they are based off the newly named `main` branch base preview.

### Demo Envrionments

Demo environments that require changes from prod will need to be deleted and rebuilt.  The major concerns here are:

- Data that must be persisted in the Demo will need to be backed up.
- Demo environment URLs will change when they are recreated.

## Local Repository Changes for Developers

Once the default branch is renamed, developers will need to update their local repos as well. Below are steps from [Github's documentation.](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-branches-in-your-repository/renaming-a-branch)

OLD-BRANCH-NAME = `master`
NEW-BRANCH-NAME = `main`

```
git branch -m OLD-BRANCH-NAME NEW-BRANCH-NAME
git fetch origin
git branch -u origin/NEW-BRANCH-NAME NEW-BRANCH-NAME
git remote set-head origin -a
```

You may have noticed that the steps assumed that your remote name is `origin`. That may or may not be the case for you.  In a concrete example, I use the remote name `upstream` below. You can determine what remote names you use by changing directory to the va.gov-cms repo and entering the below command

```
git remote -v
```

The out put may look similar to:

```
olivereri       git@github.com:olivereri/va.gov-cms (fetch)
olivereri       git@github.com:olivereri/va.gov-cms (push)
upstream        git@github.com:department-of-veterans-affairs/va.gov-cms.git (fetch)
upstream        git@github.com:department-of-veterans-affairs/va.gov-cms.git (push)
upstream-test   git@github.com:department-of-veterans-affairs/va.gov-cms-test.git (fetch)
upstream-test   git@github.com:department-of-veterans-affairs/va.gov-cms-test.git (push)
```

You can see that my personal fork remote is called `olivereri`, the va.gov-cms repo remote is called `upstream` and the va.gov-cms-test repo is called `upstream-test`. With that in mind these are the commands I would run to update my local:

```
git branch -m master main
git fetch upstream
git branch -u upstream/main main
git remote set-head upstream -a
```
