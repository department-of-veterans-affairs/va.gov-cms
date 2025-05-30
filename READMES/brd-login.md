---
layout: default
title: Logging into Running BRD Instances
---

# Logging into Running BRD Instances

_If you need to log in to production or staging, for instance to run a script
to repair or update data after deployment. Generally speaking, this should
only be performed by DevOps personnel, but this document is provided as a
general reference and for onboarding DevOps personnel._

DevOps has kindly provided the [`ssm-session`](https://github.com/department-of-veterans-affairs/devops/tree/master/utilities/ssm-session)
command line tool, which uses your existing AWS credentials to authenticate and
access the running instance.

It should go without saying that if you're attempting a task on _production_, you
should first attempt it on _staging_ to confirm:

- that you understand the steps you need to perform
- that those steps are likely to complete as expected
- that no unexpected effects are likely to result from your actions

## Prerequisites

1. **AWS access**: If this is relevant to your role on the team, this should be
provisioned as part of your onboarding. Your credentials should be configured
for command-line access.
1. **`ssm-session`**: The tool itself should be installed and compiled. This
means installing and configuring the Go programming language and tools.
1. **`aws-mfa`**: Or another tool capable of requesting access tokens from AWS
when provided with your credentials and a Temporary One-Time Password (TOTP)
token from Google Authenticator, 1Password, etc.
1. A working knowledge of UNIX shells like `bash` and `sh`: This is a very
dangerous place to learn command-line basics! If you aren't comfortable with
these tools, please ask someone to pair with you.

## Procedure

### Request an access token with `aws-mfa`

You should see output similar to this, indicating that you have been issued an
access token valid for 43200 seconds, or about twelve hours:

![AWS-MFA Output](https://user-images.githubusercontent.com/1318579/161591834-eb52cfa5-58d0-4df8-b302-299e6646bb80.png)

### Use `ssm-session` to access the running instance

The utility may be invoked as follows: `ssm-session <environment> <application> [auto]`

For our purposes, our _environment_ will normally be one of `vagov-prod` or
`vagov-staging`. These are the environments within which the CMS is deployed.

Our _application_ may be either `cms` or `cms-test`. CMS-Test is normally used
for testing changes to infrastructure and is updated irregularly; if you need
to use it, you'll know. Otherwise, use `cms`.

We will normally append `auto` to indicate that we should connect to the first available instance.  This isn't essential, but usually lets us skip a step.

For example, if you want to connect to CMS-Test Staging, you'd do this:

```bash
$ ssm-session vagov-staging cms-test auto
...
```

and if you want to connect to CMS Prod, you'd do this:

```bash
$ ssm-session vagov-prod cms auto
...
```

### Switch to the CMS user account

`ssm-session` will log you in under the `ssm-user` account, but this is
generally _not_ what you want for performing most tasks. `ssm-user` does not
have useful filesystem permissions or environment variables that ease working
with the deployed application.

The easiest solution is to switch to a ready-made user account:

```bash
$ sudo su - cms
/var/www/cms$
```

This switches to the `cms` user and changes the current working directory to
`/var/www/cms`, where the CMS codebase is installed.  The `cms` user is a
member of the `apache` group and is thus able to read and write most files to
which the Apache web server has access.

### Switch to the Apache user account

In some cases, though, operations you attempt to execute as the `cms` user
might fail, e.g. due to improperly specified file permissions or similar. In
those cases, you can switch to the Apache user and configure the environment
manually.

```bash
$ sudo su - apache -s /bin/bash
/var/www/html$
```

This switches to the Apache user, additionally specifying the shell to use
(this is necessary, since Apache is generally configured as a system account
and not permitted to log in).

```bash
$ source /etc/sysconfig/httpd
$
```

Certain important environment variables (for instance, database credentials,
GitHub API keys, etc) are stored within the above file.  `source` imports them
into the current environment.  Without this step, `drush` and other commands
that bootstrap Drupal directly or otherwise depend on environment variables
will fail.

```bash
$ cd /var/www/cms
/var/www/cms$
```

The CMS codebase is deployed to the above directory.  Without changing the
working directory, certain context-sensitive commands like `task` and `drush`
will fail.

```bash
$ PATH=$PATH:/var/www/cms/bin
$
```

We install some helpful utilities from a variety of sources within the `bin/`
subdirectory.  Adding that directory to the `PATH` environment variable allows
us to execute them easily, and (critically) allows them to execute one another.

### Carry out the original operation

Connected to the correct instance, and with the environment correctly
configured, it should now be possible to perform the original task.

Because all of the environment variables provided to Apache are provided to
your current user, operations should largely follow the format you've used for
developing and testing in your local or Tugboat environment.

For instance, see [the comment in this PR](https://github.com/department-of-veterans-affairs/va.gov-cms/pull/6889#issuecomment-967657412).

A common pattern is to log in and run `drush scr` with an argument:

```bash
$ drush scr ./scripts/VACMS-12345-some-script.php
...
```

This should work without any changes or other significant differences from its
behavior on non-BRD environments.  If you notice unexpected differences in
behavior, it's probably worth consulting someone else on the team and getting
their opinion.

And remember...

**Don't run scripts on production without first running them on staging and
verifying that their behavior matches your expectations!**

----

[Table of Contents](../README.md)
