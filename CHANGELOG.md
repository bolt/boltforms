# Changelog for Bolt Forms

* 4.2.0
  * Added: Introduce a default theme file that can be copied into local theme. (#224 credit @jadwigo)
  * Added: Support for prefix and postfix html per-field in form theme. (#224 credit @jadwigo)
  * Added: Improvements to docs and add publishing theme via CousCous (#228)
  * Fix: Hidden Data Events fix which were not triggering on empty submit data. (#227) 

* 4.1.12 (2017-10-03)
  * Fix: Handle file uploads differently depending on whether they are saved or not (#196)

* 4.1.9 (2017-09-01)
  * Fix: Incorrect dynamic replacement of email addresses (#182)

* 4.1.1 (2017-08-02)
  * Fix: #160 Fix for Ajax forms credit @craigmillerdev
  * Docs: #161 and #162 adding documentation for choices and default values
  * Added: #164 Allowing reCAPTCHA to be disabled on individual forms
  * Fix: #159 Fix for date fields not being rendered correctly

* 4.1.0 (2017-07-28)
  * Added: Support for invisible ReCAPTCHA
  * Fixed: CSS Styling of errors (#175)
  * Fixed: Error with saving ajax submitted forms (#160)
 
* 4.0.0 (2017-07-01)
  * Added: More details debug logging
  * Added: More complete exception handling
  * Added: Event driven choice selection
  * Added: Life cycle events for form processing
  * Added: Enable multiple submit buttons
  * Added: AJAX form submission
  * Added: More configuration objects
  * Added: File name fields are hyperlinked in emails
  * Added: Form configuration and field parameters can now be overridden in template
  * Added: Form meta data can be specificed at run-time
  * Fixed: Successfully submitted forms can no longer be resubmitted on page refresh
  * Fixed: File upload handling breakage
  * Changed: Cut over to Bolt v3 storage
  * Changed: Events renamed:
    * BoltFormsChoiceEvent to ChoiceEvent
    * BoltFormsCustomDataEvent to CustomDataEvent
    * BoltFormsEmailEvent to EmailEvent
    * BoltFormsProcessorEvent to ProcessorEvent
  * Break: Processor constructor signature changed, and logic moved into handlers & processors
  * Break: Choice field key/value pairs revierse to match Symfony

* 3.1.0 (2016-31-11)
  * Dual-license under GPL & LGPL

* 3.0.3 (2016-07-19)
  * Fixed: Errors in sent notification when ContentType saves enabled (credit: @dantleech)

* 3.0.2 (2016-06-04)
  * Fixed: Forms wiping values on failed submit

* 3.0.1 (2016-05-23)
  * Use FQCN for form types (credit @psychonaut)

* 3.0.0 (2016-04-26)
  * Release compatible with Bolt v3

* 2.5.2 (2016-03-03)
  * Decode JSON in email template if value is JSON (credit: @SahAssar)

* 2.5.1 (2015-12-12)
  * Added: BoltFormsEvents::PRE_EMAIL_SEND event

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
