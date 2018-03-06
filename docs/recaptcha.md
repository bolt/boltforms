reCAPTCHA Support
=================

BoltForms has support for Google's reCAPTCHA service which adds a level of spam
protection to your forms, preventing automated bots from making submissions.

If you look inside the configuration file you'll see some settings available
that are initially commented out, they look like this:

```yml
recaptcha:
    enabled: false
    label: "Please enter the reCaptcha text to prove you're a human"
    public_key: ''
    private_key: ''
    error_message: "The CAPTCHA wasn't entered correctly. Please try again."
    theme: clean
```

Basic Setup
-----------

To use the basic version of reCAPTCHA you will need to visit:
https://www.google.com/recaptcha/admin and register your site. When asked the
question: Choose the type of reCAPTCHA, use the option labelled: reCAPTCHA V2.

When you have completed the setup form you will be given two keys, a public key
and a private key. Both of these need to be added to the above configuration
next to the appropriate setting.

Once you have done this, then set `enabled` to `true` and your forms will have
the additional reCAPTCHA fields added to them.

Invisible Setup
---------------

BoltForms also has support for the new Invisible reCAPTCHA service which works
similarly to the original service, but without the need for an additional form
field. Instead Google analyses the behaviour of your visitors and automatically
classifies them as genuine or not.

To setup BoltForms in this way, you follow the same process as above but when
signing up for a key but select the option labelled: Invisible reCAPTCHA on the
setup form.

You then need to add an additional `type` option to the configuration so your
config will look something like this:

```yml
recaptcha:
    enabled: true
    label: "Please enter the reCaptcha text to prove you're a human"
    public_key: 'abc123456789'
    private_key: 'xyz123456789'
    error_message: "The CAPTCHA wasn't entered correctly. Please try again."
    theme: clean
    type: invisible
```

Setting the type attribute to `invisible` will use the correct service, you'll
see that it is working via a small reCAPTCHA logo added to the bottom right of
your page. You can change the position of the badge by adding badge_location to
your config. You can use: bottomright, bottomleft or inline

Disabling reCAPTCHA on some forms
---------------------------------

If you want reCAPTCHA enabled globally but want to disable it on a certain form
you can do this by adding a setting to the form configuration:

For example assuming that reCAPTCHA is enabled in the global settings but you
don't want it added to your contact form, then you can setup the form like
this:

```yml
contact:
    notification:
        enabled: true
        debug: false
        subject: Your message was submitted
        from_name: name                 # Email addresses and names can be either the
        from_email: email               # name of a field below or valid text.
        replyto_email: email            #
        replyto_name: name              # NOTE: Email addresses must be valid
        to_name: My Site                #
        to_email: noreply@example.com   #
    feedback:
        success: Message submission successful
        error: There are errors in the form, please fix before trying to resubmit
    recaptcha: false
    fields:
        name:
            type: text
            options:
                required: true
                label: Name
                attr:
                    placeholder: Your name...
        email:
            type: email
            options:
                required: true
                label: Email address
                attr:
                    placeholder: Your email...
        message:
            type: textarea
            options:
                required: true
                label: Your message
                attr:
                    placeholder: Your message...
```
