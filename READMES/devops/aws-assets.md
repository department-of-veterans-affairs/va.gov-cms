# AWS Assets

This is a list of some generic AWS assets that are not directly traceable to this team, i.e. they are not managed in Terraform or other IaC. The purpose of this document is to clarify ownership and collect relevant information so that these assets are more discoverable and their _raison d'être_ more transparent.

## IAM Users (Service Accounts)

The following were added in #5611 to work with certain S3 buckets; these are intended to allow transfer of files from CMS file stores to an S3 bucket designated for public access to those files. These systems are not fully in place yet, but are in progress.

- `svc-dsva-vagov-cms-dev-assets` 
- `svc-dsva-vagov-prod-cms-files`
- `svc-dsva-vagov-prod-cms-test-files`
- `svc-dsva-vagov-staging-cms-files`
- `svc-dsva-vagov-staging-cms-test-files`

The following account may be necessary for GitHub Actions workflows to interact with AWS resources.

- `svc-gh-vagov-ap-user`

