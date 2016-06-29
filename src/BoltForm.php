<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Symfony\Component\Form\Form;

/**
 * Class to hold all BoltForm data.
 *
 * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
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
 */
class BoltForm
{
    /** @var Form */
    protected $form;
    /** @var FormConfig */
    private $formConfig;
    /** @var FormMetaData */
    protected $meta;

    /**
     * Constructor.
     *
     * @param FormConfig   $formConfig
     * @param Form         $form
     * @param FormMetaData $meta
     */
    public function __construct(Form $form = null, FormConfig $formConfig = null, FormMetaData $meta = null)
    {
        $this->form = $form;
        $this->formConfig = $formConfig;
        $this->meta = $meta;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     *
     * @return BoltForm
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return FormMetaData
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param FormMetaData|array $meta
     *
     * @return BoltForm
     */
    public function setMeta($meta)
    {
        if ($this->form === null) {
            throw new Exception\UnknownFormException('Form not created');
        }
        if ($meta instanceof FormMetaData) {
            $meta = $meta->all();
        }
        $this->meta->replace((array) $meta);

        return $this;
    }

    /**
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    /**
     * @param FormConfig $formConfig
     *
     * @return BoltForm
     */
    public function setFormConfig($formConfig)
    {
        $this->formConfig = $formConfig;

        return $this;
    }
}
