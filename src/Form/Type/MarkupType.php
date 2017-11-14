<?php

/*
 * This file adds a MArkup type field to the bolrforms extension
 */

namespace Bolt\Extension\Bolt\BoltForms\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarkupType extends AbstractType
{
  /**
   * {@inheritdoc}
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefaults(array(
      // hidden fields cannot have a required attribute
      'required' => false,
      // Pass errors to the parent
      'error_bubbling' => true,
      'compound' => false,
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getName()
  {
    return $this->getBlockPrefix();
  }

  /**
   * {@inheritdoc}
   */
  public function getBlockPrefix()
  {
    return 'markup';
  }
}
