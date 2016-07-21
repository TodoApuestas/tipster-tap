=== Tipster TAP ===
Contributors: mrbrazzi, todoapuestas
Donate link: http://todoapuestas.org/
Tags: tipster, picks
Requires at least: 3.5.1
Tested up to: 4.5.3
Stable tag: 2.4.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage picks, tipsters.

== Description ==

This plugin is to manage pick, tipsters.

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
* Added to delete all metadatas when post's metadata `_post_tipo_publicacion` equal `post` in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.4 =
* Fixed a bug detected in `__construct` function of `admin/class-tipster-tap-admin.php` file
* Added check for post's status `published` in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.3 =
* Fixed a bug detected in `save_post` function of `admin/class-tipster-tap-admin.php` file

= 1.1.2 =
* Fixed a bug detected in `admin/view/update-picks-information.php` file

= 1.1.1 =
* Fixed a bug detected in `admin/views/admin.php` file
* Deleted unnecesary files and code lines

= 1.1.0 =
* Updated some Pick's metabox fields.
* Created a plugin option page to upgrade Pick information to the new postmeta definitions.

= 1.0 =
* Initial release.

== Upgrade Notice ==

Upgrade to lastest version 2.3.x as soon as posible. See Changelog section for details


== Arbitrary section ==

Nothing for now


== Updates ==

The basic structure of this plugin was cloned from the [WordPress-Plugin-Boilerplate](https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate) project.
This plugin supports the [GitHub Updater](https://github.com/afragen/github-updater) plugin, so if you install that, this plugin becomes automatically updateable direct from GitHub. Any submission to WP.org repo will make this redundant.
