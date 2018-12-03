#!/bin/sh
#
# Cloud Hook: Reinstall Headless Lightning
#
# Run `drush site-install headless_lightning` in the target environment.

site="$1"
target_env="$2"

# Fresh install of Headless Lightning.
drush @$site.$target_env site-install headless_lightning --yes --account-pass=admin --site-name='Headless Lightning - Nightly Build'
drush @$site.$target_env pm-enable api_test --yes
drush @$site.$target_env config-set simple_oauth.settings public_key /home/headlessnightly/5b5cbb3034b52b0208eb5055624de07a64e2bbfca5b61d33f074d8d2074fb4fa.key --yes
drush @$site.$target_env config-set simple_oauth.settings private_key /home/headlessnightly/57050ee7319509e25c53e3954e119abe654a2c0519634e7b19e4b7cfdf8e25c5.key --yes
