# Interfaces

1. [GraphQL](#graphql)
1. [FeatureFlags](#featureflags)
1. [FacilitiesAPI](#facilities-api)


## GraphQL

@TODO Descrption and Details needed.
The GraphQL  Explorer is for testing Queries and their responses. `/graphql/explorer`

## FeatureFlags
Feature Flags are set in Drupal and either turned off or on yo enable the use of new features on the Front End.  They are controlled in the DB via
`/admin/config/system/feature_toggle`
A flag must be defined in Drupal before it is referenced on the FE.

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
