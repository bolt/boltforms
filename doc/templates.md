Templates
=========


Define a form in `app/config/extensions/boltforms.bolt.yml` and add the 
following to your template:

```twig
    {{ boltforms('formname') }}
```

### Default data


You can also add parameters to the BoltForms invocation in Twig. In this case 
the value for the field "field_1" will be pre-set "value_1"

```twig
{{ boltforms('formname', 'Some text before the form', 'After the form', { field_1: "value_1"}) }}
```

Or

```twig
    {{ boltforms('formname',
        defaults = {
            field_1: "value_1",
            field_2: "value_2",
            field_3: "value_3"
        })
    }}
```

### Configuration overrides

Configuration parameters can be overridden at runtimeby passing them in using
the `override` named parameter 

e.g.

```twig
    {{ boltforms('formname', 
        override = {
            'field_name': {
                options: {
                    data: 'A default value that you want'
                }
            }
        })
    }}  
```

or

```twig
    {{ boltforms('formname', 
        override = {
            'field_name': {
                params: {
                    contenttype: 'pages',
                    label: 'title',
                    value: 'slug',
                    limit: 5,
                    sort: 'title',
                    order: 'DESC',
                    where: {
                        and: { 'koala': 'bear' },
                        or: { 'koala': 'dangerous' }
                    }
                }
            }
        })
    }}  
```


Templates for Custom Displays
-----------------------------

BoltForms allow you to have full control over how your form is rendered. If 
you would like to create a template for your theme, you can quickly do it for 
each form.

To get started, you must first configure the template by adding the following 
attribute: 

```yaml
formname:
  templates: 
    form: partials/_contact.twig
    ...
```

BoltForms will now use the partials/_contact.twig in your theme folder as the 
template for the form. You may create a basic form template by using the 
included template under assets/boltforms_form.twig

Individual attributes for each field can optionally be added easily in the Twig 
template by doing the following:

```twig
    {{ form_label(form['fieldName']) }}
    {{ form_errors(form['fieldName']) }}
    {{ form_widget(form['fieldName'], { 'attr': {'class': 'form-control'} } ) }}
```

Replace `fieldName` with the name you used in the form for that field.

More detailed information can be viewed at Symfony's 
[How to Customize Form Rendering](http://symfony.com/doc/current/cookbook/form/form_customization.html) 
page.
