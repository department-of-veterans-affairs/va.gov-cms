# Facilities - Lovell

1. [Overview](#overview)
1. [Pages](#pages)
1. [Menus](#menus)
1. [Facilities](#facilities)
1. [List Pages](#list-pages)

## Overview

Lovell Federal health care is a unique situation were there are two different systems within an umbrella system.
   1. Lovell VA is for Veterans.
   2. Lovell TRICARE is for Dept of Defense.

The two different systems are run as separate website sections with some overlap.

To be clear, everything related to Lovell is tech debt because it does not follow any existing patterns.  Every effort has been made to minimize the amount and location of the tech debt, but there is plenty of it both CMS and Content-Build.


## Pages
### CMS
In the CMS, pages are either Clones or singles.
   - Clone: A CMS nodes that appears in both subsystems only differing by their path. A clone is any page with field_administration of "Lovell Federal health care" (tid: 347)
   - Single: A page that only appears in one or the other.  Any page with a field_administration of "Lovell - TRICARE" (tid: 1039) OR "Lovell - VA" (tid: 1040)
   - Twins: Separate CMS nodes that have a non-identical counterpart in both subsystems.  In the CMS a twin has no connection to its twin.  It has no knowledge that the twin exists.  So a twin is just a single, that has another similar single out there.  Twin is a human construct, not a CMS construct.

### Content-Build
There are a set of steps that run during the content build. The order for these may not be reflected correctly here.
   1. Deep copy any node with a field administration of a clone, to become the TRICARE clones.
   1. Put the new copies back in the pages data.
   1. Alter all paths of one set of clones to reflect Lovell - TRICARE.
   1. Alter all paths of the other set of the clones to reflect Lovell - VA.
   1. Alterations to titles of some pages to match VA or TRICARE.

## Menus

### CMS
The menu lovell_federal_health_care is being used as the central menu for both VA and TRICARE. This was done to reduce the maintenance of two separate menus and trying to match the weight and shared items between them.
An additional field 'field_menu_section' has been added to the lovell_federal_health_care menu.  On node save, the field is set programmatically to either 'both', 'VA', or 'TRICARE' based on what was set on the node for field_administration.
### Content-Build
The frontend approach to the menu involves the following:

1. Grab lovell_federal_health_care menu (includes field_menu_section)
2. Deep copy to make TRICARE menu.

3. Lovell VA menu process (original menu)
   - Lovell VA items come in with right path. No processing needed.
   - Remove TRICARE items.
   - Alter all field_menu_section = both to set Lovell VA path.

4. TRICARE menu process (the copy)
   - TRICARE items come in with right path. No processing needed.
   - Remove VA items.
   - Alter all field_menu_section = both to set TRICARE path.

## Facilities
Some Lovell facilities exist only in VA and some exist only in TRICARE.  Only VA facility statuses and health services are pushed to the Facility API when they are changed.  TRICARE facility data is not pushed, because those facilities do not exist in the Facility API.  They were hand created and have no connection to the Facility API.  Editing of data that normally comes from VAST will currently have to be performed by an admin, as the fields are locked down for everyone else.

## List Pages
List pages for events, news releases, stories and staff will need some unique handling that has not happened yet.


[Table of Contents](../README.md)
