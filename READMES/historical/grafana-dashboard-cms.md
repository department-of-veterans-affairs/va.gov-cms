# Grafana Dashboard for CMS and Associated Panels
## Contents
1. [How to Query Loki Logs and Present Information in a Grafana Panel](#How-to-Query-Loki-Logs-and-Present-Information-in-a-Grafana-Panel)

## How to Query Loki Logs and Present Information in a Grafana Panel

This uses LogQL range aggregations to create a metric query. This effectively quantifies a log filter:

`sum by (app) (count_over_time({app="cms",filename="/var/log/messages"} |= "Cron run completed"[1m]))`

### Breaking Down the Metric Query from the Inside, Out

![image.png](https://images.zenhubusercontent.com/5f04cf8f49978b1615fee8c2/2c934cad-4392-4279-a20b-2ba27d4c47be)

Outside of the query browser the data source is set to the desired environment, i.e ( Dev, Staging, Prod). 

1. `{app="cms",filename="/var/log/messages"} |= "Cron run completed"`

In the query browser we set a couple of simple labels in the log stream selector denoted by the curly braces `{}`<sup>1</sup>. In this case we're interested in the label `app` with value `cms` and the label `filename` with value `/var/log/messages` to select that specific log.

After the stream selector `{}` we define a Log Pipeline `|= "Cron run completed"`<sup>2</sup> which will filter for logs containing the text `Cron run Completed`

2. `count_over_time( [1m])`

Then we wrap the Query Selector and Log Pipeline from above in a LogQL aggregation. `count_over_time` will count the entries for each log stream within the given range<sup>3</sup>, which is set to 1 minute `[1m]`. This effectively quantifies the log query.

3. `sum by (app) ()`

Finally, we use the `sum by` vector operation to reduce the label dimensions after the data has been aggregated. This grouping is helpful to show a single cohesive set of data. Otherwise we'd see labels in our graph for every log stream with a different `hostname`, `instance`, or `ip`.

![image.png](https://images.zenhubusercontent.com/5f04cf8f49978b1615fee8c2/c9af5e35-9346-4ebc-bb84-0a23641d7d41)

Specifically we group the data around the `app` label which is `cms` since this a common label among all the log streams. We could use anything else that is a common label between them.

### How the "Cron Run Completed" Panel Should be Configured
![image.png](https://images.zenhubusercontent.com/5f04cf8f49978b1615fee8c2/3ca621a6-193b-4189-ad03-0496f0537f69)

### References
1. https://grafana.com/docs/loki/latest/logql/#log-queries
2.  https://grafana.com/docs/loki/latest/logql/#log-pipeline
3. https://grafana.com/blog/2021/01/11/how-to-use-logql-range-aggregations-in-loki/

## How to TODO:
