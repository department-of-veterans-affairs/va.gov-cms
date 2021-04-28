# Elasticache for VA.GOV-CMS

## Purpose:
Provide an overview and detailed technical information regarding the implementation of AWS Elasticache using the Memcache engine.
## Motivation:
GraphQL queries increase load on Drupal backed VA.GOV-CMS database. By implementing a layer of the most frequently accessed data those queries will remove be much faster and shift load away from the CMS database. In-memory caching is an effective way to solve this problem
## Research Findings:

Team tested build times<sup>[[4]](https://github.com/department-of-veterans-affairs/va.gov-cms/issues/4461#issuecomment-806146870)</sup> and resource consumption of a PoC for Elasticache Memcache and found that a full build request was faster and consumed fewer resources<sup>[[5]](http://grafana.vfs.va.gov/d/dxf8a-6Zz/cms-dashboard?orgId=1&from=1616614756221&to=1616615896576&var-datasource=Prometheus%20(Utility)&var-environment=dev&var-app_name=cms-test)</sup> on the database server and only slightly more CPU on the app server. It is suspected that it is so much faster on a cold cache because the cache writes are faster with Memcache than RDS. 
Cold cache (over 50% decrease)

- GraphQL Request time
  - wo/Memcache: 1m06s
  - w/Memcache: 0m32s	
- RDS Load
  - wo/Memcache: 33% max
  - w/Memcache: 21%
- RDS Write IOPS (read was negligible)
  - wo/Memcache: 657/second
  - w/Memcache: 13/second
- EC2 Load
  - wo/Memcache: 24.0% max
  - w/Memcache: 47.1% max
- Warm cache
  - GraphQL Request time
    - wo/Memcache: 5.365s
    - w/Memcache: 4.788s
  - RDS Load - N/A
  - EC2 Load - N/A

The 50% decrease in response times for a vets-website GraphQL Build with our current node count of 5K+ justifies the replacement of AWS Relational Database Service (RDS) with AWS Elasticache Memcache for the Drupal Cache Layer. The decrease in response time also justifies the increased complexity of adding Memcache to our entire stack, including CI and Local environments.

- Redis
  - Pros
    - Advanced Data Structure
    - Clustering
    - “Fastest”
    - Sharding
  - Cons
    - Single Threading
    - Blocking
- Memcache
  - Pros:
    - Simple
    - Multi Thread out of the box
    - Fast
  - Cons:
    - Simple data structure
    - Old hotness?

## Implementation Details:

AWS Elasticache using Memcache is implemented in Terraform.<sup>[[2]](https://github.com/department-of-veterans-affairs/terraform-aws-vsp-cms/pull/21)</sup>

| Attribute | Value |
| --- | ----------- |
| Engine | Memcache |
| Node Type | cache.r5.large - 2 vCPU - 13GiB Memory |
| Number of Nodes | 2 |
| Engine Version | 1.6.6 |
|  Maintenance Window | Every Monday 22:00-23:00 UTC |

AWS Elasticache for Memcache is configured for High-Availability by running multiple (2) nodes across multiple AWS Availability Zones. This will mitigate the failure of hardware or network connectivity with a particular AZ.

Terraform is configured to ignore changes to the Node Type, Number of Nodes and Engine Version as changes to these attributes would show a difference between the configured value and the new value until the maintenance window applies them. The other possible solution to this is to use the `apply_immediately` attribute which may be fine for no production environments but may introduce problems for live/production environments.

Any changes to the above attributes will be applied during the predefined Maintenance Window. Monday 2200-2300 UTC was chosen so that it coincides with COB Eastern time and 1500 Pacific time. This is meant to minimize disruptions to users while also occurring during business hours when DevOps support staff are available.

## Troubleshooting and How-to
### How do I disable Memcache locally?

Debugging docs
Elasticache/cluster debugging
CI/Lando will be same-node debugging

## Monitoring

[Memcache health and performance metrics](http://grafana.vfs.va.gov/d/dxf8a-6Zz/cms-dashboard?orgId=1&refresh=5s) are graphed in Grafana.  This Grafrana implemenation is only available within the VA, accessible by the socks proxy. 
Memcache metrics are at the bottom of this page.


## References
1. https://github.com/department-of-veterans-affairs/va.gov-cms/issues/4458
1. https://github.com/department-of-veterans-affairs/terraform-aws-vsp-cms/pull/21
1. https://github.com/department-of-veterans-affairs/devops/pull/8895

1. https://github.com/department-of-veterans-affairs/va.gov-cms/issues/4461#issuecomment-806146870
1. http://grafana.vfs.va.gov/d/dxf8a-6Zz/cms-dashboard?orgId=1&from=1616614756221&to=1616615896576&var-datasource=Prometheus%20(Utility)&var-environment=dev&var-app_name=cms-test
