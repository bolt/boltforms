<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Section;

use Bolt\Extension\Bolt\BoltForms\Config\LegacyArrayAccessTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Form section configuration for BoltForms
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
 *
 * @method boolean  getAjax()
 * @method boolean  getEnabled()
 * @method boolean  getDebug()
 * @method string   getSubject()
 * @method string   getFromName()
 * @method string   getFromEmail()
 * @method string   getReplyToName()
 * @method string   getReplyToEmail()
 * @method string   getToName()
 * @method string   getToEmail()
 * @method string   getCcName()
 * @method string   getCcEmail()
 * @method string   getBccName()
 * @method string   getBccEmail()
 * @method boolean  getAttachFiles()
 * @method string   getSuccess()
 * @method string   getError()
 * @method string   getQuery()
 * @method string   getChoices()
 * @method string   getTable()
 * @method string   getTarget()
 * @method string   getType()
 * @method string   getContentType()
 * @method string   getForm()
 * @method string   getEmail()
 * @method string   getSubdirectory()
 * @method FormOptionBag getOptions()
 * @method FormOptionBag getRedirect()
 * @method FormOptionBag getSubmission()
 *
 * @property boolean attach_files
 * @property string  debug_address
 */
class FormOptionBag extends ParameterBag implements \ArrayAccess
{
    /** @deprecated */
    use LegacyArrayAccessTrait;

    public function __construct(array $parameters)
    {
        parent::__construct();
        foreach ($parameters as $key => $value) {
            if (is_array($value)) {
                $value = new self($value);
            }
            $this->parameters[$key] = $value;
        }
    }

    public function toArray()
    {
        $config = [];
        foreach ($this->parameters as $key => $value) {
            if ($value instanceof self) {
                $config[$key] = $value->toArray();
            } else {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    public function __call($name, $args = [])
    {
        $name = strtolower(preg_replace('/^get/', '', $name));
        if (isset($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return null;
    }

    /**
     * @deprecated For legacy use. To be removed in 4.0
     *
     * @internal
     */
    protected function getParameters()
    {
        return $this->parameters;
    }
}
