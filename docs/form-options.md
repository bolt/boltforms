Setting-up Form options
=======================

Should you want to set up your Form's global options, you can specify
them by setting the `options` key to it.

This is useful for custom validation on the whole form, e.g: say you want to
make sure your end-user only fills-in **one** out of two fields, you can define a 
Callback validator like so:

```yaml
# app/config/extensions/boltforms.bolt.yml
    options:
        constraints:
            - { Callback: { callback: [ 'Bundle\App\Form\Validator\FormValidator', 'validate'] } }
```

Then create the validator class.

```php
<?php

namespace Bundle\App\Form\Validator;

use Bolt\Extension\Bolt\BoltForms\Form\Entity\Content;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class FormValidator
{
    /**
     * Validates that either "adherent_structure" or "other_structure" is filled-in but not both.
     *
     * @param Content|mixed             $object
     * @param ExecutionContextInterface $context
     */
    public function validate($object, ExecutionContextInterface $context)
    {
        if (true !== (null === $object->get('adherent_structure') xor null === $object->get('other_structure'))) {
            $context
                ->buildViolation('You must fill-in one structure only.')
                ->addViolation()
            ;
        }
    }

}
```

Any option available from `FormType field can be defined.
See [Symfony's doc](https://symfony.com/doc/current/reference/forms/types/form.html)
for more information.