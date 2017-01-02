<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Storage\Entity;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event interface for BoltForms email notifications.
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
class EmailEvent extends Event
{
    /** @var EmailConfig */
    protected $emailConfig;
    /** @var FormConfig */
    protected $formConfig;
    /** @var Entity\Entity */
    protected $formData;

    public function __construct(EmailConfig $emailConfig, FormConfig $formConfig, Entity\Entity $formData)
    {
        $this->emailConfig = $emailConfig;
        $this->formConfig = $formConfig;
        $this->formData = $formData;
    }

    /**
     * Get the EmailConfig object used in the email notification.
     *
     * @return EmailConfig
     */
    public function getEmailConfig()
    {
        return $this->emailConfig;
    }

    /**
     * Get the FormConfig object used in the email notification.
     *
     * @return FormConfig
     */
    public function getFormConfig()
    {
        return $this->formConfig;
    }

    /**
     * Get the Entity object used in the email notification.
     *
     * @return Entity\Entity
     */
    public function getFormData()
    {
        return $this->formData;
    }
}
