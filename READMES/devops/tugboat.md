# The CI tool used for Pull Requests and DEMO environments is called [Tugboat](https://www.tugboat.qa). 

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
  
1. See ./tugboat/README.md from here on. 
