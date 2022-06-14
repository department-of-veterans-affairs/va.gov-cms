# Memcache(d)

In Lando, Tugboat, and BRD environments, Drupal uses [Memcache](https://memcached.org/) and the [memcache](https://www.php.net/manual/en/book.memcache.php) PHP module to improve performance by relieving the load on the RDBMS.

## BRD (Staging and Production)
BRD utilizes a Memcache cluster on [AWS ElastiCache](./elasticache.md).  The PECL memcache module is installed and configured [via Ansible](https://github.com/department-of-veterans-affairs/devops/pull/8943/files) and the Memcache nodes are listed explicitly in the `CMS_MEMCACHE_NODES` environment variable.

This environment variable is read, split, and mapped in [settings.brd_common.php](./docroot/sites/default/settings/settings.brd_common.php) to populate `$settings['memcache']['servers']`.

## Tugboat
Tugboat creates a single-node Memcache cluster, available at the hostname `memcache` and the default port of 11211.

## Lando
Lando creates a single-node Memcache cluster, available at the hostname `memcache` and the default port of 11211.

## Troubleshooting

### Testing Memcache directly
Memcache can be tested and interacted with directly using `telnet`.

On BRD (using [ssm-session](https://github.com/department-of-veterans-affairs/devops/tree/master/utilities/ssm-session)):
```sh
./ssm-session vagov-staging cms-test
source /etc/sysconfig/httpd
sudo yum install telnet
telnet "${CMS_MEMCACHE_NODES%,*}" 11211 # Connect to the first node.
```

On Tugboat:
```sh
tugboat shell $PREVIEW_ID
apt-get install telnet
telnet memcache 11211
```

On Lando:
```sh
lando ssh -u root
apt-get install telnet
telnet memcache 11211
```

#### Stats
While telnetted into Memcache, the `stats` command will return a (long) list of statistics useful for confirming basic health and function of the cluster.

```
sh-4.2$ telnet memcache 11211
Trying 10.247.34.20...
Connected to 10.247.34.20.
Escape character is '^]'.
stats
STAT pid 1
STAT uptime 1198799
STAT time 1618942692
STAT version 1.6.6
STAT libevent 2.0.21-stable
STAT pointer_size 64
STAT rusage_user 71.834122
STAT rusage_system 73.660193
STAT max_connections 65000
STAT curr_connections 7
STAT total_connections 13695
STAT rejected_connections 0
STAT connection_structures 73
STAT response_obj_bytes 112840
STAT response_obj_total 91
STAT response_obj_free 90
STAT response_obj_oom 0
STAT read_buf_bytes 180224
STAT read_buf_bytes_free 163840
STAT read_buf_oom 0
STAT reserved_fds 10
STAT cmd_get 1395862
STAT cmd_set 135456
STAT cmd_flush 2
STAT cmd_touch 0
STAT cmd_config_get 79914
STAT cmd_config_set 1
STAT cmd_meta 0
STAT get_hits 684482
STAT get_misses 711380
STAT get_expired 0
STAT get_flushed 0
STAT delete_misses 3021
STAT delete_hits 2290
STAT incr_misses 0
STAT incr_hits 0
STAT decr_misses 0
STAT decr_hits 0
STAT cas_misses 0
STAT cas_hits 0
STAT cas_badval 0
STAT touch_hits 0
STAT touch_misses 0
STAT auth_cmds 0
STAT auth_errors 0
STAT bytes_read 503571337
STAT bytes_written 5104566171
STAT limit_maxbytes 13932429312
STAT launch_time_maxbytes 13932429312
STAT accepting_conns 1
STAT listen_disabled_num 0
STAT time_in_listen_disabled_us 0
STAT threads 2
STAT conn_yields 0
STAT hash_power_level 16
STAT hash_bytes 524288
STAT hash_is_expanding 0
STAT curr_config 1
STAT slab_reassign_rescues 0
STAT slab_reassign_chunk_rescues 0
STAT slab_reassign_evictions_nomem 0
STAT slab_reassign_inline_reclaim 0
STAT slab_reassign_busy_items 0
STAT slab_reassign_busy_deletes 0
STAT slab_reassign_running 0
STAT slabs_moved 0
STAT lru_crawler_running 0
STAT lru_crawler_starts 92310
STAT lru_maintainer_juggles 13914112
STAT malloc_fails 0
STAT log_worker_dropped 0
STAT log_worker_written 0
STAT log_watcher_skipped 0
STAT log_watcher_sent 0
STAT bytes 103454004
STAT curr_items 32001
STAT total_items 135456
STAT slab_global_page_pool 0
STAT expired_unfetched 24747
STAT evicted_unfetched 0
STAT evicted_active 0
STAT evictions 0
STAT reclaimed 36231
STAT crawler_reclaimed 0
STAT crawler_items_checked 665253
STAT lrutail_reflocked 771
STAT moves_to_cold 154160
STAT moves_to_warm 75333
STAT moves_within_lru 58759
STAT direct_reclaims 0
STAT lru_bumps_dropped 0
END
```

#### Flushing Data

If Memcache holds corrupted data, or for some other reason should be cleared manually (e.g. if Drush and Drupal Console do not work, or for debugging), the `flush_all` command can be used:

```
stats
...
STAT bytes 111303
STAT curr_items 27
...
flush_all
OK
stats
...
STAT bytes 0
STAT curr_items 0
...
```

Most of the stats (hits, misses, connections, etc) will be retained, so this is not equivalent to restarting the cluster.


### Recovering From a Memcache Failure

If ElastiCache or Memcache fails or is not available for any reason (networking, PHP extension issues, etc), recovery should be easily accomplished.

The critical code instructing Drupal to _use_ Memcache (rather than just informing Drupal that the cluster exists) is located near the bottom of [settings.php](./docroot/sites/default/settings.php):

```
// Memcache-specific settings
if (extension_loaded('memcache') && !empty($settings['memcache']['servers'])) {
  $settings['cache']['default'] = 'cache.backend.memcache';
  $settings['memcache']['bins'] = [
    'default' => 'default',
  ];
  $settings['container_yamls'][] = $app_root . '/' . $site_path . '/../default/services/services.memcache.yml';
}
```

The conditional depends on two factors, either of which will be sufficient to disable Memcache:
1. The PECL Memcache module being loaded and configured for use by PHP.
2. The `$settings['memcache']['servers']` array being populated.

The PHP ini file that configures Memcache can be located with the following command:

```sh
php --ini | grep memcache
```

#### BRD

##### Temporarily
Thus, on BRD, the PHP Memcache extension can be disabled temporarily for incident response:

```sh
mv /etc/php-7.3.d/20-memcache.ini ./            # Output of command above
sudo /etc/init.d/apache2 reload
php -m                                          # Should not list the Memcache module
```

The Memcache extension will be restored on the next deploy, or the file can simply be moved back into place and Apache reloaded again to reenable it.

##### Across Deploys
If some profound issue with Memcache or its configuration is discovered, Memcache can be disabled by setting CMS_MEMCACHE_NODES to an empty string or unsetting it completely.

CMS_MEMCACHE_NODES is set in [ansible/deployment/config/cms-vagov-prod.yml](https://github.com/department-of-veterans-affairs/devops/blob/master/ansible/deployment/config/cms-vagov-prod.yml).

This change will persist across deploys.

#### Tugboat
On Tugboat, the path to the ini file will differ:

```sh
mv /usr/local/etc/php/conf.d/docker-php-ext-memcache.ini ./           # Output of command above
sudo /etc/init.d/apache2 reload
php -m                                                                # Should not list the Memcache module
```

The Memcache extension will be restored on the next deploy, or the file can simply be moved back into place and Apache reloaded again to re-enable it.

#### Lando
Lando has a dedicated command to disable Memcache:

```sh
lando memcache-off
```

This simply removes the memcache.ini file and restarts Apache.  A corresponding `memcache-on` command exists to enable Memcache again.


[Table of Contents](../README.md)
