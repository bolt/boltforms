Templates
=========


Define a form in `app/config/extensions/boltforms.bolt.yml` and add the
following to your template:

```twig
    {{ boltforms('form_name') }}
```

Templates can be optionally customised by copying any needed template from
`extensions/vendor/bolt/boltforms/templates/` to a location in your theme.

For example if you were to add them to `themes/my_theme/my_sub_directory/` you
would update your `app/config/boltforms.bolt.yml` file parameters to match,
e.g.:

```yaml
    templates:
        form:       my_sub_directory/form.twig
        email:      my_sub_directory/email.twig
        subject:    my_sub_directory/subject.twig
        files:      my_sub_directory/file_browser.twig
        form_theme: my_sub_directory/_form_theme.twig
```

Default data
------------

You can also add parameters to the BoltForms invocation in Twig. In this case
the value for the field "field_1" will be pre-set "value_1"

```twig
{{ boltforms('form_name', 'Some text before the form', 'After the form', { field_1: "value_1"}) }}
```

Or

```twig
    {{ boltforms('form_name',
        defaults = {
            field_1: "value_1",
            field_2: "value_2",
            field_3: "value_3"
        })
    }}
```

Configuration overrides
-----------------------

Configuration parameters can be overridden at runtimeby passing them in using
the `override` named parameter

e.g.

```twig
    {{ boltforms('form_name',
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
    {{ boltforms('form_name',
        override = {
            field_name_1: {
                options: {
                    required: false
                }
            },
            field_name_2: {
                options: {
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
            },
            redirect: { 'target': 'http://bolt.cm' }
        })
    }}
```

**NOTE:** Where the override array key matches a field name, the field name is
overridden, if it then matches a field configuration parameter, that will be
the affected value.


Pre & Post Submission HTML
--------------------------

Twig will be passed a context variables that include `htmlPreSubmit` and
`htmlPostSubmit`. These can be either HTML strings, or Twig template names.

An example using HTML strings:

```twig
    {{ boltforms('form_name', htmlPreSubmit = '<p>This will be shown before send</p>', htmlPostSubmit = '<p>Form sent to the ocean…</p>') }}
```

Or using template names:

```twig
    {{ boltforms('form_name', htmlPreSubmit = 'my_pre_template.twig', htmlPostSubmit = 'my_post_template.twig') }}
```

Overriding the Default Form Action
----------------------------------

By default the form action will point to the current request URL. Occasionally
you may want to provide your own form action, to do so, just pass in an action
variable:

```twig
   {{ boltforms('form_name', action = '/my/form/handler' }}
```

Templates for Custom Displays
-----------------------------

BoltForms allow you to have full control over how your form is rendered. If you
would like to create a template for your theme, you can quickly do it for each
form.

To get started, you must first configure the template by adding the following
attribute:

```yaml
    form_name:
        templates:
            form: partials/_contact.twig
    …
```

BoltForms will now use the partials/_contact.twig in your theme folder as the
template for the form. You may create a basic form template by using the
included template under templates/form/form.twig

Individual attributes for each field can optionally be added easily in the Twig
template by doing the following:

```twig
    {{ form_label(form['fieldName']) }}
    {{ form_errors(form['fieldName']) }}
    {{ form_widget(form['fieldName'], { 'attr': {'class': 'form-control'} } ) }}
```

Replace `fieldName` with the name you used in the form for that field.

More detailed information can be viewed at Symfony's
[How to Customize Form Rendering][customize] page.

[customize]: http://symfony.com/doc/current/cookbook/form/form_customization.html
