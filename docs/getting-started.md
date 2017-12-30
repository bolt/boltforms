Getting Started
===============

When you install BoltForms, the default configuration comes with a form called
`contact`.

You can safely remove (or comment out) this form if you don't need it. But it
is a handy first place to look, this file will be located at
`app/config/extensions/boltforms.bolt.yml`.

**NOTE:** When first installed, BoltForms defaults to turning debugging on in
the configuration. This should be turned off when deployed in production.

When debugging is on, all outbound emails are sent to the configured debug
email address.

Your First Form
---------------

For a first form, let us use a simple "anonymous-comments" form as an example.

### Configuration

Under the `notification:` key we need to set `enabled: true` and then a set of
email addresses, the minimum list shown below.

Then we define two fields; a `comment` field that allows text entry, and
the `submit` button.

```yaml
anonymous-comments:
    notification:
        enabled: true
        subject: Someone said something into the ether…
        from_name: BoltAnonymous
        from_email: anon@example.com
        to_name: Kenny Koala
        to_email: kenny@example.com
    fields:
        comment:
            type: text
            options:
                label: Leave an anonymous comment
        submit:
            type: submit
```

### Template

To render our anonymous comments form, we would just place it in the required
location in the desired Twig template, e.g.:

```twig
{{ boltforms('anonymous-comments') }}
```
