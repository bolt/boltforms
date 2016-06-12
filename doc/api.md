BoltForms API
=============

Below is a brief example of how to implement the Bolt Forms API.  For a slightly 
more detailed example, see the `Bolt\Extension\Bolt\BoltForms\Twig\BoltFormsExtension` 
class.

```php
// Get the API class
$forms = $this->app['boltforms'];

// Make the forms object inside the API class
$forms->makeForm($formName, FormType::class, $data, $options, $override);

// Your array of file names and properties.
// See config.yml.dist for examples of the array properties
$fields = [];

// Add our fields all at once
$forms->addFieldArray($formName, $fields);

if ($request->isMethod(Request::METHOD_POST) && $request->request->get($formName) !== null) {
    $formdata = $forms->handleRequest($formname);
    $sent = $forms->getForm($formname)->isSubmitted();

    if ($formdata) {
        // The form is both submitted and validated at this point
    }
}
```
