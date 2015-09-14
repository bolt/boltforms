Bolt Forms
==========

Bolt Forms is an interface to Symfony Forms for Bolt.  It provides a Twig template function and 
exposes a simplified API for extending as you need.

Template Use
------------

Define a form in `app/config/extensions/boltforms.bolt.yml` and add the following to your template:

```twig
{{ boltforms('formname') }}
```

Template with data
------------------

You can also add parameters to the BoltForms invocation in Twig. In this case the value for the field "textfieldname" will be pre-set "fieldvalue"

```twig
{{ boltforms('formname', 'Some text before the form', 'After the form', { textfieldname: "fieldvalue"}) }}
```

Fields
------

Each field contains an `options` key that is an array of values that is passed directly to 
Symfony Forms.  See [the Symfony documentation](http://symfony.com/doc/current/reference/forms/types/form.html) for more information. 

```yaml
    fieldname:
      type: text
      required: true
      options:
        label: My Field
        attr:
          placeholder: Enter your details…
        constraints: [ NotBlank, {Length: {'min': 3}} ]
```

Choice Types
------------

Choice types in BoltForms provide three different options for choice values. 
The standard indexed and associative arrays, and Bolt specific Contenttype 
record lookups.

```yaml
  fields:
    array_index:
      type: choice
      options:
        choices: [ Yes, No ]
    array_assoc:
      type: choice
      options:
        choices: { kittens: 'Fluffy Kittens', puppies: 'Cute Puppies' }
    lookup:
      type: choice
      options:
        choices: 'contenttype::pages::title::slug'
```

For the Bolt Contenttype options choices, you just need to make a string with 
double-colon delimiters, where:
    'contenttype' - String constant that always equals 'contenttype'
    'name'        - Name of the contenttype itself
    'labelfield'  - Field to use for the UI displayed to the user
    'valuefield'  - Field to use for the value stored

#### ContentType Choice Control

ContentType choice value lookups can optionally be sorted (`sort:`), limited 
number of records retrieved (`limit:`), or filtered based upon one or more of
the ContentType's field values (`filters:`).

```
    best_pet_page:
      type: choice
      options:
        required: false
        label: What is our best pets page?
        choices: 'contenttype::pets::title::slug'
        sort: title
        limit: 5
        filters: 
          by_kenny:
            field: ownerid
            value: 42
          light_fur:
            field: colour
            value: white || grey 
          cute_factor:
            field: checkbox
            value: >11
```

The `sort` option takes a field name. Sorting by default happens in assending
order. To sort in a descending order, negate the field name, e.g. `-title` 

The `limit` option takes an integer that sets the maximum number of records to
be return, and in turn the maximum number of options in the select list.

The `filters` option takes an array of one or more associative arrays with
`field` and `value` keys. These filters behave the same as `where` parameters
in Bolt's twig function `{% setcontent %}` 

File Upload Types
-----------------

Handling file uploads is a very common attack vector used to compromise (hack)
a server.

BoltForms does a few things to help increase slightly the security of handling
file uploads.

The following are the "global" options that apply to all form uploads:

```yaml
uploads:
  enabled: true                             # The global on/off switch for upload handling
  base_directory: /data/customer-uploads/   # Outside web root and writable by the web server's user
  filename_handling: prefix                 # Can be either "prefix", "suffix", or "keep"
  management_controller: true               # Enable a controller to handle browsing and downloading of uploaded files
```

The directory that you specify for `base_directory` should **NOT** be a route 
accessible to the outside world. BoltForms provides a special route should you 
wish to make the files browsable after upload. This route can be enabled as a 
global setting via the `management_controller` option.

Secondly, is the `filename_handling` parameter is an important consideration 
for your level of required site security. The reason this setting is important 
is, if an attacker knows the uploaded file name then this can make their job a 
lot easier. BoltForms provides three uploaded file naming options, `prefix`, 
`suffix` and `keep`. 

For example, when uploading the file `kitten.jpg` the settings would provide
something similar to the following table:

-------------------------------------
| Setting   | Resulting file name     |
|-----------|-------------------------|
| `prefix` | kitten.Ze1d352rrI3p.jpg |
| `suffix` | kitten.jpg.Ze1d352rrI3p |
| `keep`   | kitten.jpg              |

 
We recommend `suffix`, as this is the most secure. Alternatively `prefix` will
aid in file browsing. However, `keep` should always be used with caution!

Each form has individual options for uploads, such as whether to attach the 
uploaded file in the notification message, or whether to place the uploaded file
in a separate subdirectory or the given global upload target. 
 
A very basic, and cut-down, example of a form with an upload field type is given
here:

```yaml
file_upload_form:
  notification:
    enabled: true
    attach_files: true             # Optionally send the file as an email attachment
  uploads:
    subdirectory: file_upload_dir  # Optional subdirectory
  fields:
    upload:
      type: file
      options:
        required: false
        label: Picture of your pet that you want us to add to our site

```

File Upload Browsing
--------------------

When `management_controller` is enabled, a file in the `base_directory` 
location is accessible via `http://your-site.com/boltforms/download?file=filename.ext`.

These files can be listed via the Twig function `boltforms_uploads()`, e.g.  

```twig
{{ boltforms_uploads() }}
```

This can be limited to a form's (optionally defined) subdirectory by passing the
form name into `boltforms_uploads()`, e.g.

```twig
{{ boltforms_uploads('file_upload_form') }}
```

Custom Field Data Providers
---------------------------

BoltForms allows you to specify, and customise, certain input data. This is done
via event dispatchers.

The default events that can be used to get field data are:
  - next_increment
  - random_string
  - server_value
  - session_value
  - timestamp_formatted

#### Examples

Set the `remote_member_id` field value to the maximum value of the column, 
plus one, i.e. (`MAX(remote_member_id) + 1`), effectively working as an auto
increment.

```yaml
    remote_member_id:
      type: hidden
      options:
        label: false
      event: 
        name: next_increment
        params:
          table: bolt_tablename       # Optional
#         contenttype: pages          # Optional/alternative to table:
          column: remote_member_id    # Required
          min: 31000                  # Optional
```

Set the `randomfield` field value to a randomized string.

```yaml
    randomfield:
      type: hidden
      options:
        label: false
      event: 
        name: random_string
        params:
          length: 12                  # Optional, defaults to 12
```

Set the `remote_ip` field value to the remote address (IP) from the $_SERVER variables

```yaml
    remote_ip:
      type: hidden
      options:
        label: false
      event: 
        name: server_value
        params:
          key: REMOTE_ADDR
```

Set the `testkey` field value to the value for the session variable named "testkey"

```yaml
    testkey:
      type: hidden
      options:
        label: false
      event: 
        name: session_value
        params:
          key: testkey
```

Set the `sent_on` field value to the current date and/or time as formatted.

```yaml
    sent_on:
      type: hidden
      options:
        label: false
      event: 
        name: timestamp
        params:
          format: '%F %T'
```

#### Extending Available Events

Should you want to provide your own extension with a data event, you can specify
a custom event name and parameters in the field definition, e.g.:

```yaml
    my_custom_field:
      type: hidden
      options:
        label: false
      event: 
        name: favourite_colour
        params:
          foo: bar 
```

The in your extension you can add a listener on the event name, prefixed with
`boltforms.` (notice the dot) and provide a callback function that provides
the data you want set in the field.

```php
public function initialize()
{
    $eventName = 'boltforms.favourite_colour';
    $this->app['dispatcher']->addListener($eventName,  array($this, 'myCustomDataProvider'));
}
```

In the callback function, you can access any passed in parameters with `$event->eventParams()`
and persist the new data with `$event->setData()`.

```php
public function myCustomDataProvider($event)
{
    $params = $event->eventParams();
    if (isset($params['foo']) && $params['foo'] === 'bar') {
        $colour = 'green';
    } else {
        $colour = 'blue';
    }
    
    $event->setData($colour);
}
```

Redirect after submit
---------------------

On successfull submit the user can be redirected to another Bolt page, or URL. 
The page for the redirect target must exist.

The redirect is added to the `feedback` key of the form, for example: 

```yaml
  feedback:
    success: Form submission successful
    error: There are errors in the form, please fix before trying to resubmit
    redirect:
      target: page/another-page  # A page path, or URL
      query: [ name, email ]     # Optional keys for the GET parameters
```

**Note:**
* `target:` — Either a route in the form of `contenttype/slug` or a full URL
* `query:` — (optional) Either an indexed, or associative array
  - `[ name, email ]` would create the query string `?name=value-of-name-field&email=value-of-email-field`
  - `{ name: 'foo', email: 'bar' }` would create the query string `?name=foo&email=bar`

API
---

Below is a brief example of how to implement the Bolt Forms API.  For a slightly 
more detailed example, see the `Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension` 
class.

```php
// Get the API class
$forms = $this->app['boltforms'];

// Make the forms object inside the API class
$forms->makeForm($formname, 'form', $options, $data);

// Your array of file names and properties.
// See config.yml.dist for examples of the array properties
$fields = array();

// Add our fields all at once
$forms->addFieldArray($formname, $fields);

if ($app['request']->getMethod() == 'POST') {
    $formdata = $forms->handleRequest($formname);
    $sent = $forms->getForm($formname)->isSubmitted();

    if ($formdata) {
        // The form is both submitted and validated at this point
    }
}
``` 

Event Listeners
---------------

BoltForms exposes a number of listners, that proxy Symfony Forms listeners.
  * BoltFormsEvents::PRE_SUBMIT
  * BoltFormsEvents::SUBMIT
  * BoltFormsEvents::POST_SUBMIT
  * BoltFormsEvents::PRE_SET_DATA
  * BoltFormsEvents::POST_SET_DATA

Each of these match Symfony's constants, just with the BoltForms class name/prefix.

Below is an example of setting a field's data to upper case on submission:

```php
<?php
namespace Bolt\Extension\You\YourExtension;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

class Extension extends \Bolt\BaseExtension
{
    public function initialize()
    {
        // If you want to modify data, only use the BoltFormsEvents::PRE_SUBMIT event
        $this->app['dispatcher']->addListener(BoltFormsEvents::PRE_SUBMIT,  array($this, 'myPostSubmit'));
    }
    
    public function myPostSubmit($event)
    {
        if ($event->getForm()->getName() === 'my_form') {
            // Get the data from the event
            $data = $event->getData();
            
            // Set some data values to upper case
            $data['my_field'] = strtoupper($data['my_field']);
            
            // Save the data back
            $event->setData($data);
        }
    }
}
```

Templates for Custom Displays
-----------------------------

BoltForms allow you to have full control over how your form is rendered. If you would like to create a template for your 
theme, you can quickly do it for each form.

To get started, you must first configure the template by adding the following attribute: 

```yaml
formname:
  templates: 
    form: partials/_contact.twig
    ...
```

BoltForms will now use the partials/_contact.twig in your theme folder as the template for the form. You may create a 
basic form template by using the included template under assets/boltforms_form.twig

Individual attributes for each field can optionally be added easily in the Twig template by doing the following:

```twig
    {{ form_label(form['fieldName']) }}
    {{ form_errors(form['fieldName']) }}
    {{ form_widget(form['fieldName'], { 'attr': {'class': 'form-control'} } ) }}
```

Replace `fieldName` with the name you used in the form for that field.

More detailed information can be viewed at Symfony's [How to Customize Form Rendering](http://symfony.com/doc/current/cookbook/form/form_customization.html) page.
