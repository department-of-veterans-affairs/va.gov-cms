# Interfaces

1. [GraphQL](graph_ql.md)
1. [FeatureFlags](#featureflags)
1. [FacilitiesAPI](#facilities-api)


## GraphQL

See [GraphQL](graph_ql.md)

## FeatureFlags
Feature Flags are set in Drupal as configuration and either turned off or on to enable the use of new features on the Front End.  They are controlled in the DB via
`/admin/config/system/feature_toggle` and exported to config.
Drupal is the source of truth about the status of a Feature Flag and any use of them on the Front End should provide functionality for if the Flag is present and TRUE as well as if it is not present or FALSE.  A non-present flag should be treated as FALSE.
After a Feature Flag and its matching Front End code have made it all the way to PROD, PR's should be created to first remove Feature Flag logic from the Front End and then Removed from the CMS after that.  We should strive for as few flags as possible in the system.  They are intended only to sync up deployments of Features, they are not intended to be used beyond both FE and CMS code making it to PROD.

To see what is set on a give environment use
`curl --proxy socks5h://127.0.0.1:2001 "http://prod.cms.va.gov/flags_list" | python -m json.tool`   This requires SOCKS Proxy to be activated.

## Facilities API

[Ongoing facility migrations](../migrations-facility.md)

The Facilities API provides data about VA facilities (hospitals and treatment
centers).
[Facilities API Documentation](https://developer.va.gov/explore/facilities/docs/facilities)

### Facility API Locally
To adjust your local build to use and display Facility API data:
In this file
`src/site/constants/environments-configs.js`
The setting `API_URL` should be changed to `https://dev-api.va.gov`

### How to call the API and parse the data:

The complete API call and response is formatted JSON. Calls can be GET requests
and common calls look like:

* Find locations near me, from the current va.gov website: https://www.va.gov/find-locations/?zoomLevel=4&page=1&address=5624 Kipling Parkway%2C Arvada%2C Colorado 80002%2C United States&facilityType=all&serviceType&location=39.798459%2C-105.11022&context=80002

  NOTE: type can also be "health", "cemetery", "benefits", "vet_center", serviceType can be things like “MentalHealthCare”, “PrimaryCare”, “Dermatology” (see facility-locator-response-json.txt for mock examples with more serviceTypes)

  The API call the above find locations react widget is making: https://api.va.gov/v0/facilities/va?address=5624 Kipling Parkway, Arvada, Colorado 80002, United States&bbox[]=-105.86022&bbox[]=39.048459&bbox[]=-104.36022&bbox[]=40.548459&type=all&page=1

  NOTE: bbox[] array is bounding coordinates of the map to return based on location coords and zoomLevel from map display

* Load a facility detail page from the current va.gov website : https://www.va.gov/find-locations/facility/vha_501G2
  NOTE: Id param at the end correlates to ‘id’ returned in the response

* The API call to get a particular facility’s detail information: https://api.va.gov/v0/facilities/va/vba_339
  NOTE: Here i used vba_339 which is a different facility than vha_401G2 above

### Data available from the facility locator api:

* Example of variables that can be returned in a response. they are mostly straightforward. The ‘access’ element contains response times. A good source for the variables returned can be found in the vets-website repo in the facility locator’s MockLocatorApi.js at https://github.com/department-of-veterans-affairs/vets-website/blob/82c4c0c6968958efae5b62c1f1b67e8ab4041f3a/src/applications/facility-locator/api/MockLocatorApi.js

### How data is mapped to metalsmith templates:

* Since this is a React widget displaying dynamic data it’s not using Metalsmith templates. The .jsx component files to support rendering are located in https://github.com/department-of-veterans-affairs/vets-website/tree/master/src/applications/facility-locator/components



[Table of Contents](../README.md)
