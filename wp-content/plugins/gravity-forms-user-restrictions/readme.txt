=== Gravity Forms User Restrictions ===
Contributors: cyrilbatillat
Donate link: 
Tags: gravity forms, gravityforms, restriction, user restriction, form settings
Requires at least: 3.5
Tested up to: 3.7.1
Stable tag: 1.0.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Restrict the number of user submissions on Gravity Forms

== Description ==

User restriction can be applied using multiple parameters : 

1. IP
1. User ID
1. Embed URL
1. Or your own parameter, using plugin hook

Restrictions can be limited in time : 

1. No limit
1. Daily
1. Weekly
1. Monthly
1. Yearly
1. Your own duration, using plugin hooks

Restriction message can be customized on each form.

Multilingual : english and french translations (.pot provided).

== Installation ==

= This plugin works with Gravity Forms 1.7+ =

1. Upload the plugin to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the settings pages of your forms to manage user restrictions

== Frequently asked questions ==

= Can I add my own restriction rules ? =
Yes. The plugin provides custom hook to add your own restriction filters

== Screenshots ==

1. User restrictions in form settings

== Changelog ==

= 1.0.3 =
Get ready for languages packs (WP 3.7.1 feature)

= 1.0.2 =
Fixed bugs on uninitialized variable in settings forms
Fixed libxml warning on bad-formatted form markup

= 1.0 =
First release