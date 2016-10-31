<?php

namespace Bolt\Extension\Bolt\BoltForms\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Base configuration class.
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
abstract class AbstractCascadingBag extends ParameterBag
{
    /** @var Config */
    protected $rootConfig;

    /**
     * Constructor.
     *
     * @param array       $parameters
     * @param Config|null $rootConfig
     */
    public function __construct(array $parameters = [], Config $rootConfig = null)
    {
        parent::__construct($parameters);
        $this->rootConfig = $rootConfig;
    }

    /**
     * If there is a root configuration supplied, return its value as a default.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function getHierarchicalValue($key)
    {
        if ($this->rootConfig === null) {
            return $this->get($key);
        }

        return $this->get($key) ?: $this->getRootSection()->get($key);
    }

    /**
     * @return ParameterBag
     */
    abstract protected function getRootSection();
}
