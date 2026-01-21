---
layout: default
title: CMS Environment Access
---

# CMS Environment Access

## Web access

**Web Access** to the CMS environments via web browser is restricted to
users with VA Network access.

### VA Network Access

To access any CMS or VA environment, you must connect to the VA Network using one of the following methods:

- **PIV card with Azure Virtual Desktop (AVD)** - Recommended for most users
- **PIV card with Citrix Access Gateway (CAG)** - Alternative access method
- **VA Network (on VA premises or via VPN)** - For users with direct network access

If you're an engineer or need immediate access to internal tools, you should work with your team to obtain VA Network access as soon as possible.

## SSH access to CMS Production, Staging

The three primary environments are hosted in the VAEC and managed by VFS's BRD
System. Access is controlled and limited to DevOps personnel.

For more information on how to access these servers via SSH, see [BRD Login docs](./brd-login.md).

## Access requirements policy

Going forward the following will apply to anyone working on a VA.gov team
(including anyone on the platform team):

1. To gain VA Network access to our tooling (jenkins, grafana, sentry), SAC
adjudication will need to be completed and returned as "favorable". This should
take <=8 days from the time a person joins the team.

2. To gain access to our AWS environments (console and/or programmatic), eQIP
adjudication will need to be initiated. This should take <=30 days from the
time the person joins the team.

----

[Table of Contents](../README.md)