# SOP:  Tier 1 - Ticket Response and Communications Procedures

## Overview

Tier 1 is required to meet all SLA requirements and  resolve a users issue while providing an empathetic and efficient path to resolution.   This document will provide Tier 1 help desk support persons with answers to common issues.  It will also provide suggestions what First Response and Replies to requests for help can look like.

# Types of Incoming Requests

## How-to

## Defect or bug



## Feature Request or Enhancement 

# Communications with CMS Users

#### 

### **How do I login to the Production, Tugboat, or Training servers?**

| Site       | URL                                                          |
| ---------- | ------------------------------------------------------------ |
| Production | https://prod.cms.va.gov                                      |
| Training   | https://cms-training-pion6wlxie1t0mckcgwjl4kid1k1haqe.demo.cms.va.gov |
| Tugboat    | https://tugboat.vfs.va.gov                                   |

### **What are the credentials needed to login to the Production, Tugboat or Training servers?** 

| Site       | Username          | Password      |
| ---------- | ----------------- | ------------- |
| Production | your PIV card     | your PIV card |
| Training   | your VA.gov email | Drupal8       |
| Tugboat    | your VA.gov eamil | Drupal8       |

### **How do I request a CMS user account?**

Send an email to vacmssupport@va.gov, cc'ing your VA Product Owner and including your:

- VA Email Address associated with your PIV card.
- Requested Role (editor, reviewer, publisher).
- Section(s) of the site to which you require access (ie Benefit Hub, VHA - specify which VAMC(s), NCA etc).

An administrator will verify the request is approved with the PO via email .

Once approved, the user administrator will create your account and assign the requested role(s) and you will receive an email with instructions on how to access your account.

You can always find these instructions here: https://prod.cms.va.gov/help/support/request-a-cms-account 

### **Basic Opener**

Hi {name},

Thanks for contacting the VA.gov Drupal CMS Help Desk!  It sounds like you’re {restate the issue by paraphrasing or parroting back the issue}.   

Happy to help!

[*Optional:  Ask probing questions but keep in mind that these questions should be as specific as possible, and users don’t typically answer more than one question at a time, so it can be helpful to choose questions carefully.  Think of your words as having an actual cost per word, for example 1 word = $1; and your budget is limited.]*



[*Optional: an indication of what steps will happen next (e.g. asking teammates for assistance triaging the request). Never promise a specific delivery date or time for issue resolution!]*

Thank you for your patience as I will need to

​	meet with engineers on this one to discuss further.

​	review your account to understand more about this.

​	research this a bit more to understand what steps are best.

or something similar.

Let us know if you have other questions! *[OR]* Happy to help once we know a bit more.

Thanks,

{your name}

VA.gov Drupal CMS Help Desk



**How do you update Mental Health Phone Numbers?**

The CMS gets this information from the Lighthouse API which, in turn, gets its data from VAST _and_ other sources. I checked with the Lighthouse Team and their contact for updating Mental Health numbers is @Elena.Cherkasova@va.gov who I’ve included on this thread.

She should be able to update these numbers in the current lighthouse system so that it propagates to all relevant applications including your website.



### Followup to No-reply



Hello {Firstname}, 

Just checking in - have you been able to {perform task}? We hope so! 

If not, and you're still having trouble, please let us know and we'll be happy to help. 

Thanks, 

{Staff Name} 

VA.gov Drupal CMS Help Desk



### Ticket - No reply

We haven’t heard back from you recently, so we'll assume that you no longer need help with the issue you were having. In turn, we'll be closing this help desk ticket and won’t be emailing you any further follow-up messages.

If you still need assistance, please let us know by responding to this email. Thanks!

Thanks,

{Staff Name}

VA.gov Drupal CMS Help Desk



**Broken Links - Content Block - Link to File or Video**

Hello name,

We’ve detected a broken link in content you’ve edited recently. **Please repair the link as soon as possible** to avoid further errors publishing your content live on VA.gov. 

**What went wrong:** It looks like you tried to enter a download link to a file or video using the rich text content block on the following content:https://prod.cms.va.gov/hudson-valley-health-care/work-with-us/internships-and-fellowships. The link broke because the system requires you to “Link to a file or video” content block for download links to files such as pdfs, or to Youtube videos. Structuring your download or video links this waygm makes it easier for Veterans to detect and access that content.

**How to fix it:** Go to https://prod.cms.va.gov/hudson-valley-health-care/work-with-us/internships-and-fellowships. **#****Click on the red “broken links found” box at the top of the page#**  Remove the link from the rich text content block (there are multiples), then **click** “Add content block.” **Select** “Link to file or video,” then enter the url you’d like to link to, and the link text. You may need to add an additional rich text block in order to place your content in the right order.

