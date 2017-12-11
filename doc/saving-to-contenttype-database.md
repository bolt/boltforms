Saving to a ContentType or Database
==============
You can save form entries into a contenttype or a separate database.

## ContentTypes

There are basically 2 things you need to do:

- Prepare the ContentType that the data goes into.
- Map which BoltForms field goes into which ContentType field.

### Prepare the ContentType

```yaml
    database:
        contenttype: responses  # ContentType record to create
```

### Mapped Fields

###### (boltforms.bolt.yml)

```yaml
database:
    contenttype:
        name: responses                 # ContentType record to create
            field_map:
                name: 'title'           # Form field "message" will be saved to the ContentType field "title"
                email: ~                # Do not try to save this field to the ContentType
                animal: 'animal'        # Form field "animal" will be saved to the ContentType field "animal"
                message: 'sent_message' # Form field "message" will be saved to the ContentType field "sent_message"
    fields:
        email:
            type: email
        name:
            type: text
            options:
                label: "Your name"
        animal:
            type: choice
            options:
                label: "Use your paw to select what you are"
                choices: { "I am a cat" : "I am a cat", "I am a raccoon" : "I am a raccoon", "I am a Koala" : "I am a Koala" }
                expanded: false
        message:
            type: textarea
        status: # Don't publish new record after submitting but leave that to the editor
            type: hidden
            options:
                label: false
                attr:
                    value: draft
```

###### (contenttypes.yml)

```yaml
responses:
    name: Responses
    singular_name: Response
    fields:
        title:
            type: text
            label: Name
        animal:
            type: select
            values: { "I am a cat" : "I am a cat", "I am a raccoon" : "I am a raccoon", "I am a Koala" : "I am a Koala" }
        sent_message:
            type: textarea
    default_status: draft
```


In most cases the fieldtype in `boltforms.bolt.yml` and `contenttypes.yml` can be the same, but note that you need a `select` field with `values` to save a `choice` BoltForms field with `choices`.

## Set the publication status

If you would like to change the default status of an entry from `published` to something else, you can add a hidden field to your `boltforms.bolt.yml` to set the status. For example: 


```yaml
fields:
    status:
        type: hidden
        options:
            attr:
                value: draft
```

In the above example, you can replace "draft" with whatever status you would like to assign newly inserted records.


## Regular Database Table

If you want to keep form entries apart from your site's content, you may prefer a separate database to collect form entries.

**(This part of the docs yet to be completed)**

```yaml
    database:
        table: bolt_secret_table
```
