Using Hidden Meta Data
======================

Additional data can be used for form processing, that is not sent to the user's
browser, rather stored locally.

Meta data is added in the Twig template where `{{ boltforms() }}` is used, via
the `meta` parameter. This parameter is an associative array of property names
and a set of value keys:

  - `use` — Either a string or array of places that the data should be passed to
  - `value` — A string, number or array

An example would be:

```twig
{{ boltforms('my_form',
    meta = {
        'name': {
            use: [ 'database', 'email' ],
            value: variable_value
        },
        'id': {
            use: 'database',
            value: record.id
        },
        'koala': {
            value: { food: 'Gum Leaves', shelter: 'Gum Tree' }
        }
    })
}}
```
