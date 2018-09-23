Email Notifications
===================

You use email notifications to do something after the visitor submits the form. 
Most probable, you want it to send to an email address. Another option is, for example, [Saving it to a database](saving-to-contenttype-database.md). 

From, To, CC, bCC & ReplyTo Values
----------------------------------

You can use the following values to send email:

```yaml
my_form:
    notification:
        from_name: full_name
        from_email: email_address
        to_name: Kenny Koala
        to_email: kenny@koala.com
        cc_name: Fanny Koala
        cc_email: fanny@koala.com
        bcc_name: Bob the Builder
        bcc_email: bob@example.com
        replyto_name: full_name
        replyto_email: email_address
```

Each of these values can be either a literal string or the name of a field that you defined in your form.
For instance, by using your `full_name` field as a value for `from_name`, the email is sent on behalf of 
the name that your visitor submitted.

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
        from_name: full_name # using your field 'full_name'
        from_email: email_address # using your field 'email_address'
```

### Array of Field Names

```yaml
my_form:
    notification:
        from_name: [ first_name, last_name ]
        from_email: email_address
```

Email Uploaded Files
--------------------

If your form uses file uploads, you can attach them to the emails by setting the 
`attach_files` parameter to `true`.

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

To re-add them to the queue for processing, you can just run the following 
command in your terminal:
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
