Choice Fields
=============

Simple Choice Selection
----------------------------------

```
    choice_simple:
      type: choice
      options:
        label: A very simple choice
        choices: { 'Item One': 'item_1', 'Item Two': 'item_2' }
```
```
    group_simple:
      type: choice
      options:
        label: Grouping Simple
        choices:
          'Group Aye': { 'Item One': 'item_1', 'Item Two': 'item_2', }
          'Group Bee': { 'Item Eleven': 'item_11', 'Item Twelve': 'item_12' }
```

ContentType Record Data
------------------------------------

To use ContentType records for choice data, you need to specify a `params:` key with the following sub keys:
  * `contenttype`
  * `label`
  * `value`

Other parameters are optional.

```
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
          order: DESC            # "ASC" or "DESC"
          where:
            and: { 'koala': 'bear' }
            or: { 'koala': 'dangerous' }
```

PHP Class Choices
--------------------------

```
    choice_traversable_choices_class:
      type: choice
      options:
        required: false
        label: Traversable choices class with "group_b" passed to the constructor
        choices: Example\TraversableChoice::group_b
        choice_label: Example\StaticChoice::choiceLabel
```

```
    choice_static_choices_class:
      type: choice
      options:
        required: false
        label: Choices from the calls to a static class::function
        choices: Example\StaticChoice::choices
        choice_label: Example\StaticChoice::choiceLabel
```

```
    choice_with_attrib:
      type: choice
      options:
        label: HTML attibutes added to each choice
        choices: Example\StaticChoice::choices
        choice_value: Example\StaticChoice::choiceValue
        choice_label: Example\StaticChoice::choiceLabel
        choice_attr: Example\StaticChoice::choiceAttr
```
```
    choice_group_callouts:
      type: choice
      options:
        required: false
        label: Grouping callouts
        choices: Example\StaticChoice::choices
        choice_label: Example\StaticChoice::choiceLabel
        group_by: Example\StaticChoice::groupBy
```

```
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

```
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
--------------------------

```
    choice_event_builtin:
      type: choice
      label: This will come from BoltFormsEvents::DATA_CHOICE_EVENT
      options:
        required: false
        choices: event
        multiple: false
```

```
    choice_event_custom:
      type: choice
      label: This will come from your own named listener
      options:
        required: false
        choices: event::your.custom.wants
        multiple: false
```
