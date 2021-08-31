## Tugboat On-Premise Install Documentation

The internal/private DNS of the Tugboat install is https://tugboat.vfs.va.gov. 

### Database Backups and Restoration
**Backups** are performed automatically to the /opt/tugboat/data/backups folder nightly at 00:00 UTC. There are three databases, all MongoDB .json and .bson. Then a [Jenkins job](http://jenkins.vfs.va.gov/job/utility/job/tugboat-backup/) goes into the Tugboat server once per day and uploads them to an the dsva-vetsgov-utility-tugboat S3 bucket.

TODO: We think that the nightly backup already runs an /opt/tugboat/util/verify-backup.sh and think that it would be good to run this again in our Jenkins job before upload to S3.

These backups do not contain Docker data and will not restore the ephemeral review instances, but will restore all of the Tugboat UI config and settings plus all the environment metadata e.g. review instance names and branch pointers etc.

**Restoration** hasn't been tested yet but below are hypothetical next steps based on verify-backup.sh and https://stackoverflow.com/a/42553681/292408. Proceed with caution and update these steps.
1. If correct backup is not already in e.g. /opt/tugboat/data/backups/202105180000 then download .tar backup from dsva-vetsgov-utility-tugboat/backups S3 bucket to e.g. /opt/tugboat/data/backups/202105180000
1. rm -rf /opt/tugboat/data/backups/ # needed because backup needs to have latest date for verify-backup.sh script to work correctly
1. untar to e.g. /opt/tugboat/data/backups/202105180000
1. Run verify-backup.sh, `echo $?` to verify return status is 0 (success)
1. `docker cp /tmp/tugboat-backup-restoration MONGO_CONTAINER_IDENTIFIER:/backup` # replace MONGO_CONTAINER_IDENTIFIER (`docker ps`)
1. `docker exec -t MONGO_CONTAINER_IDENTIFIER mongorestore /backup` # replace MONGO_CONTAINER_IDENTIFIER
1. Test with `docker exec -t 398c0186a531 mongo tugboat --quiet --eval 'db.upgrades.count()'` to see if some expected data is present

### Deployment
Manual for now. @SEE "Future" section
1. Check for patches that need to be re-applied in va.gov-cms/.tugboat/patches/PATCHES.txt. Also can use `git status` and `git diff`, can possibly use `git stash`.
1. (optional) `curl --location https://raw.githubusercontent.com/department-of-veterans-affairs/va.gov-cms/fb9e9c8f3746395710482150c53a973ad8ca0144/.tugboat/patches/race-condition.patch --remote-name`
1. cd /opt/tugboat
1. git fetch --tags
1. git checkout $TAG
1. (optional) `git stash pop`
1. make && tbctl reload

### Docker Volume increase (instructions for 4TB, adjust accordingly)
1. Resize block device in AWS Console UI (very fast, because at the hypervisor level)
1. Updated /etc/docker/daemon.json, `dm.basesize=4096G"`
1. `pvresize /dev/sdg`
1. `lvextend -l+100%FREE -n docker/thinpool`
1. Suspend all previews so they don't come back in a FAILED state:
    1. `PREVIEWS=$(tugboat list previews -j | grep '"preview":' | awk '{print $2}' | tr -d ',' | tr -d '"')`
    1. `for PREVIEW in $PREVIEWS; do (tugboat suspend $PREVIEW &) ; done`
1. Wait a few minutes (this step can create a timeout error in Tugboat UI for a short time period)
     ![image](https://user-images.githubusercontent.com/1504756/107723852-37fb2700-6c97-11eb-8f63-cee7fc1f1e43.png)
1. `reboot`
1. `tbctl start` (shouldn't have to do this after a reboot but unfortunately do)
1. `tugboat update 5fd3b8ee7b465711575722d5 quota=4096`

### Patches
We currently have a few modifications to Tugboat source code that we need to make sure are either reapplied, or evaluated on each deployment for removal.

### Future
We had put Tugboat into the BRD system (PR [#8567](https://github.com/department-of-veterans-affairs/devops/pull/8567)) and were going to deploy it but have paused on that as the new ArgoCD + Kubernetes infrastructure is ready now, so we are going to port Tugboat into that system instead since Jenkins will be deprecated by Q4 2021. The possible difficulty in doing that will be that when scaling Tugboat horizontally Tugboat expects a Docker Swarm and thus will need to be adapted to K8s somehow.
