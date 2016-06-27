Updating Existing Records
=========================

If your form data needs to update, you can add the record IDv as a hidden field. 

```twig
    {{ boltforms('my_form',
        override = {
            'fields': {
                'id': {
                    'type': 'hidden',
                    'required': true,
                    'options': { 'data': record.id }
                }
            }
    }) }}
```

**NOTE:**
As the "id" field doesn't exist in the config file, it must be overridden
under the 'fields' key, unlike existing form fields can be overridden directly
by name.

**NOTE:** 
The "id" field should not be present in your form's field configuration 
`app/config/extensions/boltforms.bolt.yml` file.





```twig
    {{ boltforms('my_form',
        defaults = {
            'field_1': record.field_1,
            'field_2': record.field_2,
            'field_3': record.field_3,
            'field_4': record.field_4,
        },
        override = {
            'fields': {
                'id': {
                    'type': 'hidden',
                    'required': true,
                    'options': { 'data': record.id }
                }
            }
    }) }}
```
