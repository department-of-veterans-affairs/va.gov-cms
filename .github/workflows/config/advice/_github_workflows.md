## GitHub Workflows (.github/workflows/*.yml)

We use GitHub Workflows to accomplish the following important tasks:

- perform continuous integration (CI) tests on each commit
- initiate certain scheduled tasks, such as pruning older file backups
- provide contextual advice (like this!) in pull request comments

Performing these tasks means that our workflows must have privileges provided by access credentials.  Therefore, we have to monitor our use of third-party actions, embedded scripts, and other resources, and follow best practices in developing and maintaining our workflows.

Please follow the following guidelines:

- Pin all GitHub Actions at a specific commit.  A tag is not sufficient, as it does not protect us against the third party losing control over their account.  A commit SHA should protect us against any anticipated injection.

- Review the source code of the action at the commit you are pinning.  This should be performed again whenever the action is updated, for whatever reason.

- Do not attempt to work around GitHub's protective measures.  If you're trying to accomplish something in a GitHub Action that requires write permissions on a pull request, use the `pull_request_target` event instead and consult the examples in this repository.

- If you are struggling to test your action because it uses `pull_request_target`, and therefore needs to be merged to `main` to take effect, use the [CMS Test repository](https://github.com/department-of-veterans-affairs/va.gov-cms-test/) instead.  This will incur less suffering for all involved.

- Be careful with user-provided input, especially in scripts.  For example, do not read PR titles, comment bodies, or even the names of changed files in such a way that they might be executed as commands.

See [here](https://docs.github.com/en/actions/security-guides/security-hardening-for-github-actions) for more details concerning GitHub Actions and security.
