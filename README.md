# oik-types 
* Contributors: bobbingwide
* Donate link: http://www.oik-plugins.com/oik/oik-donate/
* Tags: custom post types, custom fields, and custom taxonomies UI for oik
* Requires at least: 3.9
* Tested up to: 4.3
* Stable tag: 1.9.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html

## Description 
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

### Features:
* Advanced API for plugin developers
* Builds on oik and oik-fields extensible architecture
* Extend pre-existing custom post type plugins that use the oik API
* Select post types to be shown on the home page
* Select post types to be publicized by Jetpack

#### Shortcodes 
oik-types does not define any shortcodes of its own. You simply use the shortcodes from oik and oik-fields

#### Actions and filter hooks 
* action "oik_types_box"
* action "oik_fie_edit_field_options"
* action "oik_fie_edit_field_type_$type"
* filter "oik_query_field_types"

## Installation 
1. Upload the contents of the oik-types plugin to the `/wp-content/plugins/oik-types' directory
1. Activate the oik-types plugin through the 'Plugins' menu in WordPress

* Note: oik-types is dependent upon the oik base plugin and the oik-fields plugin.

If you don't install and activate the oik-fields plugin then oik-types won't be called to apply changes to the post type registrations.

## Frequently Asked Questions 

# What is this plugin for? 
To help you define custom content for your website without having to write any code.

# How is the data implemented? 

oik-types does not create any tables of its own.
It records the information in structured arrays in the wp_options table.

* bw_types contains the manually defined custom post types
* bw_fields contains the manually defined custom fields
* bw_f2ts contains the Fields to Types relationships
* bw_x2ts contains the Taxonomies to Types relationships

Each instance of a custom post type is created in the wp_posts table
Each instance of a custom field for a custom post type is created in the wp_postmeta table
Each instance of a custom taxonomy is created in the wp_taxonomy and related tables

# Do I need to flush permalinks? 
Yes. When you first create a new custom post type.
If you do not then you will most likely receive a 404 message.

# What other field types are there? 
The following field types are provided by the plugins listed below:

* msoft   - oik-msoft
* rating  - oik-rating
* userref - oik-user

# What is oik-fields dependent upon? 
This plugin is dependent upon the oik base plugin. It specifically includes the following files:

  oik_require( "includes/bw_register.inc" );
  oik_require( "bw_metadata.inc" );
  oik_require2( "includes/bw_fields.inc", "oik-fields", "oik" ); // When required!

# Can I get support? 
Yes. Through the oik-plugins website.

# Can this plugin be used with other CPT managers? 
Yes, it can. But I wouldn't recommend it.

# Can this plugin extend other CPTs? 
Yes. You can use it to override the definition of existing (custom) post types.
You can add fields but you can't remove them.

# Is there an import/export facility? 
No... but there could be as it's just a case of exporting the data from wp_options using an "Export/import options plugin."

## Screenshots 
1. Definition of the oik-fields CPT
2. Three instances of the oik-fields CPT
3. Fields defined by oik-types
4. Fields to types relationships
5. Taxonomies to types relationships

## Upgrade Notice 
# 1.9.0 
Upgrade for WordPress 4.3

# 1.9 
Upgrade for support for Genesis post type supports options

# 1.8 
Adds support to allow Media files to be chosen for Nav menu items

# 1.7 
Required to display native "attachments" on the home page. Tested with WordPress 4.1 and WordPress Multi Site.

# 1.6 
Required if you want to select the post types to be shown on the home page. Tested with WordPress 4.0 and WordPress Multi Site.

# 1.5 
Now supports "has_archive" setting on post types. Required for wp-a2z.com

# 1.4 
Added support for JetPack 'publicize' function. Improved dependency checking

# 1.3 
Required for oik-plugins use of bw_related

# 1.2 
Required for setting more information for UI defined fields.
Alternative is to use a plugin and write the required APIs

# 1.1 
* Depends on oik v2.1-alpha.1028 and oik-fields v1.19.1028

# 0.1 
Requires oik base plugin version 2.1-alpha or above and oik-fields v1.19 or above

## Changelog 
# 1.9.0 
* Fixed: Applied a workaround to overcome problems raised as WordPress TRAC #33543.
* Tested: with WordPress 4.3

# 1.9 
* Added: Support for 'post type supports' options for Genesis: genesis-layouts, genesis-seo, genesis-scripts, genesis-cpt-archives-settings
* Added: Screenshots
* Added: Language files for bb_BB ( bbboing ) test language

# 1.8 
* Added: Now supports selection of Media files from Nav menus

# 1.7 
* Added: "home" post_type support for the native "attachment" post_type. Extends the "pre_get_posts" filter.

# 1.6 
* Added: "home" post_type_support value for identifying post types which will appear on the front page / blog page
* Added: "pre_get_posts" filter to handle post types with post_type_support of "home"
* Fixed: Column headings for Taxonomies
* Added: Show in admin bar checkbox. De-select to remove less used post types from the drop down Add new menu
* Fixed: Add "Archive?" to post type column headings.
* Started: Started developing logic to cater for changes to the "has_archive" setting, to correct rewrite rules
* Changed: Commented out most bw_trace2() calls

# 1.5 
* Added: Support for has_archive. Simple implementation as a check box.

# 1.4 
* Added: Support for JetPack 'publicize' - can be selected for ANY post type
* Changed: Dependency checking. Now depends on oik v2.2 and oik-fields v1.35

# 1.3 
* Added: Support for the #optional setting for 'noderef' and 'select' type fields
* Added: Dependency checking on oik and oik-fields

# 1.2 
* Added: Some field options can now be defined in the admin pages. types > fields
* Added: rewrite setting for types - work in progress
* Changed: Some usage notes in this file.

# 1.1 
* First version on oik-plugins.co.uk

# 0.1 
* Added: New plugin

## Further reading 
Read more about oik plugins and themes
* [oik-plugin](http://www.oik-plugins.com)
* [oik-fields](http://www.oik-plugins.com/oik-plugins/oik-fields)

