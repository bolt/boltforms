Fields
======

Each field contains an `options` key that is an array of values that is passed 
directly to Symfony Forms.  

See [the Symfony documentation](http://symfony.com/doc/current/reference/forms/types/form.html) 
for more information. 

```yaml
    fieldname:
        type: field_type
        required: true|false
        options:
            label: My Field
            attr:
                placeholder: Enter your details…
            constraints: [ NotBlank, {Length: {'min': 3}} ]
```

Field(s) default values
-----------------------

If you want a field to have a default value you can add it by using a `value` 
attribute. This value can be overwritten by the person who is submitting the 
form unless you have locked it.

To lock the value, you may use the attribute `readonly: true`.

**Note:** If you use a `value` the `placeholder` will not be used.

```yaml
    fieldname:
        type: text
        required: true
        options:
            label: My Field
            attr:
                readonly: true      # optional attribute if you want a readonly field
                value: My value
```

Alternatively, you can pass in a parameter to the Twig function:

```twig
    {{ boltforms('myform', defaults={fieldname: 'My value'}) }}
```

Hidden field
------------

If you want to have a hidden field with a default value you can add it by using
the 'hidden' `type`, and setting a `value`.
Use the option `label: false` to hide the field from the html output.

```yaml
    fieldname:
        type: hidden
        options:
            label: false
            attr:
                value: "My hidden value"
```

Choice Types
------------

Choice types in BoltForms provide three different options for choice values. 
The standard indexed and associative arrays, and Bolt specific ContentType 
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
      event_based:
          type: choice
          options:
              choices: event
      event_based_custom:
          type: choice
          options:
              choices: event::my.custom.event
```

#### ContentType Choice Control

For the Bolt ContentType options choices, you just need to make a string with 
double-colon delimiters, where:
    'contenttype' - String constant that always equals 'contenttype'
    'name'        - Name of the ContentType itself
    'labelfield'  - Field to use for the UI displayed to the user
    'valuefield'  - Field to use for the value stored

ContentType choice value lookups can optionally be sorted (`sort:`), limited 
number of records retrieved (`limit:`), or filtered based upon one or more of
the ContentType's field values (`filters:`).

```yaml
    best_pet_page:
        type: choice
        options:
            required: false
            label: What is our best pets page?
            choices: 'contenttype::pets::title::slug'
            sort: title
            limit: 5
            filters: 
                by_kenny:
                    field: ownerid
                    value: 42
                light_fur:
                    field: colour
                    value: white || grey 
                cute_factor:
                    field: checkbox
                    value: >11
```

The `sort` option takes a field name. Sorting by default happens in assending
order. To sort in a descending order, negate the field name, e.g. `-title` 

The `limit` option takes an integer that sets the maximum number of records to
be return, and in turn the maximum number of options in the select list.

The `filters` option takes an array of one or more associative arrays with
`field` and `value` keys. These filters behave the same as `where` parameters
in Bolt's twig function `{% setcontent %}` 


#### Event Choice Control

Event based choice selectors are driven by Symfony Events. By default a
`\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents::DATA_CHOICE_EVENT`
is dispatched, but that is customisable in the `choices:` key, e.g.:

```yaml
    event_based:
        type: choice
        options:
            choices: event   # This will dispatch on BoltFormsEvents::DATA_CHOICE_EVENT
    event_based_custom:
        type: choice
        options:
            choices: event::my.custom.event
```

In the above example the choices for the `event_based` field will be an array 
gathered from `BoltFormsEvents::DATA_CHOICE_EVENT`, and `event_based_custom`
will be dispatched to listeners to the `my.custom.event` event.

Each listener will be passed in a `BoltFormsChoiceEvent` event object to work
with, that has getters for field name, options, and configured choices, as well
as setters for an array of choices.

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(BoltFormsEvents::DATA_CHOICE_EVENT, [$this, 'replyChoices']);
        $dispatcher->addListener('my.custom.event', [$this, 'wantChoices']);
    }

    public function replyChoices(BoltFormsChoiceEvent $event)
    {
        $event->setChoices(['yes' => 'Yes of course', 'no' => 'No way!']);
    }

    public function wantChoices(BoltFormsChoiceEvent $event)
    {
        $event->setChoices(['yes' => 'Sure', 'no' => 'Not in this life']);
    }
```



Hidden Field Data Providers (on submission)
-------------------------------------------

BoltForms allows you to specify, and customise, certain input data upon form
submission. 

This is done via event dispatchers.

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
          table: bolt_tablename       # Optional
#         contenttype: pages          # Optional/alternative to table:
          column: remote_member_id    # Required
          min: 31000                  # Optional
```

Set the `randomfield` field value to a randomized string.

```yaml
    randomfield:
      type: hidden
      options:
        label: false
      event: 
        name: random_string
        params:
          length: 12                  # Optional, defaults to 12
```

Set the `remote_ip` field value to the remote address (IP) from the $_SERVER variables

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

Set the `testkey` field value to the value for the session variable named "testkey"

```yaml
    testkey:
      type: hidden
      options:
        label: false
      event: 
        name: session_value
        params:
          key: testkey
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
