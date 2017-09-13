<?php

namespace Bolt\Extension\Bolt\BoltForms\Asset;

use Bolt\Asset\File\JavaScript;

/**
 * reCaptcha JavaScript asset.
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
class ReCaptcha extends JavaScript
{
    /** @var string */
    protected $htmlLang;

    /** @var string */
    protected $renderType;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $onLoad = ($this->getRenderType() == 'invisible') ? '&render=explicit&onload=invisibleRecaptchaOnLoad' : '';
        $theme =  sprintf('<script>var RecaptchaOptions = { theme : "" };</script>', 'clean');
        $api =  sprintf('<script src="https://www.google.com/recaptcha/api.js?hl=%s%s" async defer></script>', $this->getHtmlLang(), $onLoad);

        return $theme . $api;
    }

    /**
     * @return string
     */
    public function getHtmlLang()
    {
        return $this->htmlLang;
    }

    /**
     * @param string $htmlLang
     *
     * @return ReCaptcha
     */
    public function setHtmlLang($htmlLang)
    {
        $this->htmlLang = $htmlLang;

        return $this;
    }

    /**
     * @return string
     */
    public function getRenderType()
    {
        return $this->renderType;
    }

    /**
     * @param $type
     *
     * @return ReCaptcha
     */
    public function setRenderType($type)
    {
        $this->renderType = $type;

        return $this;
    }
}
