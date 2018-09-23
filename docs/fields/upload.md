File Uploads
============

You can make the visitor upload a file through the form, using the `type: file` fieldtype.

First, a warning about security:

Security
--------

Handling file uploads is a very common way used to compromise (hack)
a server. BoltForms does a few things to help increase slightly the security of handling
file uploads.

### Store the files outside of the webroot

The following are the "global" options that apply to all form uploads:

```yaml
    uploads:
        enabled: true                           # The global on/off switch for upload handling
        base_directory: /data/customer-uploads/ # Outside web root, absolute path and writable by the web server's user
        filename_handling: prefix               # Can be either "prefix", "suffix", or "keep"
        management_controller: true             # Enable a controller to handle browsing and downloading of uploaded files
```

The directory that you specify for `base_directory` should **NOT** be a route
accessible to the outside world and have to be an **absolute path**. BoltForms provides a special route should you
wish to make the files browsable after upload. This route can be enabled as a
global setting via the `management_controller` option.

### Renaming the uploaded files

Secondly, is the `filename_handling` parameter is an important consideration
for your level of required site security. The reason this setting is important
is, if an attacker knows the uploaded file name then this can make their job a
lot easier. BoltForms provides three uploaded file naming options, `prefix`,
`suffix` and `keep`.

For example, when uploading the file `kitten.jpg` the settings would provide
something similar to the following table:

| Setting   | Resulting file name     |
|-----------|-------------------------|
| `prefix`  | kitten.Ze1d352rrI3p.jpg |
| `suffix`  | kitten.jpg.Ze1d352rrI3p |
| `keep`    | kitten.jpg              |

We recommend `suffix`, as this is the most secure. Alternatively `prefix` will
aid in file browsing. However, `keep` should always be used with caution!

How to use upload
-----------------

First, set the global switch for uploads to true in the Boltforms.bolt.yml:

```yaml
    uploads:
        enabled: true
```

Each form has individual options for uploads, such as whether to attach the
uploaded file in the notification message, or whether to place the uploaded file
in a separate subdirectory or the given global upload target.

A very basic, and cut-down, example of a form with an upload field type is given
here:

```yaml
file_upload_form:
    notification:
        enabled: true
        attach_files: true             # Optionally send the file as an email attachment
    uploads:
        subdirectory: file_upload_dir  # Optional subdirectory
    fields:
        upload:
            type: file
            options:
                required: false
                label: Picture of your pet that you want us to add to our site

```

Post-Upload Browsing
--------------------

When `management_controller` is enabled in the config, a file in the `base_directory`
location is accessible via `http://your-site.com/boltforms/download?file=filename.ext`.

These files can be listed via the Twig function `boltforms_uploads()`, e.g.

```twig
    {{ boltforms_uploads() }}
```

This can be limited to a form's (optionally defined) subdirectory by passing the
form name into `boltforms_uploads()`, e.g.

```twig
    {{ boltforms_uploads('file_upload_form') }}
```
