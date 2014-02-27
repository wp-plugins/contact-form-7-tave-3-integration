=== Plugin Name ===
Contributors: Jason Pirkey & Ryan Rowell
Tags: contact form 7, tave
Requires at least: 3.1.0
Tested up to: 3.8.1
Stable tag: 2014.02.26
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This adds Táve integration to all Contact Form 7 forms on a blog.

== Description ==

This plugin adds Táve integration to all Contact Form 7 forms on your blog.

== Installation ==

Install [Contact Form 7] (http://wordpress.org/extend/plugins/contact-form-7/) and set up a form.

Install the plugin as per usual. Add your Táve Studio Secret key and Táve Studio Alias info to the settings page.
Check your Contact Form 7 forms to ensure they use fields with these names below. All field
names should be available in your Settings>Data Administration>Integrations>New Lead API section of Táve, if they
are not there, they will be created from the form and you may not want this. Also everything is case sensitive.

FirstName, LastName, Email, HomePhone, MobilePhone, WorkPhone, Source, EventDate, JobType, Message

Then any custom fields you create through the tave custom fields in the settings.

You can find a more detailed explanation and walkthrough here: http://rowellphoto.com/tave-contact-form-integration

== Frequently Asked Questions ==

= Does this work with the forms from ProPhoto template  =

No.

== Changelog ==

= 2014.02.26 =
* Feature: added a checkbox to choose if you want the contact form 7 form to be emailed to you, or only receive the Táve form. (Thanks Shay Nartker)
* Updates to FAQ
* Small code cleanup and layout adjustments to the options page.

= 2014.02.24 = 
* set CURLOPT_FOLLOWLOCATION => false, it conflicts with users who have safe_mode turned on. (Thanks Carston Leishman)

= 2012.03.06 =
* Spelling Corrections
* extra lines for debugging no working changes

= 2011.06.02 =
* Initial release