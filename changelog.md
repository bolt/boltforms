# Changelog for Bolt Forms

* 2.5.0 (2015-09-14)
 * Added: ContentType choice control with sorting, limiting and filtering
 * Added: Moved logic handling for the field's to class
 * Break: [Minor] Choice\ContentType constructor takes different parameters

* 2.4.3 (2015-08-29)
 * Added: Icon (credit @Mikescops)
 * Fixed: Additional fix for bolt-assets path

* 2.4.2 (2015-08-28)
 * Fixed: bolt-assets was incorrectly specified as a file instead of a directory (credit @emarref)

* 2.4.1 (2015-08-26)
 * Added: Information to the readme on how to have individual templates for each form (credit @Raistlfiren)
 * Fixed: Twig parameter errors
 * Fixed: Email template overrides

* 2.4.0 (2015-08-18)
 * Added: Form specific template 
 * Added: Submissions processor event for final data manipulation
 * Added: Timestamp field event
 * Fixed: Sending of CC and bCC recipients
 * Break: Parameters for Processor::process() changed to allow better API use of custom form values

* 2.3.0 (2015-08-11)
 * Added: the ability to use ReplyTo addresses in email notifications (credit @rudott)
 * Added: Display a warning if notification debugging is on in default Twig template 
 * Added: "rossriley/formeditor" as a suggested extra package
 * Fixed: compatibility with PHP 5.3
 * Fixed: CC and bCC address handling in notifications
 * Refactor: email notification configuration handling to a separate class
 * Refactor: configuration handling to use objects

* 2.2.2 (2015-08-09)
 * Fix reCaptch displaying error on GET
 
* 2.2.1 (2015-08-08)
 * Allow redirects to use all site routes, not just records

* 2.2.0 (2015-08-08)

 * Add ability to define a redirect page after submit (credit @jadwigo & @sbani)
 * Only use reCaptcha when required (credit @sbani)
 * Added proper handling of file uploads (credit @jadwigo)
 * Added browsing of uploaded files
 * Added event base custom field data providers (credit @jadwigo)
 * Lots of small contributions from a wonderful team of people
 
* 2.1.6 (2015-06-28)

 * Fix logic on database and notification checks

* 2.1.5 (2015-05-12)

 * Fix handling of DateTime fields

* 2.1.4 (2015-04-14)

 * Fix notices

* 2.1.3 (2015-04-13)

 * Forms can define individual templates, or fall back to the global
 * Updates to the form template 

* 2.1.2 (2015-04-02)

 * Update reCapcha to latest upstream version
 * Add $app['boltforms'] service provider
 
* 2.1.1 (2015-03-28)

 * Hook into Bolt mail set up warning

* 2.1.0 (2015-03-27)

 * Update to Bolt 2.1 logger
 * Bump mimimum required version to Bolt 2.1

* 2.0.3 (2015-02-21)

 * Fix: Correct match of 'submit' field (@rixbeck)
 * Allow additional fields to be added in PRE_SET_DATA handler (@rixbeck)

* 2.0.2 (2015-02-16)

 * Show a message if the form requested in {{ boltforms() }} is not found in the configuration
 * JSON encode arrays on database writes
 * Added the ability to use Contentype records for choice field value/labels

* 2.0.1 (2015-01-18)

 * Allow BoltForms to be Twig safe and used in HTML record fields (@bobdenotter)

* 2.0.0 (2014-12-17)

 * Initial release for Bolt 2.0.0
