<?php

namespace Bolt\Extension\Bolt\BoltForms\Config\Form;

use Bolt\Extension\Bolt\BoltForms\Config\FieldMap;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Form database configuration for BoltForms
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
class DatabaseOptionsBag extends ParameterBag
{
    /**
     * Constructor.
     *
     * @param array     $parameters
     * @param FieldsBag $fieldsConfig
     */
    public function __construct(array $parameters = [], FieldsBag $fieldsConfig)
    {
        parent::__construct($parameters);
        $this->initialise($fieldsConfig);
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->get('table');
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->get('contenttype');
    }

    /**
     * @return FieldMap\ContentType|null
     */
    public function getContentTypeFieldMap()
    {
        return $this->get('field_map');
    }

    /**
     * We may get the config value `contenttype` as a string, in which case we
     * map form field names to ContentType field names one-to-one.
     *
     * If we get an array, we check for the `field_map` associative array of
     * form field names to ContentType field names to alternatively map to..
     *
     * @param FieldsBag $fieldsConfig
     */
    private function initialise(FieldsBag $fieldsConfig)
    {
        if ($this->has('contenttype') === false) {
            return;
        }

        $contentType = $this->get('contenttype');
        if ($contentType === null) {
            // Would be something weird here
            return;
        }

        if (is_string($contentType)) {
            $name = $contentType;
            $fieldNameMap = [];
        } elseif (isset($contentType['name'])) {
            $name = $contentType['name'];
            $fieldNameMap = isset($contentType['field_map']) ? (array) $contentType['field_map'] : [];
        } else {
            return;
        }

        $fieldNames = $fieldsConfig->keys();
        $mapParams = array_combine($fieldNames, $fieldNames);
        $map = new FieldMap\ContentType($mapParams);
        foreach ($map->all() as $formFieldName => $contentTypeFieldName) {
            if (array_key_exists($formFieldName, $fieldNameMap) && $fieldNameMap[$formFieldName] === null) {
                $map->remove($formFieldName);
            } elseif (isset($fieldNameMap[$formFieldName])) {
                $map->set($formFieldName, $fieldNameMap[$formFieldName]);
            }
        }

        $this->set('field_map', $map);
        $this->set('contenttype', $name);
    }
}
