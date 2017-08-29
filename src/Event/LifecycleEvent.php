<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\MetaData;
use Bolt\Storage\Entity\Entity;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Button;

/**
 * BoltForms submission lifecycle event.
 *
 * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License or GNU Lesser
 * General Public License as published by the Free Software Foundation,
 * either version 3 of the Licenses, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Gawain Lynch <gawain.lynch@gmail.com>
 * @copyright Copyright (c) 2014-2016, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License 3.0
 */
class LifecycleEvent extends Event
{
    /** @var FormConfig $formConfig */
    protected $formConfig;
    /** @var Entity $formData */
    protected $formData;
    /** @var MetaData */
    private $formMetaData;
    /** @var Button */
    protected $clickedButton;

    /**
     * Constructor.
     *
     * @param FormConfig $formConfig
     * @param Entity     $formData
     * @param MetaData   $formMetaData
     * @param Button     $clickedButton
     */
    public function __construct(
        FormConfig $formConfig,
        Entity $formData,
        MetaData $formMetaData,
        Button $clickedButton = null
    ) {
        $this->formConfig = $formConfig;
        $this->formData = $formData;
        $this->formMetaData = $formMetaData;
        $this->clickedButton = $clickedButton;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    /**
     * @return Entity
     */
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @return MetaData
     */
    public function getFormMetaData()
    {
        return $this->formMetaData;
    }

    /**
     * @return Button
     */
    public function getClickedButton()
    {
        return $this->clickedButton;
    }
}
