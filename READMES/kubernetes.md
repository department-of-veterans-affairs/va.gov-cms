### Purpose

The purpose of this document is to describe and detail the infrastructure of the CMS as deployed through Kubernetes, whether locally or in production.  Migration from the Build-Release-Deploy architecture to Kubernetes is still in its early stages, so this document will be updated frequently to reflect the evolving architecture.

### Kubernetes
><b>[Kubernetes](https://kubernetes.io/)</b> (commonly stylized as <b>K8s</b>) is an open-source container-orchestration system for automating computer application deployment, scaling, and management. It was originally designed by Google and is now maintained by the Cloud Native Computing Foundation. It aims to provide a "platform for automating deployment, scaling, and operations of database management systems". [<sup>\*</sup>](https://en.wikipedia.org/wiki/Kubernetes)

Kubernetes is a rich and complicated subject and a developing technology that may be new to engineers on the project.  To assist in the learning process, we heartily encourage creating and maintaining your own Kubernetes cluster, and we've compiled some [notes](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/6545#issuecomment-936936548) on doing so.

### Hybrid Architecture
The consensus has generally settled around an application architecture using Amazon's [EKS](https://aws.amazon.com/eks/) for the core Drupal application but AWS offerings for some components, namely database (RDS), Memcached (ElastiCache) and images and other digital assets (EFS).  We determined that there was fairly little to gain from containerizing these services, scaling them would be complex, and the risk was substantial.

Depending on the details of how the content API is implemented, its service(s) may be managed through EKS or other AWS offerings.

### GitOps and Argo CD
The CMS application manifests are expected to be the ultimate source of truth with regard to desired application state.  We use GitOps, a "set of practices to manage infrastructure and application configurations using Git" [<sup>\*</sup>](https://www.redhat.com/en/topics/devops/what-is-gitops), to improve the transparency and auditability of the development and operations lifecycles.

The specific tool managing continuous deployment of the CMS is [Argo CD](http://argocd.vfs.va.gov/).  Argo CD is itself deployed within Kubernetes, and is responsible for observing the application manifests as stored on GitHub, through polling, and synchronizing the state of the cluster to the desired state as expressed in those manifests.  It also provides an interface for inspecting the health of the cluster and its components and the ability to roll back deployments in emergencies.

### Application Manifests
All VSP application manifests are stored in the [vsp-infra-application-manifests repository](https://github.com/department-of-veterans-affairs/vsp-infra-application-manifests), in the [apps/vsp-cms subfolder](https://github.com/department-of-veterans-affairs/vsp-infra-application-manifests/tree/main/apps/vsp-cms).  The [@cms-infrastructure team](https://github.com/orgs/department-of-veterans-affairs/teams/cms-infrastructure) are code owners for the manifests, and all pull requests must be approved by a member of that team.  Once the pull request is merged, Argo CD will deploy it automatically.

### Secrets and Application Configuration Parameters
During a deploy the CMS application needs to know where to find app config parameters as well as parameters or values that are sensitive and must remain secret. As part of the Application Manifest CMS will define an `externalSecrets` resource that is backed by AWS Systems Manager Parameter Store. All CMS related parameters and secrets should be added to Parameter Store and referenced like in [this example](https://github.com/department-of-veterans-affairs/vsp-infra-application-manifests/blob/main/apps/vsp-identity/test-user-dashboard/dev/externalsecrets.yaml).

### Release Process
The CMS release process is expected to follow this chain of events:

- on a regular basis, a GitHub Action will build a Docker container image, using the [Dockerfile](../Dockerfile) in this repository
- the image will be tagged with a monotonically increasing number, with the format `v0.0.${number}`
- the image will be pushed to the [ECR repository](https://console.amazonaws-us-gov.com/ecr/repositories/dsva/cms-drupal?region=us-gov-west-1)
- the application manifest will be updated to point to the new image tag
- Argo CD will poll the manifest repository for changes and, observing them, will redeploy the pod to use the new image

### FAQ

#### How do I trigger a deployment?
As Argo CD polls the manifest repository for changes, any operation that changes the manifest should trigger a corresponding deployment -- commits, revert commits, etc.  

In an **emergency**, it might be possible to change the manifest to point to an earlier commit.  **This is dangerous** because part of Drupal's application state actually exists in the database, in the form of database schemata, interdependencies between code and config and content, and so forth.  A simple reversion runs the risk of a broken deployment and substantial damage, including data loss.  This is a last resort.

A preferable emergency course-of-action might be to locally (or on an EC2 instance, for greater upload speed) build a replacement Docker image, upload it to the ECR repository, and update the manifest to refer to that tag.  This is less risky than reverting, although it may introduce further complications.

#### How do I manually build and push a Docker image?
Assuming a Linux or Mac system with Docker installed and functional, and with AWS credentials configured:

```bash
# The next release number.
# Or we might opt to append a suffix to the current 
# version number, e.g. v0.0.417-1, v0.0.417-2, etc.
CMS_DRUPAL_VERSION="v0.0.417"; 
CMS_IMAGE_NAME="dsva/cms-drupal:${CMS_DRUPAL_VERSION}";

# To push an image to ECR, we need to login.
# ECR uses standard Docker authentication, but a 
# special password that must be retrieved from ECR.
aws ecr get-login-password --region us-gov-west-1 | docker login --username AWS --password-stdin 008577686731.dkr.ecr.us-gov-west-1.amazonaws.com;

# Build the image.
docker build -t "${CMS_IMAGE_NAME}" . ;
# Apply the tag ECR and Argo CD expect to see.
docker tag "${CMS_IMAGE_NAME}" "008577686731.dkr.ecr.us-gov-west-1.amazonaws.com/${CMS_IMAGE_NAME}";
# Push the image. 
docker push "008577686731.dkr.ecr.us-gov-west-1.amazonaws.com/${CMS_IMAGE_NAME}";
```

This can be a lengthy process, especially on residential internet access, and it is highly recommended that this be done from within AWS' system.

[Table of Contents](../README.md)
