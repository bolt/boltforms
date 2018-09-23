Choice Fields
=============

Choice fields are fields that will display as a selectfield.

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

Display As Checkboxes
------------------------

By setting the options `expanded: true` and `multiple: true` you can turn the dropdown into a group of checkboxes.

```yaml
    checkbox_group_simple:
        type: choice
        options:
            label: A simple set of checkboxes
            expanded: true
            multiple: true
            choices: { 'Kittens': 'kittens', 'Puppies': 'puppies', 'Birbs': 'birbs' }
```


Use contenttypes in choice fields
---------------------------------

You can populate your choice options with content from your website.

To use ContentType records for choice data, you need to specify a `params:` key
with at least the following sub keys:

  - `contenttype`
  - `label`
  - `value`

Other parameters are optional. For example:

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

ContentType choice value lookups can optionally be sorted (`sort:` and `order`), limited
number of records retrieved (`limit:`), or filtered based upon one or more of
the ContentType's field values (`where:`).

The `label` option allows you to select what field is used as a label.

The `value` option allows you to select what field is used as the value.

The `limit` option takes an integer that sets the maximum number of records to
be return, and in turn the maximum number of options in the select list.

The `sort` option takes a field name. Sorting by default happens in ascending
order. To sort in a descending order, specify this in the `order` option.

The `where` option takes an array of one or more associative arrays with
`field` and `value` keys. This behaves the same as `where` parameters
in Bolt's twig function `{% setcontent %}`.



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
