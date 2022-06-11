=== Gragrid: Gravity Forms + SendGrid ===
Contributors: vlass
Donate link: https://github.com/vlasscontreras/gragrid
Tags: forms, emails, subscribers, sendgrid, gravity forms
Requires at least: 5.2
Tested up to: 6.0
Requires PHP: 7.3
Stable tag: 2.2.2
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

= Why can't I create a feed? =

Make sure you have set a valid API key in the global SendGrid settings by going to Forms > Settings > SendGrid. There should be a green checkmark next to the API key field if the API key is valid.

= What permissions do I need to select when creating an API key? =

This plugin only requires full access to **Marketing > Marketing**.

= Why the field dropdowns in the mapping settings are empty? =

* To map the **Email** you need to have a field of type [Email](https://docs.gravityforms.com/email/) or [Hidden](https://docs.gravityforms.com/hidden/).
* To map the **First Name** and **Last Name** you need to have a field of type [Name](https://docs.gravityforms.com/name/), [Text](https://docs.gravityforms.com/text-field/), or [Hidden](https://docs.gravityforms.com/hidden/)
* To map the **Address** you need to have a field of type [Address](https://docs.gravityforms.com/address-field/), [Text](https://docs.gravityforms.com/text-field/), or [Hidden](https://docs.gravityforms.com/hidden/)

= The field dropdown options in the mapping settings are blank but selectable, what's going on? =

The field dropdowns show the [Field Label](https://docs.gravityforms.com/common-field-settings/#field-label) or [Admin Field Label](https://docs.gravityforms.com/common-field-settings/#admin-field-label), so make sure you have either of those set up in your fields. Or both, it's also a good practice for accessibility!

= Why I don't see any custom fields in the form settings? =

The custom field mapping won't appear unless you have at least 1 custom field in your SendGrid account, so make sure you have at least one.

= Can this plugin be installed at the same time as the official SendGrid Gravity Forms add-on? =

Yes. The official add-on and this plugin have different purposes and do not have conflicts in between.

= Can this plugin be installed at the same time as the official SendGrid WordPress plugin? =

Yes. Just like Gravity Forms' SendGrid add-on, this plugin serves a different purpose and does not conflict with it.

== Advanced ==

You can customize the contact data sent to SendGrid in the entry submission context with this hook:

`apply_filters( 'gragrid_contact_params', array $contact_params, array $entry, array $form )`

- `$contact_params` (array): Contact parameters, includes first name, email, custom fields, etc.
- `$entry` (array): The form entry that was just created.
- `$form` (array): The current form, the origin of the submission.

You can also customize the SendGrid API requests using the following hook:

`apply_filters( 'gragrid_request_args', array $args, string $path )`

- `$args` (array): Request arguments, includes headers, method, body, etc.
- `$path` (string): The specific API endpoint being called.

== Screenshots ==

1. Plugin settings page
2. Multiple feeds, send submissions to multiple lists
3. Feed settings page

== Changelog ==

= 2.2.2 =
* Fixes the truncated contact lists on the feed settings page
* Tested on WordPress 6.0

= 2.2.1 =
* Handles fatal error on the feed listing when the API key is invalid
* Escape HTML in translation strings
* Tested on WordPress 5.8

= 2.2.0 =
* Fixes fatal error due to unitialized API class on Gravity Forms 2.5+ [[#15](https://github.com/vlasscontreras/gragrid/issues/15)]
* Adds error feedback when contact lists cannot be retrieved on the feed settings

= 2.1.0 =
* Adds support to map more reserved/default SendGrid fields
* Adds support to map custom fields 🥳
* Introduces a new filter hook `gragrid_contact_params` to filter contact data at entry context

= 2.0.0 =
* Uses Twilio SendGrid's new Marketing Campaigns API (see [#9](https://github.com/vlasscontreras/gragrid/issues/9))
* Adds address fields to map them to SendGrid Contacts
* New logo 💅🏻

= 1.1.0 =
* Adds support for conditional feeds
* Adds notes to form entries when the feeds are processed

= 1.0.0 =
* Initial release
