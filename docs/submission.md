Submission & Redirection
========================

Redirect on success
-------------------

On successful submit the user can be redirected to another Bolt page, or URL.
The page for the redirect target must exist. The redirect is added to the
`feedback` key of the form, for example:

```yaml
    feedback:
        success: Form submission successful
        error: There are errors in the form, please fix before trying to resubmit
        redirect:
            target: page/another-page  # A page path, or URL
            query: [ name, email ]     # Optional keys for the GET parameters
```

**NOTE:**

  - `target:` — Either a route in the form of `contenttype/slug` or a full URL
  - `query:` — (optional) Either an indexed, or associative array
    - `[ name, email ]` would create the query string `?name=value-of-name-field&email=value-of-email-field`
    - `{ name: 'foo', email: 'bar' }` would create the query string `?name=foo&email=bar`


AJAX (beta)
-----------

Submissions can be done using AJAX request/responses. To enable this (beta)
feature, simplly set `ajax: true` under the `submission:` key in your form's
configuration, e.g.:

```
my_form:
    submission:
        ajax: true
    fields:
        # … and so on
```

**NOTE:** This feature *currently requires* jQuery, and we will request Bolt to
attempt to load it via its asset loader chain, i.e. if you're inserting a
javascript file called `jquery.*.js` (note the wildcard), then Bolt shouldn't
interfere.
