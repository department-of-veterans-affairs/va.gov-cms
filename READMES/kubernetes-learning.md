# Kubernetes Learning

## Purpose

The purpose of this document is to summarize our thoughts and feelings about how an engineer new to Kubernetes might go about gaining some practical, useful experience with it, based on our recent experiences doing just that.  This document is tailored to engineers and assumes some experience with DevOps, Linux system administration, or similar subjects.

Kubernetes is a container orchestration platform based on clustering, and its design decisions have followed from that premise.  As a result, it is critical to have a working understanding of networking (ideally including a casual familiarity with `iptables` and the Linux kernel packet filter).  But that's only a starting point: Kubernetes introduces a number of terms and concepts and behaviors that might be new even to engineers who have substantial experience with virtualization and containerization.

I believe that the best way to grasp any sort of technology is to use it yourself; I think that's why we use "grasp" as a synonym for "understand", especially with regards to technology.  So I think it's essential to dive right in to Kubernetes by deploying a cluster, suffering through the inevitable complications and setbacks encountered along the way, and maintaining and improving it as you learn.  This can be done a number of ways.

EKS is the foundation of our infrastructure with regard to Kubernetes, but I feel this is a suboptimal choice for learning.  EKS was developed with an eye to the pain points of Kubernetes: managing ingress/load balancing, deployment, upgrades and other maintenance to the cluster itself, and so forth.  It's tightly integrated with AWS' innumerable other offerings, making it comparatively easy to add storage, to log in a persistent fashion, to manage TLS certificates, and so forth.

EKS is a solid option, but the fact that it eliminates or eases some of these struggles ultimately presents some barriers to understanding.  It also costs money; not much, comparatively speaking, and the resources can of course be created and destroyed on demand as one would any other AWS resource.  But some challenges with Kubernetes present themselves only over time, and this expense will accumulate.

I think it is preferable to avoid the cloud and deploy and maintain a cluster locally -- either virtualized or on bare metal.  Some distributions of Kubernetes can run and do useful work on two or three Raspberry Pis, or two or three virtual machines running in VirtualBox, or even within a couple of Docker containers.  I think it's preferable to avoid options that don't provide a 1:1 match for functionality with a standard Kubernetes distribution, but all options provide significant and irreplaceable value.

In our cases, we took two different approaches:

- I (Nathan Douglas) maintain a homelab built around three servers running Proxmox VE, each in a distinct subnet, each standalone (as opposed to operating in a Proxmox cluster).  I created four clusters, each with a node on each of the servers, one as the control plane and two as workers.  The customary approach is to use VMs, but for various reasons I opted to use LXC containers... which posed some additional challenges.  Two of the servers are limited to approximately one terabyte of storage, which they split between about twenty containers each; the third has about forty terabytes in a ZFS pool.  Thus all nodes have access to a limited quantity of fast local storage and a substantial quantity of comparatively slow NFS storage, and some nodes have access to a substantial quantity of fairly fast local storage, all in dedicated per-host and per-cluster datasets (I might shift local volumes to zvols though).  This structure reflects my interest/obsession/terror concerning storage, networking, resilience, backups, and other gritty details of cluster administration.

