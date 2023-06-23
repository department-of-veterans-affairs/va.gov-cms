# Out of band deploy process

1. Alert Site-wide, helpdesk, and editors of an OOB deploy and downtime
2. Browse to jenkins.vfs.va.gov > Deploys > cms release tag and auto deploy for prod.cms.va.gov
3. On the left action banner, select Build with Parameters
4. Change release_wait  to 15
5. Click Build
6. Wait until complete.
