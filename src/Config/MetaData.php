<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Meta data bag.
 *
 * NOTE: Parameter values must be serialisable.
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
class MetaData extends ParameterBag
{
    /** @var string */
    protected $_metaId;

    /**
     * Constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct();
        $this->_metaId = bin2hex(random_bytes(32));

        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = new Form\MetaDataBag($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $parameters = [])
    {
        $this->parameters = [];
        foreach ($parameters as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * @return string
     */
    public function getMetaId()
    {
        return $this->_metaId;
    }

    /**
     * @param string $metaId
     */
    public function setMetaId($metaId)
    {
        $this->_metaId = $metaId;
    }

    /**
     * Return the meta data for a particular use.
     *
     * @param string $target
     *
     * @return array
     */
    public function getUsedMeta($target)
    {
        $meta = [];
        foreach ($this->keys() as $key) {
            if (in_array($target, (array) $this->get($key)->getUse())) {
                $meta[$key] = $this->get($key)->getValue();
            }
        }

        return $meta;
    }
}
