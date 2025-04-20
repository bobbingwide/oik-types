=== oik-types ===
Contributors: bobbingwide
Donate link: https://www.oik-plugins.com/oik/oik-donate/
Tags: custom post types, custom fields, and custom taxonomies UI for oik
Requires at least: 5.2.0
Tested up to: 6.7.1
Stable tag: 2.4.3
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
= 2.4.4 =
Improved support for PHP 8.4, tested with WordPress 6.8

== Changelog ==
= 2.4.4 = 
* Changed: Taxonomies admin: Support PHP 8.4. Ensure global $bw_taxonomy fields are set #28
* Tested: With WordPress 6.8 and WordPress Multisite
* Tested: With PHP 8.3 and PHP 8.4
* Tested: With PHPUnit 9.6

== Further reading ==
Read more about oik plugins and themes
* [oik-plugin](https://www.oik-plugins.com)
* [oik-fields](https://www.oik-plugins.com/oik-plugins/oik-fields) 