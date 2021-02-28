# Gragrid: Gravity Forms + SendGrid

[![GitHub Workflows](https://github.com/vlasscontreras/gragrid/workflows/Build/badge.svg)](https://github.com/vlasscontreras/gragrid)
[![Version](https://img.shields.io/badge/version-2.0.0-brightgreen.svg)](https://github.com/vlasscontreras/gragrid)
[![WordPress Plugin: Required WP Version](https://img.shields.io/badge/wordpress-v5.2-blue)](https://github.com/vlasscontreras/gragrid)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/badge/wordpress-v5.6.2%20tested-brightgreen)](https://github.com/vlasscontreras/gragrid)

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

_Inspired by [Gravity Form Mailchimp Add-On](https://www.gravityforms.com/add-ons/mailchimp/)._
