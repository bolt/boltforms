<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

use Bolt\Extension\Bolt\BoltForms\Config\AbstractCascadingBag;
use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Notification configuration object.
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
class NotificationOptionsBag extends AbstractCascadingBag
{
    /**
     * Constructor.
     *
     * @param array  $parameters
     * @param Config $rootConfig
     */
    public function __construct(array $parameters = [], Config $rootConfig)
    {
        parent::__construct($parameters, $rootConfig);
        if ($rootConfig->isDebug()) {
            $this->set('debug', true);
            $this->set('debug_address', $rootConfig->getDebugAddress());
        }
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->get('enabled');
    }

    /**
     * @param boolean $enabled
     *
     * @return NotificationOptionsBag
     */
    public function setEnabled($enabled)
    {
        $this->set('enabled', $enabled);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return  $this->getHierarchicalValue('debug');
    }

    /**
     * @param boolean $debug
     *
     * @return NotificationOptionsBag
     */
    public function setDebug($debug)
    {
        $this->set('debug', $debug);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDebugSmtp()
    {
        return  $this->getHierarchicalValue('debug_smtp');
    }

    /**
     * @param boolean $debug
     *
     * @return NotificationOptionsBag
     */
    public function setDebugSmtp($debug)
    {
        $this->set('debug_smtp', $debug);

        return $this;
    }

    /**
     * @return string
     */
    public function getDebugAddress()
    {
        return  $this->getHierarchicalValue('debug_address');
    }

    /**
     * @param string $debugAddress
     *
     * @return NotificationOptionsBag
     */
    public function setDebugAddress($debugAddress)
    {
        $this->set('debug_address', $debugAddress);

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return  $this->getHierarchicalValue('subject');
    }

    /**
     * @param string $subject
     *
     * @return NotificationOptionsBag
     */
    public function setSubject($subject)
    {
        $this->set('subject', $subject);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromName()
    {
        return  $this->getHierarchicalValue('from_name');
    }

    /**
     * @param mixed $fromName
     *
     * @return NotificationOptionsBag
     */
    public function setFromName($fromName)
    {
        $this->set('from_name', $fromName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFromEmail()
    {
        return  $this->getHierarchicalValue('from_email');
    }

    /**
     * @param mixed $fromEmail
     *
     * @return NotificationOptionsBag
     */
    public function setFromEmail($fromEmail)
    {
        $this->set('from_email', $fromEmail);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToName()
    {
        return  $this->getHierarchicalValue('replyto_name');
    }

    /**
     * @param mixed $replyToName
     *
     * @return NotificationOptionsBag
     */
    public function setReplyToName($replyToName)
    {
        $this->set('replyto_name', $replyToName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getReplyToEmail()
    {
        return  $this->getHierarchicalValue('replyto_email');
    }

    /**
     * @param mixed $replyToEmail
     *
     * @return NotificationOptionsBag
     */
    public function setReplyToEmail($replyToEmail)
    {
        $this->set('replyto_email', $replyToEmail);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToName()
    {
        return  $this->getHierarchicalValue('to_name');
    }

    /**
     * @param mixed $toName
     *
     * @return NotificationOptionsBag
     */
    public function setToName($toName)
    {
        $this->set('to_name', $toName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getToEmail()
    {
        return  $this->getHierarchicalValue('to_email');
    }

    /**
     * @param mixed $toEmail
     *
     * @return NotificationOptionsBag
     */
    public function setToEmail($toEmail)
    {
        $this->set('to_email', $toEmail);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcName()
    {
        return  $this->getHierarchicalValue('cc_name');
    }

    /**
     * @param mixed $ccName
     *
     * @return NotificationOptionsBag
     */
    public function setCcName($ccName)
    {
        $this->set('cc_name', $ccName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCcEmail()
    {
        return  $this->getHierarchicalValue('cc_email');
    }

    /**
     * @param mixed $ccEmail
     *
     * @return NotificationOptionsBag
     */
    public function setCcEmail($ccEmail)
    {
        $this->set('cc_email', $ccEmail);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBccName()
    {
        return  $this->getHierarchicalValue('bcc_name');
    }

    /**
     * @param mixed $bccName
     *
     * @return NotificationOptionsBag
     */
    public function setBccName($bccName)
    {
        $this->set('bcc_name', $bccName);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBccEmail()
    {
        return  $this->getHierarchicalValue('bcc_email');
    }

    /**
     * @param mixed $bccEmail
     *
     * @return NotificationOptionsBag
     */
    public function setBccEmail($bccEmail)
    {
        $this->set('bcc_email', $bccEmail);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAttachFiles()
    {
        return  $this->getHierarchicalValue('attach_files');
    }

    /**
     * @param boolean $attachFiles
     *
     * @return NotificationOptionsBag
     */
    public function setAttachFiles($attachFiles)
    {
        $this->set('attach_files', $attachFiles);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRootSection()
    {
        return new ParameterBag();
    }
}
