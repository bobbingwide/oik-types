=== oik-types ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: custom post types, custom fields, and custom taxonomies UI for oik
Requires at least: 5.2.0
Tested up to: 5.3-RC1
Stable tag: 2.0.0-beta-20191017
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Create custom post types, fields and taxonomies that are integrated with the oik API to bring the power of oik to your website content.
 
* Define new custom post types 
* Extend existing post types
* Define new custom fields
* Define the fields and taxonomy relationships
* Use WordPress admin to create and manage your content
* Use shortcodes from oik and oik-fields to display the content in ingenious ways

Requires:

* No PHP programming
* A bit of thought
* Some creative flair

For advanced users:

* Write CSS to style your content beautifully. Why not use the oik-css plugin?
* Extend your custom post types with action and hook filters
* Develop your own custom field plugins using the oik APIs

=== Features:===
* Advanced API for plugin developers
* Builds on oik and oik-fields extensible architecture
* Extend pre-existing custom post type plugins that use the oik API
* Select post types to be shown on the home page
* Select post types to be publicized by Jetpack

==== Shortcodes ====  
oik-types does not define any shortcodes of its own. You simply use the shortcodes from oik and oik-fields

==== Actions and filter hooks ====
* action "oik_types_box"
* action "oik_fie_edit_field_options"
* action "oik_fie_edit_field_type_$type" 
* filter "oik_query_field_types"

