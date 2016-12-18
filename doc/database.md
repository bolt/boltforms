Using Database
==============

## ContentTypes

### Default

```yaml
    database:
        contenttype: mycontenttype  # ContentType record to create
```

### Mapped Fields

```yaml
    database:
        contenttype:
           name: mycontenttype  # ContentType record to create
           field_map:
               email: ~                # Do not try to save this field to the ContentType
               message: 'sent_message' # Form field "message" will be saved to the ContentType field "sent_message"
```

## Regular Database Table

```yaml
    database:
        table: bolt_secret_table
```
