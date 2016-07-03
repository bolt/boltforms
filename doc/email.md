Email Notifications
===================


From, To, CC, bCC & ReplyTo Values
----------------------------------

Each of these values can be either a literal string, a field name.
 
In the case of the `*_name` values, an array of field names that will be 
concatenated (space delimited) can also be specified.

### String Literal

```yaml
my_form:
    notification:
        from_name: Kenny Koala
        from_email: kenny@koala.com
```

### Field Names

```yaml
my_form:
    notification:
        from_name: full_name
        from_email: email_address
```

### Array of Field Names

```yaml
my_form:
    notification:
        from_name: [ first_name, last_name ]
        from_email: email_address
```
