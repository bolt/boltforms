<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Global reCaptcha configuration.
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
class ReCaptchaOptionsBag extends ParameterBag
{
    /**
     * Constructor.
     *
     * @param array $parameter
     */
    public function __construct(array $parameter)
    {
        parent::__construct($parameter);
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->getBoolean('enabled');
    }

    /**
     * @param boolean $enabled
     *
     * @return ReCaptchaOptionsBag
     */
    public function setEnabled($enabled)
    {
        $this->set('enabled', $enabled);

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->get('label', 'Please enter the reCaptcha text to prove you\'re a human');
    }

    /**
     * @param string $label
     *
     * @return ReCaptchaOptionsBag
     */
    public function setLabel($label)
    {
        $this->set('label', $label);

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->get('public_key');
    }

    /**
     * @return string
     */
    public function getBadgeLocation()
    {
        return $this->get('badge_location');
    }

    /**
     * @param string $publicKey
     *
     * @return ReCaptchaOptionsBag
     */
    public function setPublicKey($publicKey)
    {
        $this->set('public_key', $publicKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->get('private_key');
    }

    /**
     * @param string $privateKey
     *
     * @return ReCaptchaOptionsBag
     */
    public function setPrivateKey($privateKey)
    {
        $this->set('private_key', $privateKey);

        return $this;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->get('error_message', 'The CAPTCHA wasn\'t entered correctly. Please try again.');
    }

    /**
     * @param string $errorMessage
     *
     * @return ReCaptchaOptionsBag
     */
    public function setErrorMessage($errorMessage)
    {
        $this->set('error_message', $errorMessage);

        return $this;
    }

    /**
     * @return string
     */
    public function getTheme()
    {
        return $this->get('theme', 'clean');
    }

    /**
     * @param string $theme
     *
     * @return ReCaptchaOptionsBag
     */
    public function setTheme($theme)
    {
        $this->set('theme', $theme);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->get('type', 'v2');
    }

    /**
     * @param string $type
     *
     * @return ReCaptchaOptionsBag
     */
    public function setType($type)
    {
        $this->set('type', $type);

        return $this;
    }
}
