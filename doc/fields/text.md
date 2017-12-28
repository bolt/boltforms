Text Based Fields with Examples
===============================

**NOTE:** See [the Symfony Forms documentation][forms] for an always up-to-date
list of field types and their options.

Standard Text Field
-------------------

```yaml
    mytextfield:
        type: text
        options:
            required: true
            label: My Text Field
            attr:
                placeholder: Enter some text…
                value: A Default Value
```

Textarea Field
--------------

```yaml
    mytextareafield:
        type: textarea
        options:
            label: My Textarea Field
            attr:
                placeholder: Enter some text…
```

Email Field
-----------

This renders an `<input type="email">` form element.

```yaml
    myemailfield:
        type: email
        options:
            label: My Email Field
            attr:
                placeholder: you@example.com
```

Integer Field
-------------

This renders an `<input type="number">` form element.

```yaml
    mynumber:
        type: integer
        options:
            label: My Integer Field
            attr:
                min: 0
                max: 1000
```

Money Field
-----------

This renders a number form element with a currency symbol before the input. Any
ISO 3-Letter Currency code is supported.

```yaml
    amount:
        type: money
        options:
            label: How much does it cost?
            currency: EUR
```

Password Field
--------------

This renders am HTML password input.

```yaml
    password:
        type: password
        options:
            label: Enter a secret word
```

Percent Field
-------------

This renders an HTML number input and converts the inputted percentage to a
decimal value. It also adds a percentage sign after the form input.

```yaml
    percentage:
        type: percent
        options:
            label: Percentage Increase?
```

Search Field
------------

This renders an HTML search input `<input type="search" />`.

```yaml
    query:
        type: search
        options:
            label: Search Term
            attr:
                value: Example Query
```


URL Field
---------

This renders an HTML URL input `<input type="url" />`.

```yaml
    website:
        type: url
        options:
            label: Your Website
```

Range Field
-----------

This renders an HTML URL range input `<input type="range" />`.

```yml
        amount:
            type: range
            options:
                label: Enter a number
                attr:
                    min: 10
                    max: 100
                    step: 5
```

Note that by default the range has no UI to show the actual selected value,
this is normally accomplished by listening to the `input` event on the range
field and using javascript to update another element with the value.

[forms]: http://symfony.com/doc/current/reference/forms/types/form.html