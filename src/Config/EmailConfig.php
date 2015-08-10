<?php
namespace Bolt\Extension\Bolt\BoltForms\Config;

use Bolt\Extension\Bolt\BoltForms\Exception\EmailException;

/**
 * Email configuration for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class EmailConfig implements \ArrayAccess
{
    /** @var array */
    protected $globalConfig;
    /** @var array */
    protected $formConfig;
    /** @var array */
    protected $formData;
    /** @var boolean */
    protected $debug;
    /** @var string */
    protected $debugAddress;
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

    public function __construct(array $globalConfig, array $formConfig, array $formData)
    {
        $this->globalConfig = $globalConfig;
        $this->formConfig = $formConfig;
        $this->formData = $formData;

        $this->setDebugState();
        $this->setEmailConfig();
    }

    /**
     * Set the debugging state.
     *
     * Global debug enabled
     *   - Messages should always go to the global debug address only
     *   - Takes preference over form specific settings
     *   - To address also takes precidence
     *
     * Global debug disabled
     *   - Form specific debug settings are applied
     *
     * Form debug enabled
     *   - For debug address takes priority if set
     *
     * @throws EmailException
     */
    protected function setDebugState()
    {
        if ($this->globalConfig['debug']['enabled']) {
            $this->debug = true;

            if (empty($this->globalConfig['debug']['address'])) {
                throw new EmailException('[BoltForms] Debug email address can not be empty if debugging enabled!');
            } else {
                $this->debugAddress = $this->globalConfig['debug']['address'];
            }
        } else {
            $this->debug = isset($this->formConfig['debug']) && $this->formConfig['debug'];

            if (isset($this->formConfig['debug_address'])) {
                $this->debugAddress = $this->formConfig['debug_address'];
            } else {
                $this->debugAddress = $this->globalConfig['debug']['address'];
            }
        }
    }

    /**
     * Debugging state.
     *
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Get the debugging email address.
     *
     * @return string
     */
    public function getDebugAddress()
    {
        return $this->debugAddress;
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
     * Get the 'from' email address.
     *
     * @return string
     */
    public function getFromEmail()
    {
        return $this->fromEmail;
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
     * Get the 'replyto' email address.
     *
     * @return string
     */
    public function getReplyToEmail()
    {
        return $this->replyToEmail;
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
     * Get the 'To' email address.
     *
     * @return string
     */
    public function getToEmail()
    {
        return $this->toEmail;
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
     * Get the 'CC' email address.
     *
     * @return string
     */
    public function getCcEmail()
    {
        return $this->ccEmail;
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
     * Get the 'bCC' email address.
     *
     * @return string
     */
    public function getBccEmail()
    {
        return $this->bccEmail;
    }

    /**
     * Get resolved email configuration settings.
     */
    private function setEmailConfig()
    {
        $notify = $this->formConfig['notification'];

        $hashMap = array(
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
        );

        foreach ($hashMap as $property => $key) {
            $this->{$property} = $this->getConfigValue($notify[$key]);
        }

    }

    /**
     * Get a resolved field value.
     *
     * If the form notification configuration wants a value to be returned from
     * a submitted field we use this, otherwise the configured parameter.
     *
     * @param string $value
     */
    private function getConfigValue($value)
    {
        if (isset($this->formData[$value])) {
            return $this->formData[$value];
        }

        return $value;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetExists($offset)
    {
    }

    public function offsetUnset($offset)
    {
    }

    public function offsetGet($offset)
    {
        $offset = $this->toSnakeCase($offset);

        return isset($this->{$offset}) ? $this->{$offset} : null;
    }

    /**
     * Convert a CamelCase string to snake_case.
     *
     * @param string $input
     *
     * @return string
     */
    private function toSnakeCase($input)
    {
        $matches = array();
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }
}
