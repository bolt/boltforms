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

Event Dispatcher Listener
-------------------------

```php
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

$this->app['dispatcher']->addListener(BoltFormsEvents::POST_SUBMIT,  array($this, 'myPostSubmit'));
```
