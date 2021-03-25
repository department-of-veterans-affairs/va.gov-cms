# CMS Environment Access

## Web & Shell access 

1. **Web Access** to the CMS environments via web browser is restricted to users with SOCKS Proxy or CAG access.
2. **Shell Access** is granted using the `sshuttle` tool. 

### SOCKS Proxy or CAG Access

To access any CMS or VA environment, you must connect with either CAG or SOCKS proxy.

### SOCKS Proxy Setup

  1. Add the following to `~/.ssh/config`:

            ### Access to SOCKS proxy from public internet, by way of dev jumpbox
            Host socks
              HostName 172.31.2.171
              ProxyCommand ssh -l dsva -A 52.222.32.121 -W %h:%p
              User socks
              
  2. Run the following command: 
  
            $  ssh socks -D 2001 -N

     Or, as a shortcut, if you have the CMS codebase and `composer` installed:
     
            $ cd /path/to/va.gov-cms
            $ composer va:proxy:socks
                 - or -
            $ composer v:p:s
     
     To test or debug the connection run:
          
            $  curl -v --proxy socks5h://127.0.0.1:2001 sentry.vetsgov-internal
                 - or - 
            $ composer va:proxy:test
                  - or -
            $ composer v:p:t

## CMS Production, Staging, Dev 

The three primary environments are hosted in the VAEC and managed by VFS's BRD System.

For more information on how to access these servers, see [DevOps Repo Docs]().


# TODO: Edit the following to reflect current onboarding procedures.

## "what do people need to do before getting access to things" policy:

Going forward the following will apply to anyone working on a VA.gov team
 (including anyone on the platform team):
1. To gain SOCKS access to our tooling (jenkins, grafana, sentry), SAC
 adjudication will need to be completed and returned as "favorable". this should take <=8 days from the time a person joins the team; and
2. To gain access to our AWS environments (console and/or programmatic), eQUIP
 adjudication will need to be initiated. this should take <=30 days from the time the person joins the team.
to check on the status of either milestone for non-gov't employees (so, any folks working on any contracts), please check with @Hayter. and of course let me know if you have any questions!

See https://dsva.slack.com/archives/C7S6EA0ES/p1568297100157500

@TODO: Fill out with content from the old CMS-devops repo.
