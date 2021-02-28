=== Gragrid: Gravity Forms + SendGrid ===
Contributors: vlass
Donate link: https://wordpress.org/plugins/gragrid/
Tags: forms, emails, subscribers, sendgrid, gravity forms
Requires at least: 5.2
Tested up to: 5.6.2
Requires PHP: 7.2
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrates Gravity Forms with SendGrid, allowing form submissions to be automatically sent to your SendGrid contact lists.

== Description ==

Integrate your Gravity Forms with SendGrid to send submissions with email fields to your contact lists.

== Installation ==

1. Upload the extracted contents of `gravity-forms-sengrid.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Forms > Settings > SendGrid and add your API key

== Frequently Asked Questions ==

= Can this plugin be installed at the same time as the official SendGrid Gravity Forms add-on? =

Yes. The official add-on and this plugin have different purposes and do not have conflicts in between.

= Can this plugin be installed at the same time as the official SendGrid WordPress plugin? =

Yes. Just like Gravity Forms' SendGrid add-on, this plugin serves a different purpose and does not conflict with it.

== Advanced ==

You can customize the SendGrid API requests using the following hook:

`apply_filters( 'gragrid_request_args', array $args, string $path )`

- `$args` (array): Request arguments, includes headers, method, body, etc.
- `$path` (string): The specific API endpoint being called.

== Screenshots ==

1. Plugin settings page
2. Multiple feeds, send submissions to multiple lists
3. Feed settings page

== Changelog ==

= 1.1.0 =
* Adds support for conditional feeds
* Adds notes to form entries when the feeds are processed

= 1.0.0 =
* Initial release
