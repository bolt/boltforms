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

You can also add parameters to the BoltForms invocation in Twig. In this case the value for the field "textfieldname" will be preset "fieldvalue"

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

Redirect after submit
---------------------

On successfull submit the user can be redirected to another Bolt page, or URL. 
The page for the redirect target must exist.

The redirect is added to the `feedback` key of the form, for example: 

```yaml
  feedback:
    success: Form submission sucessful
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

Event Dispatcher Listener
-------------------------

```php
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;

$this->app['dispatcher']->addListener(BoltFormsEvents::POST_SUBMIT,  array($this, 'myPostSubmit'));
```
