Templates
=========

Using a BoltForm in your template
---------------------------------
Define a form in `app/config/extensions/boltforms.bolt.yml` and add the
following to your template:

```twig
    {{ boltforms('form_name') }}
```

Adding Parameters to the BoltForms tag
--------------------------------------

### Setting default values in the tag

You can also directly add parameters to the BoltForms tag in Twig. In this example
the value for the fields `field_1` and `field_2` will be pre-set "value_1" and "value_2":

```twig
    {{ boltforms('form_name',
        defaults = {
            field_1: "value_1",
            field_2: "value_2"
        })
    }}
```

### Configuration overrides in the tag

Configuration parameters can be overridden at runtime by passing them in using
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


### Pre & Post Submission HTML

Twig will be passed a context variables that include `htmlPreSubmit` and
`htmlPostSubmit`. These can be either HTML strings, or Twig template names.

An example using HTML strings:

```twig
    {{ boltforms('form_name',
        htmlPreSubmit = '<p>This will be shown before send</p>', 
        htmlPostSubmit = '<p>Form sent to the ocean…</p>'
        )
    }}
```

Or using template names:

```twig
    {{ boltforms('form_name',
        htmlPreSubmit = 'my_pre_template.twig',
        htmlPostSubmit = 'my_post_template.twig'
        )
    }}
```

### Overriding the Default Form Action

By default the form action will point to the current request URL. Occasionally
you may want to provide your own form action, to do so, just pass in an action
variable:

```twig
   {{ boltforms('form_name', action = '/my/form/handler') }}
```


Editing the form templates for custom displays
----------------------------------------------

The templates of the form itself can be optionally customised by copying any needed template 
from `extensions/vendor/bolt/boltforms/templates/` to a location in your theme.

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
        messages:   my_sub_directory/_messages.twig
```

You can override the form templates for all your forms in the top of config. To do this, 
uncomment the `templates` part of the config.

You can also override the templates for each form seperately:

```yaml
    myform:
        templates:
            form: extensions/boltforms/form.twig
```


### Customizing the form template

**When customizing Bolt forms in this way, it will override the functionality of
using the `boltforms` TWIG extension**

For example, if you modify the `action` in `boltforms` like [above](#overriding-the-default-form-action), but have a
`form_start` and closing tag, then the `form_start` tag will override the
`action`.

BoltForms allow you to have full control over how your form is rendered. If you
would like to create a template for your theme, you can quickly do it for each
form.

To get started, you must first configure the form to use your template by adding 
the following attribute:

```yaml
    form_name:
        templates:
            form: partials/_contact.twig
    …
```

BoltForms will now use the `partials/_contact.twig` in your theme folder as the
template for the form. You may create a basic form template by copying the
included template under `templates/form/form.twig`.

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

### Customizing the email template

For instance, if you want to customise the email that is sent after submitting the form, 
create your own email.twig template. To start, you can copy and edit the included template
 `templates/email/email.twig`.


### Feedback (Submission)

Success, error, and debug messages can all be found in the following blocks
messages_info, messages_error, and messages_debug. You can display the messages
by viewing `templates/feedback/_messages.twig` for reference. If you want to edit 
the feedback and messages template, be sure to override them in the config using:

```yaml
templates:
    messages:   my_sub_directory/_messages.twig
```



### Translation

**NOTE: Install the Translate and Labels extension for this**

If you want to use multilanguage in your forms, be sure to install both the 
`Translate` and `Labels` extensions in your site. The Translate extension has a template 
overriding the default form and adding translatable labels. [See the Translate docs for this][translate] 

To translate the messages edit the `_messages.twig` template as below:

change this: `<p class="boltform-info boltform-message">{{ info }}</p>`

into this: `<p class="boltform-info boltform-message">{{ l(info) }}</p>`

This adds all feedback messages to your labels.yml to translate.


[customize]: http://symfony.com/doc/current/cookbook/form/form_customization.html
[translate]: https://bolttranslate.github.io/Translate/configuration.html
