<?php
namespace Bolt\Extension\Bolt\BoltForms\Config;

/**
 * Form section configuration for BoltForms
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
 *
 * @method boolean getEnabled()
 * @method boolean getDebug()
 * @method string  getSubject()
 * @method string  getFromName()
 * @method string  getFromEmail()
 * @method string  getReplytoName()
 * @method string  getReplytoEmail()
 * @method string  getToName()
 * @method string  getToEmail()
 * @method string  getCcName()
 * @method string  getCcEmail()
 * @method string  getBccName()
 * @method string  getBccEmail()
 * @method boolean getAttachFiles()
 * @method string  getSuccess()
 * @method string  getError()
 * @method array   getRedirect()
 * @method string  getTable()
 * @method string  getContenttype()
 * @method string  getForm()
 * @method string  getSubject()
 * @method string  getEmail()
 * @method string  getSubdirectory()
 *
 * @property boolean attach_files
 * @property string  debug_address
 * @property array   redirect
 */
class FormConfigSection implements \ArrayAccess
{
    /** @var array */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __call($name, $args = array())
    {
        $name = strtolower(preg_replace('/^get/', '', $name));
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }

    public function __get($name)
    {
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }

    public function __set($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->config[$name]);
    }

    public function __unset($name)
    {
        unset($this->config[$name]);
    }

    public function offsetSet($name, $value)
    {
        $this->config[$name] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->config[$name]);
    }

    public function offsetUnset($name)
    {
        unset($this->config[$name]);
    }

    public function offsetGet($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }
}
