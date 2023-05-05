## Dependabot Alerts Policy

### Background

Dependabot creates [alerts](https://github.com/department-of-veterans-affairs/va.gov-cms/security/dependabot) in response to security
vulnerabilities detected within project dependencies. In the case of the CMS, these dependencies can be any of several different 
languages, installed through several package managers, span multiple subprojects and concerns within the greater CMS project, and be
easily forgotten.

This policy's intent is to systematize how we handle these on an ongoing basis.

### Recommendations

1. While it is important to maintain security, we acknowledge that anyone can run `composer outdated` or `npm audit` on our codebase. 
   Given the nature of these vulnerabilities and how infrequently they are remotely exploitable, we have decided to minimize the 
   overhead of managing these issues and the resulting work by maintaining them in the open, within the public repository.

   However, if a vulnerability is identified as remotely exploitable or otherwise problematic, discussions and issue handling should
   be conducted within a more secure context, such as within Slack.

2. Any engineer can handle these updates, but we should restrict managing them to a single team (at present, the Platform CMS team).
   This is intended to minimize overhead from communication and negotiation between teams, and ensure that each alert is assigned to
   an engineer during Sprint Planning.

3. Carefully evaluate and consider ignoring vulnerabilities in `devDependencies` (Node) and `dev-dependencies` (Composer). Dev 
   dependencies are generally not used in our production environments, so we don't need to worry about them being remotely exploited
   at runtime. We control the build and test processes during which these dependencies are used, so we do not need to worry about
   them being exploited then.

   If a bad actor gains control over our build or test processes, they are not going to waste their time exploiting these
   vulnerabilities when they have far more tempting targets. They could just inject code into any file and have that executed whenever
   and wherever they wish.

   There is a definite risk of a compromised developer dependency injecting malicious code somewhere, so this should not be taken as
   a suggestion to ignore all alerts on developer dependencies, or to do so by default. But we should evaluate the vulnerability in
   terms of the risk it poses and be prepared to dismiss it if that risk is negligible.

4. Evaluate alerts based on the following criteria: does addressing this alert:
   - increase our productivity?
   - increase our stability, or help us recover from outages/breaches?
   - increase our capacity?
   - _actually_ remedy a reasonable threat to our security, stability, or reliability?

   If the alert does not meet any of these criteria, we should _not_ proceed with addressing it, and should simply dismiss the alert and
   provide our reasoning for doing so in the "Dismissal comment" field.
   
5. Triage and create issues for Dependabot alerts during DevSecOps Refinement meetings. We don't need a lot of boilerplate here, and
   these things are almost impossible to size. But we can at least say "Try to knock out the stack overflow vulnerability in padLeft" 
   or whatever.

   If multiple issues are present within a single project, we face the possibility of merge conflicts and additional toil on top of the
   toil inherent to these issues; we should therefore try to solve multiple issues in a single project in one fell swoop where possible,
   allowing for the possibility that one or more of those issues may be thorny enough, after investigation, to warrant a followup ticket.

6. Assign issues as part of sprint planning. As mentioned above, these should be rotated. We should acknowledge the possibility that any
   given issue might become incredibly complex and frustrating to manage, however simple it might seem at first.

   **This is toil**, so it should be rotated as broadly as possible. This should not be the responsibility of a single person.