== Installation ==
1. Upload the contents of the oik-types plugin to the `/wp-content/plugins/oik-types' directory
1. Activate the oik-types plugin through the 'Plugins' menu in WordPress

Note: oik-types is dependent upon the oik base plugin and the oik-fields plugin.

If you don't install and activate the oik-fields plugin then oik-types won't be called to apply changes to the post type registrations. 

== Frequently Asked Questions ==

= What is this plugin for? =
To help you define custom content for your website without having to write any code.

= How is the data implemented? = 

oik-types does not create any tables of its own.
It records the information in structured arrays in the wp_options table.

* bw_types contains the manually defined custom post types
* bw_fields contains the manually defined custom fields
* bw_f2ts contains the Fields to Types relationships
* bw_x2ts contains the Taxonomies to Types relationships

Each instance of a custom post type is created in the wp_posts table
Each instance of a custom field for a custom post type is created in the wp_postmeta table
Each instance of a custom taxonomy is created in the wp_taxonomy and related tables

= Do I need to flush permalinks? =
Yes. When you first create a new custom post type.
If you do not then you will most likely receive a 404 message.

= What other field types are there? =
The following field types are provided by the plugins listed below:

* msoft   - oik-msoft
* rating  - oik-rating
* userref - oik-user

= What is oik-types dependent upon? =
This plugin is dependent upon the oik-fields plugin and the oik base plugin. 
 
= Can I get support? = 
Yes. Through the oik-plugins website or GitHub.

= Can this plugin be used with other CPT managers? = 
Yes, it can. But I wouldn't recommend it.

= Can this plugin extend other CPTs? =
Yes. You can use it to override the definition of existing (custom) post types.
You can add fields but you can't remove them.

= Is there an import/export facility? =
No... but there could be as it's just a case of exporting the data from wp_options using an "Export/import options plugin."

== Screenshots ==
1. Definition of the oik-fields CPT
2. Three instances of the oik-fields CPT
3. Fields defined by oik-types
4. Fields to types relationships
5. Taxonomies to types relationships

== Upgrade Notice ==
= 2.0.0-beta-20191017 =
Upgrade primarily for corrected plugin version.

= 2.0.0-alpha-20181019 = 
Upgrade for PHP 7.2 support

= 1.9.2 = 
Tested with WordPress 4.6 and REST API v2.

= 1.9.1 = 
Tested with WordPress 4.5.2

= 1.9.0 = 
Upgrade for WordPress 4.3

= 1.9 =
Upgrade for support for Genesis post type supports options

= 1.8 = 
Adds support to allow Media files to be chosen for Nav menu items

= 1.7 = 
Required to display native "attachments" on the home page. Tested with WordPress 4.1 and WordPress Multi Site.

= 1.6 =   
Required if you want to select the post types to be shown on the home page. Tested with WordPress 4.0 and WordPress Multi Site.

= 1.5 = 
Now supports "has_archive" setting on post types. Required for wp-a2z.com 

= 1.4 =
Added support for JetPack 'publicize' function. Improved dependency checking

= 1.3 =
Required for oik-plugins use of bw_related 

= 1.2 =
Required for setting more information for UI defined fields.
Alternative is to use a plugin and write the required APIs

= 1.1 =
* Depends on oik v2.1-alpha.1028 and oik-fields v1.19.1028

= 0.1 = 
Requires oik base plugin version 2.1-alpha or above and oik-fields v1.19 or above

== Changelog ==
= 2.0.0-beta-20191017 =
* Fixed: Don't adjust orderby when the post_type is explicitly set to 'post',[github bobbingwide issues 9]
* Changed: Added some simple PHPUnit tests for issues #5 and 16
* Tested: With WordPress 5.2.4 and WordPress Multi Site
* Tested: With WordPress 5.3-RC1
* Tested: With PHP 7.3 and PHPUnit 8
* Tested: With Gutenberg 6.7.0

= 2.0.0-alpha-20181019 =
* Added: Add show_in_rest checkbox [github bobbingwide oik-types issue 13]
* Changed: Add batch setting of archive_posts_per_page. First version [github bobbingwide oik-types issue 8] 
* Changed: Add more strings to oik_cpt_oik_post_type_supports [github bobbingwide oik-types issue 14]
* Changed: Add singular_name to custom taxonomies [github bobbingwide oik-types issue 16]
* Changed: Add three more post_type_supports values [github bobbingwide oik-types issue 2]
* Changed: Depends on oik v3.2.3
* Changed: Don't alter orderby for posts. [github bobbingwide oik-types issue 9]
* Changed: Eliminate deprecated messages from bw_translate [github bobbingwide oik-types issue 15]
* Changed: Force orderby title ASC for taxonomy and category queries [github bobbingwide oik-types issue 9]
* Changed: Pass $args rather than $label in oiktax_register_taxonomy
* Changed: Set posts_per_page for the main taxonomy archive query [github bobbingwide oik-types issue 9]
* Changed: Support PHP 7.1 and PHP 7.2 [github bobbingwide oik-types issue 7] 
* Tested: With WordPress 4.9.8

= 1.9.2 =
* Changed: Support for WordPress 4.6 [github bobbingwide oik-types issue 4]
* Changed: Don't prevent programmed registrations requiring REST API support [github bobbingwide oik-types issue 5]

= 1.9.1 = 
* Added: Improve support for the multi-select "post_type_support" select box [github bobbingwide oik-types issue 2]
* Added: Language files, though they may be out of date
* Added: Order by capability for the front-end. Note: this is only a partial solution
* Added: Respond to 'register_post_types_args' filter [github bobbingwide oik-types issue 3]
* Added: Workaround for WordPress TRAC 36579
* Added: posts_per_page for the front-end
* Changed: Trace levels, whitespace and docblocks
* Tested: With WordPress 4.5.2 and WordPress MultiSite

= 1.9.0 = 
* Fixed: Applied a workaround to overcome problems raised as WordPress TRAC #33543.
* Tested: with WordPress 4.3

= 1.9 = 
* Added: Support for 'post type supports' options for Genesis: genesis-layouts, genesis-seo, genesis-scripts, genesis-cpt-archives-settings
* Added: Screenshots
* Added: Language files for bb_BB ( bbboing ) test language

= 1.8 =
* Added: Now supports selection of Media files from Nav menus

= 1.7 = 
* Added: "home" post_type support for the native "attachment" post_type. Extends the "pre_get_posts" filter.

= 1.6 = 
* Added: "home" post_type_support value for identifying post types which will appear on the front page / blog page
* Added: "pre_get_posts" filter to handle post types with post_type_support of "home"
* Fixed: Column headings for Taxonomies
* Added: Show in admin bar checkbox. De-select to remove less used post types from the drop down Add new menu
* Fixed: Add "Archive?" to post type column headings.
* Started: Started developing logic to cater for changes to the "has_archive" setting, to correct rewrite rules
* Changed: Commented out most bw_trace2() calls 
 
= 1.5 =
* Added: Support for has_archive. Simple implementation as a check box.

= 1.4 = 
* Added: Support for JetPack 'publicize' - can be selected for ANY post type
* Changed: Dependency checking. Now depends on oik v2.2 and oik-fields v1.35

= 1.3 =
* Added: Support for the #optional setting for 'noderef' and 'select' type fields
* Added: Dependency checking on oik and oik-fields

= 1.2 = 
* Added: Some field options can now be defined in the admin pages. types > fields
* Added: rewrite setting for types - work in progress
* Changed: Some usage notes in this file.

= 1.1 =
* First version on oik-plugins.co.uk

= 0.1 = 
* Added: New plugin

== Further reading ==
Read more about oik plugins and themes
* [oik-plugin](http://www.oik-plugins.com)
* [oik-fields](http://www.oik-plugins.com/oik-plugins/oik-fields) 

