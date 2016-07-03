Bolt Forms
==========

Bolt Forms is an interface to Symfony Forms for Bolt.  It provides a Twig 
template function and exposes a simplified API for extending as you need.

Set up
------

If email notifications are to be sent, you should configure the `mailoptions` 
setting in your Bolt `app/config/config.yml` file.

**Note:**
It is recommended to set `spool: false` in `mailoptions`.

Bolt uses the Swiftmailer library to send email notifications, which can be 
configured to spool email (`spool: true`) which causes Swiftmailer to delay 
sending emails until a `finish()` middleware event in the Silex service provider.

As such, Swiftmailer will return a true response to the caller, BoltForms in 
this case, when the message is queued, and that's the last we know of it.

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