- I (Eric Oliver) run MS Windows on my workstation. This presented an opportunity to run [Windows Subsystem for Linux](https://docs.microsoft.com/en-us/windows/wsl/) and install [MicroK8s](https://microk8s.io/), a light-weight simple Kubernetes (k8s) distribution. Using WSL and MicroK8s appeared to be the quickest path to get K8s up and running with minimal configuration and deployment steps.  The end goal was to quickly bring up a K8s environment and quickly install [ArgoCD](https://argoproj.github.io/cd) to locally replicate VSP-Operations team's Production environment.  This would provide a safe space to explore and break K8s and ArgoCD, as well as explore deployment patterns and creating K8s hosted applications.  In the end this worked up until pods needed to communicate with each other. Unfortunately, I haven't found out why all other network communication works except for inter-pod communication. I'm not sure if it's a combination for WSL and MIcroK8s causing this issue but I would like to explore other options for locally running K8s.

We documented our experiences with getting a cluster running [in this GitHub issue](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/6355).

The factors in your decision-making may (and likely will) differ from ours and lead to different choices and different avenues of investigation.  This is a good thing.  Kubernetes is large and complex, built on many different technologies at different levels, and developing rapidly.  However, its sheer breadth should provide toeholds while you learn.

We've found these resources to be useful and worth checking out:

### Videos:
- [Life of a Packet](https://www.youtube.com/watch?v=0Omvgd7Hg1I) - A description of a packet's journey in several different networking scenarios, focusing on how Kubernetes uses `iptables`.
- [Kubernetes 101](https://www.youtube.com/watch?v=IcslsH7OoYo&list=PL2_OBreMn7FoYmfx27iSwocotjiikS5BD) - A gentle but very productive introduction to Kubernetes and how it can be used with not just 12-factor apps but... Drupal.  Covers concepts like logging, scaling (horizontal and vertical), storage, DNS, TLS, and many others.
- [Kubernetes on Windows with WSL 2 and Microk8s](https://www.youtube.com/watch?v=DmfuJzX6vJQ&t=2s)
- [Full Tutorial GitOps & ArgoCD | Day 34 of \#100DaysOfKubernetes](https://www.youtube.com/watch?v=c4v7wGqKcEY&list=PLWnens-FYbIpUpmiiNYfkqTZQUYppGMFV&index=35)

### Core Tools:
- [Argo CD](https://argoproj.github.io/) - A GitOps (meaning that a Git repository is the source of truth) tool for continuous deployment
    - [Getting Started](https://argoproj.github.io/argo-cd/getting_started/)
- [Helm](https://github.com/helm/helm) - A Kubernetes package manager.  Probably the simplest to use, although some consider it an anti-pattern.  Built around Go templating the YAML manifest files.

### Extra Tools:
- [k9s](https://github.com/derailed/k9s) - Awesome terminal UI.
- [kubebox](https://github.com/astefanutti/kubebox) - Another terminal UI.
- [kube-shell](https://github.com/cloudnativelabs/kube-shell) - Basically code completion, but for kubectl.
- [Lens](https://k8slens.dev/) - A Kubernetes IDE.

### Kubernetes, the Hard Way:
- [The Original](https://github.com/kelseyhightower/kubernetes-the-hard-way) -- The original, somewhat tailored to Google Kubernetes Engine.
- [Proxmox](https://github.com/Wirebrass/kubernetes-the-hard-way-on-proxmox) -- If you use [Proxmox](https://proxmox.com/en/) as a virtualization platform.
- [VirtualBox](https://github.com/sgargel/kubernetes-the-hard-way-virtualbox) -- Instructions can be tweaked for use with desktop VMWare, etc.
- [ESXi](https://github.com/defo89/kubernetes-the-hard-way-lab) and [notes on vSphere](https://www.domstamand.com/installing-a-kubernetes-cluster-on-vmware-vsphere-and-what-ive-learned/)

### Kubernetes Distributions:
- [k3s](https://k3s.io/) -- Lightweight Kubernetes distribution by Rancher.  Runs on small systems, including Raspberry Pis, but isn't a 1:1 match for a standard Kubernetes distribution.
- [microk8s](https://microk8s.io/) -- Lightweight Kubernetes distribution by Canonical.  Focuses on simplicity of installation over minimized resource usage.  Includes Traefik as an Ingress Controller out of the box.
- [minikube](https://minikube.sigs.k8s.io/docs/) -- A full-blown Kubernetes cluster that will run within a VM on your PC.  Very well supported and documented, and even has `kubectl` built-in and automatically configured.

### Just For Fun:
- [kubedoom](https://github.com/storax/kubedoom) -- A fun implementation of chaos engineering; test the resilience of your distributed system by killing demons (and thereby individual pods).

### Links/References:
- [Jeff Geerling's Drupal on Kubernetes](https://www.jeffgeerling.com/blog/2019/running-drupal-kubernetes-docker-production)
- [Kubernetes on Windows with MicroK8s and WSL 2](https://ubuntu.com/blog/kubernetes-on-windows-with-microk8s-and-wsl-2)
- [WSL2+Microk8s: the power of multinodes](https://wsl.dev/wsl2-microk8s/) - Installation guide for MicroK8s on WSL 2