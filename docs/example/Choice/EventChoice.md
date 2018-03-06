EventChoice
===========

```php
<?php

namespace Example;

use Bolt\Extension\Bolt\BoltForms\Event\ChoiceEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\SimpleExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class to demonstrate event listeners for option choice management.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class EventChoice extends SimpleExtension
{
    protected function subscribe(EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(BoltFormsEvents::DATA_CHOICE_EVENT, [$this, 'onDataChoiceEvent']);
    }

    public function onDataChoiceEvent(ChoiceEvent $event)
    {
        if ($event->getFormName() === 'contact' && $event->getFieldName() === 'department') {
            $choices = [
                '' => null,
                'Sales'     => 'dept_sales',
                'Marketing' => 'dept_markt',
                'support'   => 'dept_support',
            ];

            $event->setChoices($choices);
        }
    }
}
```
