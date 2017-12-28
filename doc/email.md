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

Uploaded Files
--------------

If your form uses file uploads, you can attach them to the email notifications
by setting the `attach_files` parameter to `true`.

```yaml
my_form:
    notification:
        attach_files: true
```

Email Queues
------------

BoltForms spools all emails to a file spool directory, and them dispatches them
after the request has been sent to the client.


### Viewing Queued Messages

Queued messages can be viewed but running the following `nut` command:
`./app/nut email:spool --show`.

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

Occasionally during sending, the Swiftmailer component used by BoltForms will
encounter a severe error when processing and sending emails and the queued
message file will have the `.sending` suffix.

To re-add them to the queue for processing, you can just run
`./app/nut email:spool --recover`.

### Flushing (sending) Queues

If you have queued emails due to SMTP server problems, or Bolt / BoltForms
misconfiguration and wish to retry sending them, simply execute the following
command:

`./app/nut email:spool --flush`

### Clearing (deleting) Queued Messages

If you have stale message objects that you want to flush, e.g. debugging or
testing, you can clear the queued messages with:

`./app/nut email:spool --clear`

**NOTE:** This is a destructive action and will delete the messages, which
generally means they have not been sent.
