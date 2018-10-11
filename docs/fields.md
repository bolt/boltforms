Fields
======

You use the fields to display all elements of your form. This might contain form fields
as well as the submit buttons. 

View the BoltForms config at `app/config/extensions/boltforms.bolt.yml` for the 
Example Showcase Form that contains all possible fieldtypes.


Fieldtypes
----------
BoltForms contains several possible fieldtypes. Most used are:
  - `type: text` - A simple textfield (see [textfields](fields/text.md))
  - `type: textarea` - A textarea (see [textfields](fields/text.md))
  - `type: email` - A textfield, especially for an email address (see [textfields](fields/text.md))
  - `type: choice` - A checkbox or radiobutton (see [choice types](#choice-types))
  - `type: file` - A file upload (see [upload-types](#upload-types))
  - `type: hidden` - A hidden field (see [hidden fields](#hidden-field))
  - `type: submit` - The submit button

There several are more specific fieldtypes for textfields. See the [textfields](fields/text.md) page for those.


Options
-------

Each field also contains an `options` key that is an array of values.

BoltForms is based on Symfony forms and uses many of its possibilities. 
The options of the fields are passed directly to Symfony Forms.
See [the Symfony documentation][forms] for more information.

```yaml
    field_name:
        type: field_type
        options:
            required: true|false
            label: My Field
            attr:
                placeholder: Enter some details…
            constraints: [ NotBlank, {Length: {'min': 3, 'max': 64}} ]
```

### Extra attributes

You may use some extra attributes in the `options` to add a _prefix_ or a _postfix_ to a field. 
These two attributes will be displayed before and after the form widget

Other attributes, like _data-example_ will be added to the input element.
You may use these to add aria roles, data attributes and other custom elements.

```yaml
    field_name:
        type: field_type
        required: true|false
        options:
            label: My Field
            attr:
                data-example: example-value
                prefix: '<p>A small HTML prefix</p>'
                postfix: '<p>A small HTML postfix</p>'
```

Default values
-----------------------

If you want a field to have a default value you can add it by using a `value`
attribute. This value can be overwritten by the person who is submitting the
form unless you have locked it.

To lock the value, you may use the attribute `readonly: true`.

**NOTE:** If you use a `value` the `placeholder` will not be used.

```yaml
    field_name:
        type: text
        required: true
        options:
            label: My Field
            attr:
                readonly: true      # optional attribute if you want a readonly field
                value: My value
```

Alternatively, you can pass in a parameter to the Twig function in your template:

```twig
    {{ boltforms('my_form', defaults={field_name: 'My value'}) }}
```

Choice Types
------------

Choice types in BoltForms provide several different options for choice values.
The standard indexed and associative arrays, and Bolt specific ContentType
record lookups & event based lookups.

```yaml
  fields:
      array_index:
        type: choice
        options:
            choices: [ Yes, No ]
      array_assoc:
          type: choice
          options:
              choices: { 'Fluffy Kittens': kittens, 'Cute Puppies': puppies }
      lookup:
          type: choice
          options:
              choices: content
      event_based:
          type: choice
          options:
              choices: event
      event_based_custom:
          type: choice
          options:
              choices: event::my.custom.event
```

**NOTE:** If you use the `array_assoc` style, the choices are inverted compared
to the select fields in Bolt contenttypes.

If you want to provide a default value for the choice type, then the syntax is
slightly different than text inputs which use the `value` attribute. Instead
pass a top-level option called `data` with the selected value. For example:

```yml
      array_assoc:
          type: choice
          options:
              choices: { 'Fluffy Kittens': kittens, 'Cute Puppies': puppies }
              data: puppies
```

For other fields based on the choice type, the same syntax applies.

```yml
        location:
            type: country
            options:
                label: Where are you From?
                data: GB
```

For more information on this field type, see the [choice fields documentation](fields/choice.md)

Upload Types
------------

### SECURITY WARNING

Handling file uploads is a very common way to compromise (hack)
a server.

BoltForms does a few things to help increase slightly the security of handling
file uploads:

1 - The directory that you specify for "base_directory" below should NOT
be an route accessible to the outside world. We provide a special route should
you wish to make the files browsable after upload.

2 - is the "filename_handling" parameter. If an attacker knows the
uploaded file name, this can make their job a bit easier. So we provide three
options, e.g. uploading the file kitten.jpg:

| Setting   | Resulting file name     |
|-----------|-------------------------|
| `prefix`  | kitten.Ze1d352rrI3p.jpg |
| `suffix`  | kitten.jpg.Ze1d352rrI3p |
| `keep`    | kitten.jpg              |

We recommend "suffix" as this is the most secure, alternatively "prefix" will
aid in file browsing. However "keep" should always be used with caution!


### Configuration Parameters

```yaml
uploads:
    enabled: false                                 # The global on/off switch for upload handling
    base_directory: /full/path/for/uploaded/files/ # Outside web root and writable by the web server's user
    filename_handling: suffix                      # Can be either "prefix", "suffix", or "keep"
    management_controller: false                   # Enable a controller to handle browsing and downloading of uploaded files
```


For more information on this field type, see the
[upload fields documentation](fields/upload.md).


Hidden field
------------

If you want to have a hidden field with a default value you can add it by using
the 'hidden' `type`, and setting a `value`. Use the option `label: false` to
hide the field from the html output.

```yaml
    field_name:
        type: hidden
        options:
            label: false
            attr:
                value: "My hidden value"
```


### Hidden Field Data Providers

**NOTE:** These field values are set upon successful submission of the form,
not during render!

BoltForms allows you to specify, and customise, certain input data upon form
submission. This is done via event dispatchers.

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
                contenttype: pages          # Optional
#                table: bolt_table_name     # Optional and alternative to contenttype:
                column: remote_member_id    # Required
                min: 31000                  # Optional
```

Set the `random_field` field value to a randomised string.

```yaml
    random_field:
        type: hidden
        options:
            label: false
        event:
            name: random_string
            params:
                length: 12 # Optional, defaults to 12
```

Set the `remote_ip` field value to the remote address (IP) from the `$_SERVER`
variables

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

Set the `test_key` field value to the value for the session variable named
"test_key"

```yaml
    test_key:
        type: hidden
        options:
            label: false
        event:
            name: session_value
            params:
                key: test_key
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

Dynamic Fields
--------------

Dynamic fields are fields that are _not_ in your BoltForms config, but are added from your template.
You can dynamically add these fields when calling the BoltForms tag `{{ boltforms() }}`,
e.g.:

```twig
    {{ boltforms('form_name',
        override = {
            fields: {
                non_config_field: {
                    type: 'text',
                    options: {
                        label: 'Once upon a time…'
                    }
                }
            }
        })
    }}
```

Controlling Field Rendering
---------------------------

If you want to simply render the remaining fields, you can use the `form_rest`
function, passing in the form variable, e.g.:

```twig
{{ form_rest(form) }}
```

Should you want to exclude a single field, you can set the `setRendered`
property for that field, e.g.:

```twig
{% do form.your_field_name.setRendered %}
```

If you know you have remaining fields, and you don't want them rendered, you
can simply pass `form_end` the `render_rest: false` option, i.e.:

```twig
{{ form_end(form, { 'render_rest': false }) }}
```

For more details, see Symfony's [Twig reference][twig].

[forms]: http://symfony.com/doc/current/reference/forms/types/form.html
[twig]: http://symfony.com/doc/2.8/reference/forms/twig_reference.html#form-rest-view-variables
