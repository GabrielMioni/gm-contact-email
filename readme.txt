=== GM Contact Form ===
Contributors: Gabriel Mioni
Tags: contact form, form, email, javascript, ajax
Requires at least: 3.0.1
Tested up to: 4.8.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple foolproof contact form for WordPress.

== Description ==

GM Contact Form is an easy to implement and use way to setup a contact form on your WordPress site. Configuration is optional. The plugin supports Ajax, but if JavaScript is unavailable on the client's browser it will still work.

The GM Contact Form includes the following inputs. Those marked by asterisks are required:
- Name *
- Company
- Email *
- Message *

Once installed and activated, the GM Contact Form can be placed on a WordPress page by using the shortcode [gm-contact-form][/gm-contact-form]. It's that simple.

When a user submits the Contact Form, they receive a thank you message when the email is sent. If the contact form user missed putting data in one of the required fields or if the email address they provided doesn't look right, they'll receive messages letting them know what corrections should be made.

== Installation ==

1. Upload the gm-contact-email folder and its contents to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Configuration ==

Configuration is optional. By default GM Contact will send emails from the Contact Form to the email address registered
with the WordPress admin account.

If you want the GM Contact Form to send emails to a different address:

1. Log into your WordPress admin panel.
2. Browse to Settings > GM Contact.
3. Enter the email address and name you would like the GM Contact Email plugin to use.

== Frequently Asked Questions ==

1. How do you add/alter input fields on the GM Contact Form.

- You don't. If you need more or different fields I recommend that you use [Contact Form 7](https://wordpress.org/plugins/contact-form-7/).
The goal of GM Contact Form is to be as simple as possible. If you have a specific need, feel free to contact me.

2. What about CAPTCHA?

- The plugin includes simple security that I think will be appropriate in most cases. An option for reCaptcha may
be available in a later version.


== Changelog ==

= 1.0 =
* Initial Release
