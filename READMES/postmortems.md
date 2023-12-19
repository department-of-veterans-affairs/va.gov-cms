# Postmortems

Chances are that any situation serious enough to require an [out-of-band deploy](./devops/deploy-oob.md) will warrant a postmortem.

To create the postmortem, follow the procedure [here](https://github.com/department-of-veterans-affairs/va.gov-team-sensitive/tree/master/Postmortems). Note that this involves a pull request and review process. Don't just create it in `master` :slightly_smiling_face:

Remember that the purpose of a postmortem is to determine the root causes – the deficits in processes and tools – that made this situation possible, and reduce the likelihood of it happening again. It is not to assign blame, express guilt, etc.

## Communicating Impact

Take extra care in how you report the impact. Use actual, quantifiable figures, statistics, and graphs if you have them. Be sure to communicate the normal, background figures for comparison: the background error rate, the number of users total and the users who _weren't_ affected by the issue, etc. It's difficult to understand or convey actual impact without an understanding of the normal situation.

If you can't readily access this information:

- acknowledge that problem in the postmortem document
- open and prioritize tickets to address
- list these among your followup actions in the postmortem document

Ultimately, someone reading your postmortem should be able to come away with a good understanding of the severity of the issue and its impacts on direct users of the CMS, stakeholders within the system, and the Veteran community.

----

[Table of Contents](../README.md)
