Bolt Forms
==========

Bolt Forms is an interface to Symfony Forms for Bolt.  It provides a Twig 
template function and exposes a simplified API for extending as you need.

Set up
------

If email notifications are to be sent, you should configure the `mailoptions` 
setting in your Bolt `app/config/config.yml` file.

**Note:**
BoltForms uses the Swiftmailer library to send email notifications, based on
the `mailoptions:` setting in your Bolt `app/config/config.yml` file.

When a form is in debug mode, BoltForms will process the email queue
immediately upon submission of that form. The may cause a flood of debug 
messages if the queue is large. 

Note that the queues can be managed see [Email Queues](doc/email.md#email-queues) for more.


Use
---

```twig
    {{ boltforms('formname') }}
```

Documentation
--------------

For the full documentation see [GitHub](https://github.com/bolt/boltforms/tree/master/doc)

Upgrading
---------

If you are upgrading from a relase prior to 4.0, there are a few settings which
may require attention, or modification.

### Twig Variables

The following Twig feedback variables are deprecated:
  * `error`
  * `message`

They are replaced by `messages` (plural) that contains a keyed array of 
`info`, `error`, and `debug` message arrays.

### Templates


