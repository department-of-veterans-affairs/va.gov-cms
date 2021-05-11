# VA Gov Dashboards

1. [Sections](#sections)
2. Dashboards
   1. [Vet Center](#vet-center)
3. [Caching](#caching)


## Sections

The sections taxonomy is used to provide the section specific dashboards.  The product field setting on any section term is used to determine the dashboard Layout Builder view mode that gets used for that section and any of its children that do not specify their own product.

## Dashboards

### Vet Center
  The Vet Center dashboard provides its own blocks through VcDashboardsBlock, and its derivatives to generate the blocks.  Node specific lookups are provided by the VetCenterDashboard service.

## Caching
To avoid excessive queries for items related to the dashboard, we are caching items on two levels.
  * Section product - The associated product for a section is cached and invalidated upon save of the section term.
  * Related blocks - Block data is cached at the block layer and is invalidated upon save of the section term. The data is gathered by a Dashboard service so it is only gathered once and used across all the dashboard blocks.


[Table of Contents](/README.md)
