# 1.1.0 (2024-03-22)
* Available node versions updated to 14, 16, 18, 20 and 21
* PHP 8.2 & 8.3 added to php version selection
* Postgres versions updated
* Removed composer version selection, now installs latest

# 1.0.9 (2022-07-14)
* Timezone support, new `set:timezone` command added.
* PHP `memory_limit` set to `-1`
* PHP `max_input_time` set to `-1`
* PHP `max_execution_time` set to `0`

# 1.0.8 (2022-06-14)
* Fix use command by using docker-compose down & up
* Add scan command to run configure and restart containers

# 1.0.7 (2022-06-13)
* Fix composer patching
* Increase PHP memory_limit to 1024M
* Increase PHP max_execution_time to 120
* New command: servd drush:install (Drush install with version selection)
* New command: servd drush:uninstall (Uninstall drush)

# 1.0.6 (2022-06-13)
* Adjust Drupal driver for project detection

# 1.0.5 (2022-06-13)
* Add Drush v10 as global composer require in core container

# 1.0.4 (2022-05-10)
* Fix Nginx configuration

# 1.0.3 (2022-05-08)
* Laravel driver fixed for Laravel 9
* Nginx configuration modified to increase project allowance
* Add PHPCS to GitHub Actions

# 1.0.2 (2022-05-04)
* Github Actions workflow added to run tests including coverage sent to codecov
* Fix tests failing on CI due to folders not being present
* README updates to include badges (version, build status and code coverage)
* Fix switching PHP versions not updating the stored service version

# 1.0.1 (2022-05-04)

* Fix error in nginx configuration preventing HTTP sites from working
* Improve nginx configuration to better support wildcards
* Prevent database migration confirmation prompt on install

# 1.0.0 (2022-05-04)

* Initial release
