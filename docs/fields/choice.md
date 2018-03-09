Choice Fields
=============

Simple Choice Selection
-----------------------

```yaml
    choice_simple:
        type: choice
        options:
            label: A very simple choice
            choices: { 'Item One': 'item_1', 'Item Two': 'item_2' }
```

```yaml
    group_simple:
        type: choice
        options:
            label: Grouping Simple
            choices:
                'Group Aye': { 'Item One': 'item_1', 'Item Two': 'item_2' }
                'Group Bee': { 'Item Eleven': 'item_11', 'Item Twelve': 'item_12' }
```

Display As Radio Buttons
------------------------

By setting the options `expanded: true` and `multiple: false` you can turn the dropdown into a radio button group.

```yaml
    radio_button_group_simple:
        type: choice
        options:
            label: A simple radio button group
            expanded: true
            multiple: false
            choices: { 'YES': 'yes', 'NO': 'no' }
```

ContentType Record Data
-----------------------

ContentType choice value lookups can optionally be sorted (`sort:`), limited
number of records retrieved (`limit:`), or filtered based upon one or more of
the ContentType's field values (`filters:`).

```yaml
    best_pet_page:
        type: choice
        options:
            required: false
            label: What is our best pets page?
            choices: content
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

To use ContentType records for choice data, you need to specify a `params:` key
with the following sub keys:

  - `contenttype`
  - `label`
  - `value`

Other parameters are optional.

```yaml
    contenttype_choice:
        type: choice
        options:
            required: false
            label: ContentType selection
            choices: content
            params:
                contenttype: pages
                label: title
                value: slug
                limit: 4
                sort: title
                order: DESC # "ASC" or "DESC"
                where:
                    and: { 'koala': 'bear' }
                    or: { 'koala': 'dangerous' }
```

PHP Class Choices
-----------------

Choice data can be supplied via PHP objects. Examples of these object classes
can be seen here:

  - [StaticChoice](../example/Choice/StaticChoice.md)
  - [TraversableChoice](../example/Choice/TraversableChoice.md)
  - [EventChoice](../example/Choice/EventChoice.md)

```yaml
    choice_traversable_choices_class:
        type: choice
        options:
            required: false
            label: Traversable choices class with "group_b" passed to the constructor
            choices: Example\TraversableChoice::group_b
            choice_label: Example\StaticChoice::choiceLabel
```

```yaml
    choice_static_choices_class:
        type: choice
        options:
            required: false
            label: Choices from the calls to a static class::function
            choices: Example\StaticChoice::choices
            choice_label: Example\StaticChoice::choiceLabel
```

```yaml
    choice_with_attrib:
        type: choice
        options:
            label: HTML attibutes added to each choice
            choices: Example\StaticChoice::choices
            choice_value: Example\StaticChoice::choiceValue
            choice_label: Example\StaticChoice::choiceLabel
            choice_attr: Example\StaticChoice::choiceAttr
```

```yaml
    choice_group_callouts:
        type: choice
        options:
            required: false
            label: Grouping callouts
            choices: Example\StaticChoice::choices
            choice_label: Example\StaticChoice::choiceLabel
            group_by: Example\StaticChoice::groupBy
```

```yaml
    choice_group_callouts_preferred_choices:
        type: choice
        options:
            required: false
            label: Grouping callouts with preferred choices
            choices: Example\StaticChoice::choices
            choice_label: Example\StaticChoice::choiceLabel
            preferred_choices: Example\StaticChoice::preferredChoices
            multiple: false
```

```yaml
    choice_group_callouts_preferred_choices:
        type: choice
        options:
            required: false
            label: Grouping callouts with preferred choices
            choices: Example\StaticChoice::choices
            choice_label: Example\StaticChoice::choiceLabel
            preferred_choices: Example\StaticChoice::preferredChoices
            multiple: false
```

Event Choice Data
-----------------

Event based choice selectors are driven by Symfony Events. By default a
`\Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents::DATA_CHOICE_EVENT`
is dispatched, but that is customisable in the `choices:` key, e.g.:

```yaml
    event_based:
        type: choice
        label: This will come from BoltFormsEvents::DATA_CHOICE_EVENT
        options:
            choices: event # This will dispatch on BoltFormsEvents::DATA_CHOICE_EVENT
    event_based_custom:
        type: choice
        label: This will come from your own named listener
        options:
            choices: event::my.custom.event
```

In the above example the choices for the `event_based` field will be an array
gathered from `BoltFormsEvents::DATA_CHOICE_EVENT`, and `event_based_custom`
will be dispatched to listeners to the `my.custom.event` event.

Each listener will be passed in a `ChoiceEvent` event object to work
with, that has getters for field name, options, and configured choices, as well
as setters for an array of choices.

```php
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(BoltFormsEvents::DATA_CHOICE_EVENT, [$this, 'replyChoices']);
        $dispatcher->addListener('my.custom.event', [$this, 'wantChoices']);
    }

    public function replyChoices(ChoiceEvent $event)
    {
        $event->setChoices(['yes' => 'Yes of course', 'no' => 'No way!']);
    }

    public function wantChoices(ChoiceEvent $event)
    {
        $event->setChoices(['yes' => 'Sure', 'no' => 'Not in this life']);
    }
```
