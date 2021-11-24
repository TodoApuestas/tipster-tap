=== Tipster TAP ===
Contributors: mrbrazzi, todoapuestas
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=brazzisoft.com@gmail.com&lc=US&item_name=For%20improve%20Wordpress%20plugin%20Tipster%20TAP&currency_code=USD&no_note=0&bn=PP-DonationsBF:btn_donateCC_LG.gif:NonHostedGuest
Tags: tipster, picks
Requires at least: 5.5
Tested up to: 5.8.1
Stable tag: 4.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage tipsters and picks.

== Description ==

This plugin is to manage tipsters and picks.

== Installation ==

This section describes how to install the plugin and get it working.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'tipster-tap'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `tipster-tap.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `tipster-tap.zip`
2. Extract the `tipster-tap` directory to your computer
3. Upload the `tipster-tap` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard


== Frequently Asked Questions ==

Nothing for now


== Screenshots ==

Nothing for now


== Changelog ==

= 4.2.2 =
* Added troubleshooting page
* Added some minor improvements
* Fixed errors detected

= 4.2.1 =
* Tested up to Wordpress 5.8.1
* Added some minor improvements
* Fixed errors detected

= 4.2.0 =
* Tested up to Wordpress 5.5.3
* Added some minor improvements

= 4.1.1 =
* Added some fixes

= 4.1 =
* Tested up to Wordpress 5.4.2
* Added some fixes

= 4.0
* Improved manage picks
* Added multiples improvements

= 3.7 =
* Tested up to Wordpress 4.9.4
* Added some improvements

= 3.6 =
* Added some missing validations

= 3.5 =
* Fixed bug when calculate accumulated tipster's information
* Deleted @deprecated methods

= 3.4 =
* Fixed some minor bugs

= 3.2 =
* Tested up to Wordpress 4.9.2
* Use Rest Client TAP actions and filters to access TodoApuestas's API services
* Added multiple improvements

= 3.0 =
* This is a major release, update as son as possible
* Tested up to Wordpress 4.9.1
* Added a table to save picks
* Improved tipster statistics calculation
* Improved tipster yield history calculation
* Added validation to pick's post_meta
* Added validation to tipster's post_meta
* Added backend page to migrate pick's meta information to the new picks table
* Added backend page to update and see tipster's statistics
* Added @deprecated to old methods
* Added multiple improvements
* Deleted unnecessary code

= 2.6 =
* Improved custom meta box with CMB2 plugin
* Improved pick result edit using quick edit metabox
* Tested up to Wordpress 4.6.1
* Deleted unnecessary files

= 2.5.1 =
* Added `ignore_sticky_posts` in filter `tipster_tap_get_tipster_picks`
* Tested up to Wordpress 4.5.3

= 2.5 =
* Added multiples improvements

= 2.4.6 =
* Added some improvements
* Tested up to Wordpress 4.5.3

= 2.4.5 =
* Added some improvements
* Fixed some bugs detected

= 2.4.4 =
* Fixed bug detected

= 2.4.3 =
* Added some improvements
* Fixed some bugs detected
* Tested up to Wordpress 4.5.2

= 2.4.2 =
* Added some improvements

= 2.4.1 =
* Tested up to Wordpress 4.4

= 2.4.0 =
* Added some improvements

= 2.3.2 =
* Added some minor changes

= 2.3.1 =
* Added some minor changes

= 2.3.0 =
* Added some improvements

= 2.2.2 =
* Fixed some bugs detected

= 2.2.1 =
* Fixed a bug detected in remote sync process

= 2.2.0 =
* Added support for namespaces

= 2.1.1 =
* Added some minor changes

= 2.1.0 =
* Added support for TAP Api REST's services through OAuth authentication/authorization

= 2.0.1 =
* Fixed a bug detected in update picks information process

= 2.0.0 =
* Added support for TAP Api REST's services

= 1.1.6 =
* Added function to create statistics table on plugin installation

= 1.1.5 =
* Added to delete all metadata when post's metadata `_post_tipo_publicacion` equal `post` in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.4 =
* Fixed a bug detected in `__construct` function of `admin/class-tipster-tap-admin.php` file
* Added check for post's status `published` in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.3 =
* Fixed a bug detected in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.2 =
* Fixed a bug detected in `admin/view/update-picks-information.php` file

= 1.1.1 =
* Fixed a bug detected in `admin/views/admin.php` file
* Deleted unnecessary files and code lines

= 1.1.0 =
* Updated some Pick's metabox fields.
* Created a plugin option page to upgrade Pick information to the new postmeta definitions.

= 1.0 =
* Initial release.


== Upgrade Notice ==

Upgrade to the last version 4.0 as soon as possible. See Changelog section for details.


== Arbitrary section ==

Must have installed [Rest Client TAP](https://www.wordpress.org/plugins/rest-client-tap) plugin version 1.0 o later.


== Updates ==

The basic structure of this plugin was cloned from the [WordPress-Plugin-Boilerplate](https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate) project.
This plugin supports the [GitHub Updater](https://github.com/afragen/github-updater) plugin, so if you install that, this plugin becomes automatically updateable direct from GitHub. Any submission to WP.org repo will make this redundant.
