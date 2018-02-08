<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Bolt\Extension\Bolt\BoltForms\Config\Form\FieldOptionsBag;
use Bolt\Extension\Bolt\BoltForms\Exception\EmailException;
use Bolt\Storage\Entity;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Email configuration for BoltForms
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
class EmailConfig extends ParameterBag
{
    /** @var boolean */
    protected $debug;

    public function __construct(FormConfig $formConfig, Entity\Entity $formData)
    {
        parent::__construct();

        $this->set('attachFiles', $formConfig->getNotification()->get('attach_files', false));
        $this->set('debug', $formConfig->getNotification()->isDebug());
        $this->set('debugEmail', $formConfig->getNotification()->get('debug_address'));
        $this->set('debugSmtp', $formConfig->getNotification()->get('debug_smtp'));
        $this->setEmailConfig($formConfig, $formData);
    }

    /**
     * Debugging state.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->getBoolean('debug');
    }

    /**
     * Debugging state.
     *
     * @return boolean
     */
    public function isDebugSmtp()
    {
        return $this->getBoolean('debugSmtp');
    }

    /**
     * Get the debugging email address.
     *
     * @throws EmailException
     *
     * @return string|null
     */
    public function getDebugEmail()
    {
        $address = $this->get('debugEmail');
        if ($address === null && $this->isDebug()) {
            throw new EmailException('BoltForms debug email address can not be empty if BoltForms, or an individual form\'s debugging is enabled!');
        }

        return $address;
    }

    /**
     * Attach files.
     *
     * @return boolean
     */
    public function attachFiles()
    {
        return $this->getBoolean('attachFiles');
    }

    /**
     * Get the 'from' name.
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->get('fromName');
    }

    /**
     * Set the 'from' name.
     *
     * @param string
     */
    public function setFromName($fromName)
    {
        $this->set('fromName', $fromName);
    }

    /**
     * Get the 'from' email address.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->get('fromEmail');
    }

    /**
     * Set the 'from' email address.
     *
     * @param string
     */
    public function setFromEmail($fromEmail)
    {
        $this->set('fromEmail', $fromEmail);
    }

    /**
     * Get the 'replyto' name.
     *
     * @return string
     */
    public function getReplyToName()
    {
        return $this->get('replyToName');
    }

    /**
     * Set the 'replyto' name.
     *
     * @param string
     */
    public function setReplyToName($replyToName)
    {
        $this->set('replyToName', $replyToName);
    }

    /**
     * Get the 'replyto' email address.
     *
     * @return string
     */
    public function getReplyToEmail()
    {
        return $this->get('replyToEmail');
    }

    /**
     * Set the 'replyto' email address.
     *
     * @param string
     */
    public function setReplyToEmail($replyToEmail)
    {
        $this->set('replyToEmail', $replyToEmail);
    }

    /**
     * Get the 'To' name.
     *
     * @return string
     */
    public function getToName()
    {
        return $this->get('toName');
    }

    /**
     * Set the 'To' name.
     *
     * @param string
     */
    public function setToName($toName)
    {
        $this->set('toName', $toName);
    }

    /**
     * Get the 'To' email address.
     *
     * @return string
     */
    public function getToEmail()
    {
        return $this->get('toEmail');
    }

    /**
     * Set the 'To' email address.
     *
     * @param string
     */
    public function setToEmail($toEmail)
    {
        $this->set('toEmail', $toEmail);
    }

    /**
     * Get the 'CC' name.
     *
     * @return string
     */
    public function getCcName()
    {
        return $this->get('ccName');
    }

    /**
     * Set the 'CC' name.
     *
     * @param string
     */
    public function setCcName($ccName)
    {
        $this->set('ccName', $ccName);
    }

    /**
     * Get the 'CC' email address.
     *
     * @return string
     */
    public function getCcEmail()
    {
        return $this->get('ccEmail');
    }

    /**
     * Set the 'CC' email address.
     *
     * @param string
     */
    public function setCcEmail($ccEmail)
    {
        $this->set('ccEmail', $ccEmail);
    }

    /**
     * Get the 'bCC' name.
     *
     * @return string
     */
    public function getBccName()
    {
        return $this->get('bccName');
    }

    /**
     * Set the 'bCC' name.
     *
     * @param string
     */
    public function setBccName($bccName)
    {
        $this->set('bccName', $bccName);
    }

    /**
     * Get the 'bCC' email address.
     *
     * @return string
     */
    public function getBccEmail()
    {
        return $this->get('bccEmail');
    }

    /**
     * Set the 'bCC' email address.
     *
     * @param string
     */
    public function setBccEmail($bccEmail)
    {
        $this->set('bccEmail', $bccEmail);
    }

    /**
     * Set resolved email configuration settings.
     *
     * @param FormConfig    $formConfig
     * @param Entity\Entity $formData
     */
    private function setEmailConfig(FormConfig $formConfig, Entity\Entity $formData)
    {
        $notifyConfig = $formConfig->getNotification();
        $hashMap = [
            'fromName'     => 'from_name',
            'fromEmail'    => 'from_email',
            'replyToName'  => 'replyto_name',
            'replyToEmail' => 'replyto_email',
            'toName'       => 'to_name',
            'toEmail'      => 'to_email',
            'ccName'       => 'cc_name',
            'ccEmail'      => 'cc_email',
            'bccName'      => 'bcc_name',
            'bccEmail'     => 'bcc_email',
        ];

        foreach ($hashMap as $internalName => $keyName) {
            $key = $notifyConfig->get($keyName);

            // Allow for both `replyto_email: email` as well as `from_name: [name, lastname]`
            if (is_array($key)) {
                $value = [];
                foreach($key as $keyPart) {
                    if ($this->getConfigValue($formData, $keyPart) != $keyPart) {
                        $value[] = $this->getConfigValue($formData, $keyPart);
                    }
                }
                $value = implode(" ", $value);
            } else {
                $value = $this->getConfigValue($formData, $key);
            }

            if ($value === null) {
                $this->set($internalName, $key);
            } else {
                $this->set($internalName, $value);
            }
        }
    }

    /**
     * Get a resolved field value.
     *
     * If the form notification configuration wants a value to be returned from
     * a submitted field we use this, otherwise the configured parameter.
     *
     * @param Entity\Entity $formData
     * @param string        $value
     *
     * @return string
     */
    private function getConfigValue(Entity\Entity $formData, $value)
    {
        if ($value instanceof FieldOptionsBag) {
            $parts = [];
            foreach ($value->all() as $val) {
                $parts[$val] = $this->getConfigValue($formData, $val);
            }

            return implode(' ', $parts);
        }
        if (is_string($value) && isset($formData[$value])) {
            return $formData->get($value);
        }

        return $value;
    }
}
