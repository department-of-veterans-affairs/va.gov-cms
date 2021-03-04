# DevOps documentation for Tugboat
The CI tool used for Pull Requests and DEMO environments is called [Tugboat](https://www.tugboat.qa).

Also see [/READMES/tugboat.md](/READMES/tugboat.md) for Tugboat user documentation.

## Local Setup
1. Pull down the private key from credstash and save to ~/.ssh/tugboat.key:
 `credstash -r us-gov-west-1 get cms.github.tugboat.ssh_deploy_key.private > ~/.ssh/tugboat.key`

1. Add below to ~/.ssh/config to use IdentityFile tugboat.key for host tugboat.github.com:
   ```
   Host tugboat.github.com
      HostName github.com
      User git
      Identityfile /home/elijah/.ssh/tugboat.key
      IdentitiesOnly yes
   ```

1. Clone repo:
  `git clone git@tugboat.github.com:Lullabot/tugboat.git`


## Common Tugboat Operations
Can only update CPU and memory at a project level, not repository level.

### How to update CPU limit?
1. `tugboat ls projects` # Get project ID
1. `tugboat ls 5fd3b8ee7b465711575722d5 -j | grep cpus` # Get current limit
1. `tugboat update 5fd3b8ee7b465711575722d5 cpus=24` # Set limit to 24 CPU
1. `tugboat ls 5fd3b8ee7b465711575722d5 -j | grep memory` # Verify new limit

### How to update memory limit?
1. `tugboat ls projects` # Get project ID
1. `tugboat ls 5fd3b8ee7b465711575722d5 -j | grep memory` # Get current limit
1. `tugboat update 5fd3b8ee7b465711575722d5 memory=16384` # Set limit to 16GB
1. `tugboat ls 5fd3b8ee7b465711575722d5 -j | grep memory` # Verify new limit
