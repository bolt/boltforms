<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * General configuration.
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
class Config extends ParameterBag
{
    /** @var ParameterBag */
    protected $baseForms;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct();
        $nonForms = ['csrf', 'debug', 'fieldmap', 'recaptcha', 'templates', 'uploads'];
        $this->baseForms = new ParameterBag();

        foreach ($parameters as $key => $value) {
            if ($value instanceof FieldMap\Email) {
                $this->set($key, $value);
            } elseif (is_array($value)) {
                if (in_array($key, $nonForms)) {
                    $this->set($key, new ParameterBag($value));
                } else {
                    $this->baseForms->set($key, new ParameterBag($value));
                }
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * @return boolean
     */
    public function isCsrf()
    {
        return $this->get('csrf');
    }

    /**
     * @return ParameterBag
     */
    public function getReCaptcha()
    {
        return $this->get('recaptcha');
    }

    /**
     * @return ParameterBag
     */
    public function getTemplates()
    {
        return $this->get('templates');
    }

    /**
     * @return ParameterBag
     */
    public function getDebug()
    {
        return $this->get('debug');
    }

    /**
     * @return ParameterBag
     */
    public function getUploads()
    {
        return $this->get('uploads');
    }

    /**
     * @return FieldMap\Email[]
     */
    public function getFieldMap()
    {
        return $this->get('fieldMap');
    }

    /**
     * @return ParameterBag
     */
    public function getBaseForms()
    {
        return $this->baseForms;
    }
}
