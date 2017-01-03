<?php

namespace Bolt\Extension\Bolt\BoltForms\Form\Entity;

use Bolt\Storage\Entity\Content as BoltContent;

class Content extends BoltContent
{
    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        if (array_key_exists('title', $this->_fields)) {
            return $this->_fields['title'];
        }

        if (property_exists($this, 'title')) {
            return $this->title;
        }

        return null;
    }
}
