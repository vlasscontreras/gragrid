# Gragrid: Gravity Forms + SendGrid

[![GitHub Workflows](https://github.com/vlasscontreras/gragrid/workflows/Build/badge.svg)](https://github.com/vlasscontreras/gragrid)
[![Version](https://img.shields.io/badge/version-2.2.0-brightgreen.svg)](https://github.com/vlasscontreras/gragrid)
[![Plugin Version](https://img.shields.io/wordpress/plugin/v/gragrid)](https://wordpress.org/plugins/gragrid/)
[![PHP Version](https://img.shields.io/wordpress/plugin/required-php/gragrid)](https://github.com/vlasscontreras/gragrid)
[![WordPress Plugin: Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/gragrid)](https://github.com/vlasscontreras/gragrid)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/gragrid)](https://github.com/vlasscontreras/gragrid)
[![WordPress Plugin: Downloads](https://img.shields.io/wordpress/plugin/dt/gragrid)](https://wordpress.org/plugins/gragrid/)

Integrates Gravity Forms with SendGrid, allowing form submissions to be automatically sent to your SendGrid contact lists.

![Plugin Screenshot](assets/screenshot-1.png)
![Plugin Screenshot](assets/screenshot-2.png)
![Plugin Screenshot](assets/screenshot-3.png)

## Hooks

```php
apply_filters( 'gragrid_request_args', array $args, string $path )
```

Filters the remote request arguments used when communicating with the SendGrid API.

### Parameters

- `$args` (array): Request arguments, includes headers, method, body, etc.
- `$path` (string): The specific API endpoint being called.


```php
apply_filters( 'gragrid_contact_params', array $contact_params, array $entry, array $form )
```

- `$contact_params` (array): Contact parameters, includes first name, email, custom fields, etc.
- `$entry` (array): The form entry that was just created.
- `$form` (array): The current form, the origin of the submission.

_Inspired by [Gravity Form Mailchimp Add-On](https://www.gravityforms.com/add-ons/mailchimp/)._
