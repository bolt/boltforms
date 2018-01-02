Events
======

  - Submission Processing Events
    - Pre & Post Processing Events
    - Processing Lifecycle Events
  - Processing Lifecycle Events
  - Email Event Listeners
  - Extending Available Events
  - Field Configured Data Events
  - Symfony Form Event Listener Proxies

Submisssion Processing Events
-----------------------------

### Pre & Post Processing Events

Listener's will be passed an event parameter that is an
`Bolt\Extension\Bolt\BoltForms\Event\ProcessorEvent`


#### BoltFormsEvents::SUBMISSION_PRE_PROCESSOR

Dispatched when the POSTed form data data is valid and freshly obtained from
the Request object.


#### BoltFormsEvents::SUBMISSION_POST_PROCESSOR

Post processing event dispatched after field, database & email processing, and
prior to feedback session and redirect handling.

The listener's event parameter will be a with data from after the end of the
field, database & email processing events.


### Processing Lifecycle Events

Listener's will be passed an event parameter that is an
`Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent`.


#### BoltFormsEvents::SUBMISSION_PROCESS_FIELDS

The internal listener for this event does the processing of fields, custom data
events and handled here.

### BoltFormsEvents::SUBMISSION_PROCESS_UPLOADS

The internal listener for this event does the processing of uploads.

#### BoltFormsEvents::SUBMISSION_PROCESS_DATABASE

The internal listener for this event attempts to save submitted forms as
database records, either to a ContentType or standard database table.


#### BoltFormsEvents::SUBMISSION_PROCESS_EMAIL

The internal listener for this event handles the disspacthing of emails from
form submission.


#### BoltFormsEvents::SUBMISSION_PROCESS_FEEDBACK

The internal listener for this event handles the saving of BoltForms feedback
notices to the user's session


#### BoltFormsEvents::SUBMISSION_PROCESS_REDIRECT

The internal listener for this event handles determination of response
redirection. Redirection only occurs if a redirect is set and the page exists.


### Email Event Listeners

BoltForms provides a `EmailEvent` that is dispatched immediately prior to
emails being sent, and during the internal
`BoltFormsEvents::SUBMISSION_PROCESS_EMAIL` listener's execution.

This event object will contain the EmailConfig, FormConfig and Entity objects.

```php
//use Bolt\Extension\Bolt\BoltForms\Event\EmailEvent;

    public function initialize()
    {
        $this->app['dispatcher']->addListener(BoltFormsEvents::PRE_EMAIL_SEND,  array($this, 'myPreEmailSend'));
    }

    public function myPreEmailSend(EmailEvent $event)
    {
        $emailConfig = $event->getEmailConfig();
        $formConfig = $event->getFormConfig();
        $formData = $event->getFormData();
    }
```

### Field Configured Data Events

Should you want to provide your own extension with a data event, you can
specify a custom event name and parameters in the field definition, e.g.:

```yaml
    my_custom_field:
        type: hidden
        options:
            label: false
        event:
            name: favourite_colour
            params:
                foo: bar
```

The in your extension you can add a listener on the event name, prefixed with
`boltforms.` (notice the dot) and provide a callback function that provides
the data you want set in the field.

```php
public function initialize()
{
    $eventName = 'boltforms.favourite_colour';
    $this->app['dispatcher']->addListener($eventName,  array($this, 'myCustomDataProvider'));
}
```

In the callback function, you can access any passed in parameters with
`$event->eventParams()` and persist the new data with `$event->setData()`.

```php
public function myCustomDataProvider($event)
{
    $params = $event->eventParams();
    if (isset($params['foo']) && $params['foo'] === 'bar') {
        $colour = 'green';
    } else {
        $colour = 'blue';
    }

    $event->setData($colour);
}
```


### Symfony Form Event Listener Proxies

BoltForms exposes a number of listeners, that proxy Symfony Forms listeners:

  - `BoltFormsEvents::PRE_SUBMIT`
  - `BoltFormsEvents::SUBMIT`
  - `BoltFormsEvents::POST_SUBMIT`
  - `BoltFormsEvents::PRE_SET_DATA`
  - `BoltFormsEvents::POST_SET_DATA`

Each of these match Symfony's constants, just with the BoltForms class name/prefix.

There are also events that trigger during the data processing:

  - `BoltFormsEvents::SUBMISSION_PRE_PROCESSOR`
  - `BoltFormsEvents::SUBMISSION_POST_PROCESSOR`

Below is an example of setting a field's data to upper case on submission:

```php
<?php
namespace Bolt\Extension\You\YourExtension;

use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\SimpleExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Extension extends SimpleExtension
{
    /**
     * Define events to listen to here.
     *
     * @param EventDispatcherInterface $dispatcher
     */
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(BoltFormsEvents::PRE_SUBMIT,  array($this, 'myPostSubmit'));
    }

    public function myPostSubmit($event)
    {
        if ($event->getForm()->getName() === 'my_form') {
            // Get the data from the event
            $data = $event->getData();

            // Set some data values to upper case
            $data['my_field'] = strtoupper($data['my_field']);

            // Save the data back
            $event->setData($data);
        }
    }
}
```
