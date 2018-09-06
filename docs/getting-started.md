Getting Started
===============

When you install BoltForms, the default configuration will be installed.
this config file will be located at `app/config/extensions/boltforms.bolt.yml`.

The config comes with a form called `contact`. This is a simple 
contactform. It asks for a name, email and message of the visitor. 
After submission it is send to the specified e-mail address.

You can safely remove (or comment out) this form if you don't need it. But it
is a handy first place to start. 


Debugging
---------
**NOTE:** When first installed, BoltForms defaults to `debug : true` in
the configuration. This should be set `false` to when deployed in production.

You can set debug on two levels:
- for all forms (top of the config)
- for one separate form (in the config of that form)

The debug of a separate form overrides the global debug setting. 
When debugging is on, all outbound emails are sent to the configured debug
email address.

**NOTE:** When the debug of _BOLT_ in the bolt config is set to `false`, debug will
function the same, but will give less information on screen after sending.


Your First Form
---------------

For a first form, let us use a simplified version of the "contact" form as an example.

### Configuration

We want some fields that the visitor can fill out:
Then we define two fields; a `comment` field that allows text entry, and
the `submit` button.

We want the form to be send somewhere on submission:
Under the `notification:` key we need to set `enabled: true` and then a set of
email addresses, the minimum list shown below.

```yaml
contact:
    notification:
        enabled: true
        subject: The form on your website was submitted
        from_name: name # uses the submitted value of the name field
        from_email: email # uses the submitted value of the email field
        to_name: Kenny Koala # recipient of the notification mail
        to_email: kenny@example.com # recipient of the notification mail
    fields:
        name:
            type: text
            options: 
                constraints: [ NotBlank ]
        email:
            type: email
            options: 
                constraints: [ NotBlank, Email ]
        comment:
            type: text
            options:
                label: Leave an anonymous comment
        submit:
            type: submit
```

### Include the form in your website

To show our contact form, place the following tag in the desired Twig template where 
we want the form to show:

```twig
{{ boltforms('contact') }}
```
