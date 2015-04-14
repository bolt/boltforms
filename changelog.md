# Changelog for Bolt Forms

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
