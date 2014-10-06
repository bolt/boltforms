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

API
---

Each field contains an `options` key that is an array of values that is passed directly to 
Symfony Forms.  See [the Symfony documentation](http://symfony.com/doc/current/reference/forms/types/form.html) for more information. 

```
    fieldname:
      type: text
      required: true
      options:
        label: My Field
        attr:
          placeholder: 
        constraints: [ NotBlank, {Length: {'min': 3}} ]
```

Below is a brief example of how to implement the Bolt Forms API.  For a slightly more detailed 
example, examine the `BoltForms\Twig\BoltFormsExtension` class.

```php
// Get the API class
$forms = new \Bolt\Extension\Bolt\BoltForms\BoltForms($app);

// Make the forms object inside the API class
$forms->makeForm($formname, 'form', $options, $data);

// Your array of filenames and properties.
// See config.yml.dist for examples of the array properties
$fields = array();

// Add our fields all at once
$forms->addFieldArray($formname, $fields);

if ($app['request']->getMethod() == 'POST') {
    $formdata = $forms->handleRequest($formname);
    $sent = $forms->getForm($formname)->isSubmitted();
    
    if ($formdata) {
    
       // The form is both submittted and validated at this point
       
    }
}
``` 
