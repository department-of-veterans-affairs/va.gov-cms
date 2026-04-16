---
name: "(Sitewide) Campaign Landing Page request"
about: Request a new Campaign Landing Page, owned by Public Websites team
title: 'Campaign Landing Page request: <content info>'
labels: Needs refining, Public Websites, sitewide, User support, VA.gov frontend
assignees: FranECross, jilladams

---

## Description
Use this issue to request a new Campaign Landing Page on behalf of a requesting team. 


## Intake
- [ ] What triggered this runbook? (Help desk ticket, Product team, Slack thread, etc)
Trigger: <insert_trigger>

- [ ] Link to associated JIRA help desk ticket (if applicable)
Help desk ticket: <insert_help_desk_link>

- [ ] Vanity URL requested:
<insert_vanity_URL_request>	

- [ ] Name of submitter (if applicable)
Submitter: <insert_name>

- Campaign title: 
- Who the editor(s) will be for the Campaign Landing Page and any appropriate stakeholders for awareness: 
- The goals/outcomes you are looking to achieve with the campaign: 
- Outcome success measurement & how it will be measured (note: "Page views" is not a generally accepted success measurement): 
- Target Audience(s): 
-  Campaign start/end dates: 
- Is this a seasonal campaign? 
- If not: when campaign ends, should campaign page be archived or redirected? 
- If redirected, where should it redirect?

## Acceptance criteria
  
- Request comes in, via helpdesk request or direct to PO, or potentially in a Github issue.
- [ ] If not in a helpdesk request, redirect to Helpdesk or create a Helpdesk issue using email support@va-gov.atlassian.net or URL https://prod.cms.va.gov/help
Helpdesk will use #sitewide-public-websites channel to request approvals
- [ ] In helpdesk ticket, @ mention Michelle Middaugh to review/approve the creation / existence / description of the CLP. Example text: 
> Hi Michelle, Helpdesk has received a request for a new Campaign Landing Page. Can you confirm that this CLP is approved?

### **If not approved**
- [ ] Close the Helpdesk ticket and the Github issue.

### **If approved**
#### Helpdesk steps
- [ ] Helpdesk or any Drupal Admin can create the CLP node. Clone the [CLP template node](https://prod.cms.va.gov/node/16512) 
<insert_CLP_node_link>
- [ ] If a new CMS editor user is needed, Helpdesk or any Drupal admin can create them and assign proper permissions according to [CLP CMS account administration policy](https://prod.cms.va.gov/help/cms-account-admin-policies/clp-cms-account-administration-policy).
- [ ] Send the editor a link to the KB article: [self-guided Campaign Landing Page training](https://prod.cms.va.gov/help/campaign-landing-pages/how-to-manage-campaign-landing-pages)
- [ ] Create a post in DSVA slack channel #sitewide-public-websites, and @ mention Jill Adams and Fran Cross: "New CLP requested and approved, for your awareness:" with a link to this Github issue for next steps.


#### Public Websites steps
- [ ] Create a [Redirect, URL change, or vanity URL request](https://github.com/department-of-veterans-affairs/va.gov-team/issues/new/choose), to request the Vanity URL IA _must_ review & approve URL requests.

<insert_redirect_issue_link>

- [ ] @mention Mikki Northuis in IA (#sitewide-content-ia) with link to Github issue, to provide Vanity URL request feedback. IA _must_ review & approve URL requests.

- [ ] When approved by IA, execute the steps of the URL change request ticket from step 2 above.

- [ ] When redirect is verified in Production, close Redirect ticket as well as this ticket.
