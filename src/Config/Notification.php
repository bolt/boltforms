<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Notification configuration object.
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
class Notification extends ParameterBag
{
    /** @var Config */
    private $rootConfig;

    /**
     * Constructor.
     *
     * @param array       $parameters
     * @param Config|null $rootConfig
     */
    public function __construct(array $parameters, Config $rootConfig = null)
    {
        $this->rootConfig = $rootConfig;
        parent::__construct($parameters);
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
     * @return Notification
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
        return  $this->rootConfig->isDebug() || $this->get('debug');
    }

    /**
     * @param boolean $debug
     *
     * @return Notification
     */
    public function setDebug($debug)
    {
        $this->set('debug', $debug);

        return $this;
    }

    /**
     * @return string
     */
    public function getDebugAddress()
    {
        return  $this->get('debug_address') ?: $this->rootConfig->getDebugAddress();
    }

    /**
     * @param string $debugAddress
     *
     * @return Notification
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
        return  $this->get('subject');
    }

    /**
     * @param string $subject
     *
     * @return Notification
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
        return  $this->get('from_name');
    }

    /**
     * @param mixed $fromName
     *
     * @return Notification
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
        return  $this->get('from_email');
    }

    /**
     * @param mixed $fromEmail
     *
     * @return Notification
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
        return  $this->get('replyto_name');
    }

    /**
     * @param mixed $replyToName
     *
     * @return Notification
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
        return  $this->get('replyto_email');
    }

    /**
     * @param mixed $replyToEmail
     *
     * @return Notification
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
        return  $this->get('to_name');
    }

    /**
     * @param mixed $toName
     *
     * @return Notification
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
        return  $this->get('to_email');
    }

    /**
     * @param mixed $toEmail
     *
     * @return Notification
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
        return  $this->get('cc_name');
    }

    /**
     * @param mixed $ccName
     *
     * @return Notification
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
        return  $this->get('cc_email');
    }

    /**
     * @param mixed $ccEmail
     *
     * @return Notification
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
        return  $this->get('bcc_name');
    }

    /**
     * @param mixed $bccName
     *
     * @return Notification
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
        return  $this->get('bcc_email');
    }

    /**
     * @param mixed $bccEmail
     *
     * @return Notification
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
        return  $this->get('attach_files');
    }

    /**
     * @param boolean $attachFiles
     *
     * @return Notification
     */
    public function setAttachFiles($attachFiles)
    {
        $this->set('attach_files', $attachFiles);

        return $this;
    }
}