**More resources:** [CMS training: Lists, links, and media](https://youtu.be/SQXSK6tMhcw?t=521)

Please let me know if I can help answer any questions.

Hello {name},

I wanted to bring to your attention that during a recent content release a broken link was found on this node {insert parent link}.

On the node a link to {insert broken link} was entered into the rich text editor. For content consistency we’ve intentionally made this not a supported feature.

In order to add links to files you have to use the “Link to file or video” Content block. 

A good resource for linking can be found in the Session 2 training here too https://prod.cms.va.gov/help/session-2-editing-basics.

Please let me know if I can help answer any questions.

 

**First Response to little or no detail Ticket**

Hello {name}!

Thank you for bringing this to the attention of the Help Desk. It seems like {paraphrase issue}. No worries, happy to dive in. 

I do not see many details on your ticket though so I have a few questions to get us started.

Do you know….

{collaborate with team on some great questions}



 

**Response to a user experiencing a known defect**

Hello , 

The CMS team has identified that when the Front End of the website is built, it is assuming that every Story has an image. If no photo is uploaded to the Story, the published Story ends up showing a broken image.

However, for now it would be best to edit and then publish the affected Story with an image. 

To do this Edit this node, https://prod.cms.va.gov/houston-health-care/stories/new-va-clinic-open-in-ft-bend-county, and find the widget titled “Image” and add a default image using the Add media button.

Thank you for adding an image to this Story to assist in resolving this current issue. 

Let us know if we can answer any questions.

Respectfully,

**Josh Arebalo**

[VA.gov](http://va.gov/) Drupal CMS Help Desk 

*Navy Veteran*

 

**Welcome to Production (after complete training)**

Great work completing the training! 

Now that training is complete I’ve gone ahead and updated your production account.  You can now visit https://prod.cms.va.gov and log in using your PIV card for credentials. 

The VAMC - web modernization team will be sending you an invite for Production Office Hours that occurs each week on Tuesday. If you have not received this invitation please reachout to Stan Gardner or Lisa Trombley. 

This Production Q & A session is a great place to ask questions and see how to complete common tasks using the Drupal CMS. 

You can also find many answers to your questions by reviewing the Help Desk’s Help Center found here https://prod.cms.va.gov/help. 

Again, welcome aboard! We are excited to have you utilizing the new VA.gov Drupal CMS to engage Veterans worldwide. 

Respectfully,
**Josh Arebalo**

VA.gov Drupal CMS Help Desk

*Navy Veteran*

 

**Facilities API Referral**

Bernardo, 

The [VA.gov](http://va.gov/) CMS is the source for most content visible to Veterans on [VA.gov](http://va.gov/) but some content is also managed in other databases, including facility addresses, hours, and contact info, and made available to [www.va.gov](http://www.va.gov/) via APIs managed by Lighthouse. 

To request a correction to this data, contact api@va.gov and they will route your request to the appropriate team. Be sure to include your facility ID and a clear description of the data issue.

The facility ID for this specific issue is vha_460HE.

Thanks for working with Lighthouse to get this correct and please let me know if you need to discuss this further I’m happy to look into it more with you if needed.

Respectfully,

 

**Follow up to ongoing issue**

I wanted to follow-up with an update to the {specific} issue where {describe issue}. I’ve learned that the front end team were able to schedule it for priority during the next week or so. 

I’ll follow up with you again after the solution is implemented, tested, and verified.

**OR**

It looks like a current issue in the front end tool, not Drupal, that another team is currently working on. 

We’ll follow the status of that issue and update you when there’s a resolution.

Thanks for your patience,

**OR**

I just wanted to check-in and let you know that this issue is still persisting for now.

I appreciate your patience while our engineers continue to work through their backlog to resolve the issue {enter issue}.

 

**Response to a Suggestion or Tip**

Thank you for your response. I will log your suggestion. I will keep this ticket in pending status and follow up when we have a resolution.

**Remove a Staff Profile**

Hello {name},,

To prevent accidental deletion, you won’t be able to delete any content in Drupal CMS. You’ll want to use the Save as menu in the Editorial Workflow and select Archived. That will prevent it from being visible on [VA.gov](http://va.gov/). 

You may want to hold off on archiving any staff profiles, as the training environment is not connected to the live production environment that feeds information onto [VA.gov](http://va.gov/). It would be better to wait until after you’ve completed training and you have access to the production environment so you can do it there.

If you want me to archive any of those staff profiles on the production environment so you don’t forget, please reply to this email with the staff profile(s) that need archiving in production, and I’ll take care of that for you.

Please let me know if you need anything else.

Thanks,

 

**FYI to Upgrade Team - New Users Created**

[@Stan Gardner (GovernmentCIO)](https://dsva.slack.com/team/UPNNX8AGP) [@Lisa Trombley](https://dsva.slack.com/team/UPLDHNN7N) Vaughan Dill at Kansas City VA Medical Center has requested himself with Publisher role.  He is not a POA but states he is taking lead - is he good to go for the Publisher role?  FYSA he’s got both accounts in prod and training and I’ve sent him a training invite as well. Thank you!

 

**Explanation of lag in Training content vs production**

Thanks for getting started with Drupal CMS training. I apologize for any confusion about the training.

You are absolutely doing great in training. Because your site is so new, a lot of content is still being added for your system, so you won’t see exactly the same amount of content for your site as for the fully built out example in the training video. You also won’t get publisher access until your site is further built out in the live production environment.

Please go as far as you can in each activity. I’m able to track when you’re logging in and trying out the activities and will count your effort.

Again, I apologize for any frustration and I will use your feedback to improve future training updates.

Please let us know if you have any other questions.

 

**Broken Link - Missing https:// (malformed URL includes** [**https://www.va.gov**](https://www.va.gov)**/somewebsite.com)**

We’ve detected a broken link in content you’ve edited recently. **Please repair the link as soon as possible** to avoid further errors publishing your content live on VA.gov. 

**What went wrong:** It looks like you entered a url and did not include “https://”. This results in the CMS treating the lnk as a relative link and the result is a URL that is broken. 

**How to fix it:** Go to https://prod.cms.va.gov/hudson-valley-health-care/work-with-us/internships-and-fellowships. **Click** the red banner to reveal the broken link

It should look like https://prod.cms.va.gov/facebook.com/vaphs. 

Please edit the link above so that it links to [https://www.facebook.com/vaphs](https://facebook.com/vaphs)**.**

**Thank you for resolving this issue,**

**Josh Arebalo**

VA.gov Drupal CMS Help Desk

*Navy Veteran*

 

### **Follow up to Completion of Training**

Hello Cynthia!

Thanks for contacting the Drupal CMS help desk and letting us know that you’ve finished training. Congratulations.

We’ll let the VAMC Upgrade team know you’ve completed training.

They will follow up with the playbook when your system gets closer to dual state.

You should also receive an invitation to the Production Office Hours meeting weekly which is a great place to get your questions answered.

In the meantime, feel free to continue to practice in the training environment but do remember that changes you make there aren’t connected to the live production site.

You are also active on the production site and you can login using your PIV card as the credentials.

Please let us know if you need any other assistance.

Respectfully,

 

 

### **No Acknowledgement after Welcome email**

The CMS Help Desk routinely reviews tickets older than a few days for suitability to close the issue. 

It looks like Help Desk sent a welcome to training email last week with credentials to login and begin training.

Did you receive this email from our CMS Help Desk? 

 

### **Confirming removal of menu link**

Hello {user},

We’ve removed **Homeless Program Traditional Housing** from the **Programs** menu for **VA Eastern Oklahoma health care**. Please allow about an hour for that change to be displayed on your system page after the next hourly content release.

### **Broken link notification**

A broken link just popped up for this recently published page:[ https://prod.cms.va.gov/pittsburgh-health-care/programs/covid-19-vaccines](https://prod.cms.va.gov/pittsburgh-health-care/programs/covid-19-vaccines)

It looks like the “MyHealtheVet” link in the “ Eligible enrolled Veterans” section is missing an https:// in the URL.

Could you take a look when you have a moment please?

Thank you.

 



### **How to add a Youtube Video**

To add a YouTube video that is part of the[ VA.gov](http://va.gov/) YouTube Channel, the process requires first entering the video into the CMS’ Media Library and then adding the specific video to the node where it should be displayed. To do this:

1. Enter the video into the

**Media** **Library**

a.   Log into the production environment

b.   From the tab bar up-top hover your cursor over **Content**

**c.**   Select **Media Library**

d.   Then, click **Add Media**

e.   Click **Video**

​                          i.   Enter the YouTube video URL into the **Video** **URL** field

​                          ii.   Copy the **Title** of the YouTube video and enter into the **Name** field

​                         iii.   Under **Section** **Settings**, select your **Section**

​                         iv.   **Enter** other Option information (i.e., duration)

​                          v.   Click **Save**

1. Edit the node where the YouTube video should be expected

a.   Add the **Link to file or video** Content Block

​                          i.   Under the **Main Content** section, Click **Add** **Content** Block button

​                          ii.   Click **Add** for the **Link to file or video** content block type

​                         iii.   Click **Add** **Media** button

​                         iv.   Select **Video** in the sidebar

​                          v.   Enable the **checkbox** for the proper YouTube video and then click the **Insert** **Selected** button

​                         vi.   Enter **Link Text**

​                        vii.   Finish editing and **Save** the node

 

 

## Communications with VAMC system

### VA Web Modernization Team (Upgrade Team)

From time to time it may be necessary to share with the Upgrade team the status of users, training, onboarding, or other issue pertaining to content creators and editors of the VAMC health care system.

The preferred method to communciate with the Upgrade Team is to use Slack at the channel #vamc-editor-support.  

## Onboarding

The process to onboard a new user to training is as follows:

1. Receive a request to create a new user to the JIRA Active Queue
2. Help desk support should create a new user on both the production and training server following standard SOP for user account administration. 
3. After account create, send **Welcome to Training** e-mail
4. Once training is complete notify the **Upgrade Team** by sending the Slack message **Slack to Upgrade team for completion of training**.
5. Follow-up to the CMS user with **VAMC CMS user completes training** or **VAMC CMS user completes training but not ready**

### Welcome to Training

Welcome to the VA.gov Drupal CMS on-demand training. 

We are excited for you to create and share information with Veterans using this new editorial tool!

Training is required before you get an account on the production (live) environment of the Drupal CMS.

To complete training:

Open a browser to the training environment, https://cms-training-pion6wlxie1t0mckcgwjl4kid1k1haqe.demo.cms.va.gov/help/access-training#available-trainings

*Bookmark* this page (CTRL-D) and log in with:

Username: *your VA.gov email address*

Password: *drupal8*

_Your PIV card login won’t work on the training environment_

Once logged in click *Help* then click *Get Training*

Watch the trainings for your product (VAMCs, Vet Centers, etc.) and complete the *activities* when prompted.

*Reply* to this message when you’ve completed the orientation *video* and *activities* 

Thanks! 

_And remember, the best way to get the VA CMS Help Desk is to visit the Help page and click Contact Help Desk or just bookmark this link https://va-gov.atlassian.net/servicedesk/customer/portal/3/group/8/create/26_

Cheers for now,

*Josh Arebalo*

VA.gov Drupal CMS Help Desk

_Navy Veteran_



### **Slack to Upgrade team for completion of training**

FYSA we’ve added a new VAMC editor {users name} for <insert Section> per <insert requesting POA>. The user is <**active**/**inactive**> in <select “**training env.**” or “**prod**”> With content publisher, inactive in prod until training is completed. 

The user’s info is:

<insert name>

<insert title>

<insert email>

<insert phone>

<insert help desk ticket link>

### **VAMC user completes training but not ready**

Thanks for contacting the Drupal CMS help desk and letting us know that you’ve finished training. Congratulations.

We’ll let the VAMC Upgrade team know you’ve completed training. They will follow up with the playbook when your system gets closer to dual state.

In the meantime, feel free to continue to practice in the training environment but do remember that changes you make there aren’t connected to the live production site.

Please let us know if you need any other assistance.

### **VAMC CMS user completes training**

For the next steps from here, the VAMC - web modernization team will be sending you an invite, along with several other PAOs who have completed their training, to go over the validation checklist and some do’s and don’ts in the production environment. This is an introduction to the product environment and an overview of what areas to focus on in your validation. They will also provide a checklist to help you in your journey.

All the new production users will also be invited to the Production Q & A, Open office where we take questions and provide screen share of how to resolve questions and complete tasks.

Please be on the lookout for both invites.

## Other comms to upgrade team

### **Notifying of tickets requiring the teams attention**

FYSA Shannon Arledge from Shreveport opened a ticket with help desk regarding Texarkana VA clinic health services not appearing as expected on the clinic’s page. It looks like there is something wrong with this node because the facility health service is labeled as Texarkana but the owner is Shreveport. 

# Announcements 

## **Announcement - Limited Accessibility**

Limited accessibility for CMS demo + CI environments tomorrow evening.

Due to a system update (Tugboat upgrade!), the CMS demo and CI environments will be intermittently inaccessible **tomorrow form 6:00 p.m. to 8:00 p.m. ET.**

Reach out in #cms-support or via the CMS Help Desk with questions or concerns. 
