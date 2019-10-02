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
@TODO Descrption and Details needed.
[Facilities API Documentation](https://developer.va.gov/explore/facilities/docs/facilities)

### Facility API Locally
To adjust your local build to use and display Facility API data:
In this file
`src/site/constants/environments-configs.js`
The setting `API_URL` should be changed to `https://dev-api.va.gov`



[Table of Contents](../README.md)
