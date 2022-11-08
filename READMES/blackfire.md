# Blackfire Support

Blackfire is a performance tool used for gathering and reporting how resources are used by PHP applications.  It can be used to measure the performance of a specific bit of code or an entire request.  This allows us to measure the performance impact of a code or platform change and debug performance issues.

See also: Dedicated Blackfire page in [the ddev documentation]([https://ddev.readthedocs.io/en/stable/users/blackfire-profiling/](https://ddev.readthedocs.io/en/latest/users/debugging-profiling/blackfire-profiling/)).

## Configuration
Blackfire is licensed per-seat; if you need a seat, reach out in the #cms-platform channel on DSVA Slack.  Without a license, you will not be able to use Blackfire.

Blackfire is configured for ddev at build time based on the presence of the `BLACKFIRE_CLIENT_ID` environment variable.  If it is set and non-empty, the PHP Blackfire PHP module and Blackfire Agent will be installed.  Otherwise, it will not be available.  **Lando will need to be rebuilt to recognize any changes made to this or other environment variables.**

For Blackfire to work correctly, the following four environment variables must be set.  They can be retrieved from [your Blackfire account credentials page](https://blackfire.io/my/settings/credentials).
- **`BLACKFIRE_CLIENT_ID`**
- **`BLACKFIRE_CLIENT_TOKEN`**
- **`BLACKFIRE_SERVER_ID`**
- **`BLACKFIRE_SERVER_TOKEN`**

These environment variables can be populated in several different ways according to your personal preferences and development needs.
- Recommended: if you prefer to associate these credentials only with this project, you might set them in a `.env` file in the root of this directory.
- If you only use Blackfire in this project, you might set them in your shell init script, e.g. `.zshrc` or `.bashrc`.

There might be other approaches that make more sense in your specific circumstances.

## Toggling blackfire

* Turn blackfire on with `ddev blackfire on`
* Turn blackfire off with `ddev blackfire off`

## Testing
To test that Blackfire is configured correctly, you can execute the following command once ddev has completed the build process: `ddev exec blackfire curl https://va-gov-cms.ddev.site/`

You should see a response similar to the following:
```
ddev exec blackfire curl https://va-gov-cms.ddev.site/
Profiling: [########################################] 10/10
Blackfire cURL completed
Graph                 https://blackfire.io/profiles/45ef7f4f-8c79-4ca3-b88a-d3ff9cafd07f/graph
Timeline              https://blackfire.io/profiles/45ef7f4f-8c79-4ca3-b88a-d3ff9cafd07f/graph?settings%5Bdimension%5D=timeline
No tests!             Create some now https://blackfire.io/docs/cookbooks/tests
2 recommendations     https://blackfire.io/profiles/45ef7f4f-8c79-4ca3-b88a-d3ff9cafd07f/graph?settings%5BtabPane%5D=recommendations

Wall Time    25.5ms
I/O Wait     12.1ms
CPU Time     13.4ms
Memory       3.62MB
Network         n/a     n/a     n/a
SQL          1.72ms     5rq
```

## Credits
The `blackfire-init.sh` script is based on [this gist](https://gist.github.com/tylerssn/8923149702d4a796c5e103412c2370c3) by @tylerssn.
