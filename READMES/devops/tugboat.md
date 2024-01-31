# DevOps documentation for Tugboat
The CI tool used for Pull Requests and DEMO environments is called [Tugboat](https://www.tugboat.qa).

Also see [/READMES/tugboat.md](/READMES/tugboat.md) for Tugboat user documentation.

## Local Setup
1. Pull down the private key from SSM Parameter Store at `/cms/tugboat/ssh_deploy_key.private` and save to `~/.ssh/tugboat.key`:

1. Add below to ~/.ssh/config to use IdentityFile tugboat.key for host tugboat.github.com:
   ```
   Host tugboat.github.com
      HostName github.com
      User git
      Identityfile /home/<your username>/.ssh/tugboat.key
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

## Tugboat Crisis Intervention

### Overload

**Symptoms**: Tugboat is slow, requests to Tugboat dashboard return 502/504 status codes, previews disappear and reappear, etc.

**Diagnosis**: Tugboat might be overloaded; too many previews might be running simultaneously.

**Verification**: 

1. Log into the Tugboat server (`ssm-session utility tugboat auto`).
2. Check system load and free memory (e.g. `top`).
3. If load is incredibly high, and available memory is very low, then the Tugboat server might be dealing with too many open previews.

**Remediation**:

1. Close unused previews in the CMS/Pull Requests project. Target older previews and those corresponding to closed/merged PRs; these should be closed automatically, but there may be issues somewhere in the system that impair communication and cause these to remain open.
2. Suspend older previews. This normally happens automatically (for Pull Request-based previews that haven't been touched in some period of time), but a flurry of previews might have been created inadvertently.
3. Consider upscaling the Tugboat server or migrating to an alternative architecture.
