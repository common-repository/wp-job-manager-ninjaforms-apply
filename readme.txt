=== Apply with Ninja Forms for WP Job Manager ===

Author URI: http://astoundify.com
Plugin URI: https://github.com/Astoundify/wp-job-manager-ninja-forms-apply/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=contact@appthemer.com&item_name=Donation+for+Astoundify WP Job Manager Ninja Forms
Contributors: spencerfinnell
Tags: job, job listing, job apply, wp job manager, job manager, ninja forms, ninja, forms
Requires at least: 3.5
Tested up to: 3.9
Stable Tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Allow themes using the WP Job Manager plugin to apply via a Ninja Form.

== Description ==

Apply directly to Jobs (and Resumes if using Resume Manager) via a custom Ninja Form. Use any available Ninja Form fields to build a completely custom submission form.

= Where can I use this? =

Astoundify has released the first fully integrated WP Job Manager theme. Check out ["Jobify"](http://themeforest.net/item/jobify-job-board-wordpress-theme/5247604?ref=Astoundify)

= Tutorial Video =

https://vimeo.com/89439524/

== Frequently Asked Questions ==

= The form does not appear in my theme =

It is up to the theme to respect your choice to use this plugin (as there is no way to automatically insert the form). The easiest way is to output a shortcode with the form ID that has been specified.

`echo do_shortcode( sprintf( '[ninja_forms_display_form id="%s"]', get_option( 'job_manager_job_apply' ) );`

= I do not receive an email =

You **must** create a *hidden* field with the following specific settings:

* **Label:** `application_email`
* **Default Value:** `Post/Page ID`

[View an image of the settings](https://i.cloudup.com/pnfVzYBFiN.png)

The Job/Resume listing must also have an email address associated with it, not a URL to a website.

= I am using Jobify and it's not working =

**Please make sure you have the latest version of Jobify from ThemeForest**

If you have purchased Jobify and still have questions, please post on our dedicated support forum: http://support.astoundify.com

== Installation ==

1. Install and Activate
2. Create your forms via the Ninja Forms interface. Be sure to review the FAQ for settings details.

You **must** create a *hidden* field with the following specific settings:

* **Label:** `application_email`
* **Default Value:** `Post/Page ID`

[View an image of the settings](https://i.cloudup.com/pnfVzYBFiN.png)

3. Go to "Job Listings > Settings" and enter select the form you would like from the dropdown.

== Changelog ==

= 1.0.1: March 31, 2014 =

* Fix: Change the hook locations for filtering wp_mail() to more reliable places.
* Fix: Update text domain.

= 1.0.0: March 2, 2014 =

* First official release!