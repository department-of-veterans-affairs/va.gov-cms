# CMS Environment Access

## Web access

**Web Access** to the CMS environments via web browser is restricted to
users with SOCKS Proxy or CAG access.

### SOCKS Proxy or CAG Access

To access any CMS or VA environment, you must connect with one of the following:

- **Citrix Access Gateway**
- **SOCKS proxy**
- **VFS Toolkit** (also referred to as VTK or `vtk`)

If you're an engineer, you probably need the SOCKS proxy or VTK.

### VFS Toolkit Setup

See [here](https://github.com/department-of-veterans-affairs/vtk#socks) for instructions.
### SOCKS Proxy Setup

Add the following to `~/.ssh/config`:

```ssh-config
Host socks
     HostName 172.31.2.171
     ProxyCommand ssh -l dsva -A 52.222.32.121 -W %h:%p
     User socks
```

Run the following command:
  
```bash
$ ssh socks -D 2001 -N &
[1] 53114
```

Or, as a shortcut, if you have the CMS codebase and `composer` installed:

```bash
$ cd /path/to/va.gov-cms
$ composer va:proxy:socks
[1] 53114
```

To test or debug the connection run:

```bash
$ curl -v --proxy socks5h://127.0.0.1:2001 sentry.vetsgov-internal
- or - 
$ composer va:proxy:socks:test
```

## SSH access to CMS Production, Staging

The three primary environments are hosted in the VAEC and managed by VFS's BRD
System.  Access is controlled and limited to DevOps personnel.

For more information on how to access these servers via SSH, see [BRD Login docs](./brd-login.md).

## "what do people need to do before getting access to things" policy

Going forward the following will apply to anyone working on a VA.gov team
(including anyone on the platform team):

1. To gain SOCKS access to our tooling (jenkins, grafana, sentry), SAC
adjudication will need to be completed and returned as "favorable". This should
take <=8 days from the time a person joins the team.

2. To gain access to our AWS environments (console and/or programmatic), eQIP
adjudication will need to be initiated. this should take <=30 days from the
time the person joins the team.

----

[Table of Contents](../README.md)
