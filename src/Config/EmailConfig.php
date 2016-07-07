<?php
namespace Bolt\Extension\Bolt\BoltForms\Config;

use Bolt\Extension\Bolt\BoltForms\Config\Section\FormRoot;
use Bolt\Extension\Bolt\BoltForms\Exception\EmailException;
use Bolt\Extension\Bolt\BoltForms\FormData;

/**
 * Email configuration for BoltForms
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
class EmailConfig implements \ArrayAccess
{
    /** @var FormConfig */
    protected $formConfig;
    /** @var FormData */
    protected $formData;
    /** @var boolean */
    protected $attachFiles;
    /** @var boolean */
    protected $debug;
    /** @var string */
    protected $debugEmail;
    /** @var string */
    protected $toName;
    /** @var string */
    protected $toEmail;
    /** @var string */
    protected $fromName;
    /** @var string */
    protected $fromEmail;
    /** @var string */
    protected $ccName;
    /** @var string */
    protected $ccEmail;
    /** @var string */
    protected $bccName;
    /** @var string */
    protected $bccEmail;
    /** @var string */
    protected $replyToName;
    /** @var string */
    protected $replyToEmail;

    public function __construct(FormConfig $formConfig, FormData $formData)
    {
        $this->formConfig = $formConfig;
        $this->formData = $formData;
        $this->attachFiles = $formConfig->getNotification()->get('attach_files', false);

        $this->setEmailConfig();
    }

    /**
     * Debugging state.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->formConfig->getNotification()->isDebug();
    }

    /**
     * Get the debugging email address.
     *
     * @throws EmailException
     *
     * @return string
     */
    public function getDebugEmail()
    {
        $address = $this->formConfig->getNotification()->get('debug_address');
        if ($address === null) {
            throw new EmailException('[BoltForms] Debug email address can not be empty if debugging enabled!');
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
        return $this->attachFiles;
    }

    /**
     * Get the 'from' name.
     *
     * @return string
     */
    public function getFromName()
    {
        return $this->fromName;
    }

    /**
     * Set the 'from' name.
     *
     * @param string
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
    }

    /**
     * Get the 'from' email address.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
    }

    /**
     * Set the 'from' email address.
     *
     * @param string
     */
    public function setFromEmail($fromEmail)
    {
        $this->fromEmail = $fromEmail;
    }

    /**
     * Get the 'replyto' name.
     *
     * @return string
     */
    public function getReplyToName()
    {
        return $this->replyToName;
    }

    /**
     * Set the 'replyto' name.
     *
     * @param string
     */
    public function setReplyToName($replyToName)
    {
        $this->replyToName = $replyToName;
    }

    /**
     * Get the 'replyto' email address.
     *
     * @return string
     */
    public function getReplyToEmail()
    {
        return $this->replyToEmail;
    }

    /**
     * Set the 'replyto' email address.
     *
     * @param string
     */
    public function setReplyToEmail($replyToEmail)
    {
        $this->replyToEmail = $replyToEmail;
    }

    /**
     * Get the 'To' name.
     *
     * @return string
     */
    public function getToName()
    {
        return $this->toName;
    }

    /**
     * Set the 'To' name.
     *
     * @param string
     */
    public function setToName($toName)
    {
        $this->toName = $toName;
    }

    /**
     * Get the 'To' email address.
     *
     * @return string
     */
    public function getToEmail()
    {
        return $this->toEmail;
    }

    /**
     * Set the 'To' email address.
     *
     * @param string
     */
    public function setToEmail($toEmail)
    {
        $this->toEmail = $toEmail;
    }

    /**
     * Get the 'CC' name.
     *
     * @return string
     */
    public function getCcName()
    {
        return $this->ccName;
    }

    /**
     * Set the 'CC' name.
     *
     * @param string
     */
    public function setCcName($ccName)
    {
        $this->ccName = $ccName;
    }

    /**
     * Get the 'CC' email address.
     *
     * @return string
     */
    public function getCcEmail()
    {
        return $this->ccEmail;
    }

    /**
     * Set the 'CC' email address.
     *
     * @param string
     */
    public function setCcEmail($ccEmail)
    {
        $this->ccEmail = $ccEmail;
    }

    /**
     * Get the 'bCC' name.
     *
     * @return string
     */
    public function getBccName()
    {
        return $this->bccName;
    }

    /**
     * Set the 'bCC' name.
     *
     * @param string
     */
    public function setBccName($bccName)
    {
        $this->bccName = $bccName;
    }

    /**
     * Get the 'bCC' email address.
     *
     * @return string
     */
    public function getBccEmail()
    {
        return $this->bccEmail;
    }

    /**
     * Set the 'bCC' email address.
     *
     * @param string
     */
    public function setBccEmail($bccEmail)
    {
        $this->bccEmail = $bccEmail;
    }

    /**
     * Get resolved email configuration settings.
     */
    private function setEmailConfig()
    {
        $notify = $this->formConfig->getNotification();

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

        foreach ($hashMap as $property => $key) {
            $this->{$property} = $this->getConfigValue($notify->get($key));
        }
    }

    /**
     * Get a resolved field value.
     *
     * If the form notification configuration wants a value to be returned from
     * a submitted field we use this, otherwise the configured parameter.
     *
     * @param string $value
     *
     * @return string
     */
    private function getConfigValue($value)
    {
        if ($value instanceof FormRoot) {
            $parts = [];
            foreach ($value->all() as $val) {
                $parts[$val] = $this->getConfigValue($val);
            }

            return implode(' ', $parts);
        }
        if ($this->formData->has($value)) {
            return $this->formData->get($value);
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
        $offset = $this->toPsr2CamelCase($offset);
        $this->{$offset} = $value;
    }

    public function offsetExists($offset)
    {
        $offset = $this->toPsr2CamelCase($offset);

        return isset($this->{$offset});
    }

    public function offsetUnset($offset)
    {
        $offset = $this->toPsr2CamelCase($offset);
        unset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        $offset = $this->toPsr2CamelCase($offset);

        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
     * Convert a snake_case string to CamelCase PSR-2 property.
     *
     * @param string $input
     *
     * @return string
     */
    private function toPsr2CamelCase($input)
    {
        $parts = explode('_', $input);
        foreach ($parts as &$part) {
            $part = ucfirst($part);
        }

        return lcfirst(implode('', $parts));
    }
}
