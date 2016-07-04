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


Email Queues
------------

BoltForms spools all emails to a file spool directory, and them dispatches
them after the request has been sent to the client.


### Viewing Queued Messages

Queued messages can be viewed but running the following `nut` command: `./app/nut boltforms:mailqueue show` 

Which will output a table of queued emails similar to:

```
Currently queued emails:
+---+---------------------------+----------------------------------+-------------------------+
|   | Date                      | Address                          | Subject                 |
+---+---------------------------+----------------------------------+-------------------------+
| 1 | 2016-07-04T05:00:00+10:00 | Kenny Koala <kenny@koala.com.au> | Stock Order: Gum leaves |
+---+---------------------------+----------------------------------+-------------------------+
```


### Recovering Messages

Occassionally during sending, the Swiftmailer component used by BoltForms will
encounter a severe error when processing and sending emails and the queued
message file will have the `.sending` suffix.

To re-add them to the queue for processing, you can just run `./app/nut boltforms:mailqueue recover`


### Flushing (sending) Queues

If you have queued emails due to SMTP server problems, of Bolt/BoltForms
misconfiguration and wish to retry sending them, simplly execute the following command:
 
`./app/nut boltforms:mailqueue flush`
