<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Bolt\Storage;

/**
 * ContentType choices for BoltForms
 *
 * Copyright (C) 2015 Gawain Lynch
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
 * @copyright Copyright (c) 2015, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class ContentType implements ChoiceInterface
{
    /** @var \Bolt\Storage */
    private $storage;
    /** @var string */
    private $name;
    /** @var array */
    private $options;
    /** @var array */
    private $choices;

    /**
     * @param Storage $storage
     * @param string  $name    Name of the BoltForms field
     * @param array   $options The 'choices' key is a string that takes
     *                         the format of: 'contenttype::name::labelfield::valuefield'
     *                         Where:
     *                         'contenttype' - String constant that always equals 'contenttype'
     *                         'name'        - Name of the contenttype itself
     *                         'labelfield'  - Field to use for the UI displayed to the user
     *                         'valuefield'  - Field to use for the value stored
     */
    public function __construct($storage, $name, array $options)
    {
        $this->storage = $storage;
        $this->name    = $name;
        $this->options = $options;
    }

    /**
     * Get the name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return choices array
     *
     * @return array
     */
    public function getChoices()
    {
        if ($this->choices === null) {
            $this->choices = $this->getChoicesFromContenttypeRecords();
        }

        return $this->choices;
    }

    /**
     * Get choice values from Contenttype records
     *
     * @return array
     */
    private function getChoicesFromContenttypeRecords()
    {
        $key = $this->options['choices'];
        $params = explode('::', $key);

        if ($params === false || count($params) !== 4) {
            throw new \UnexpectedValueException("The configured Contenttype choice field '$this->name' has an invalid key string: '$key'");
        }

        /** @var $records Bolt\Content[] */
        $records = $this->storage->getContent($params[1], $this->getQueryParameters());
        $choices = array();

        foreach ($records as $record) {
            $choices[$record->get($params[3])] = $record->get($params[2]);
        }

        return $choices;
    }

    /**
     * Determine the parameters passed to getContent() for sorting and filtering.
     *
     * @return array
     */
    private function getQueryParameters()
    {
        $parameters = array();
        // ORDER BY field
        if (isset($this->options['sort'])) {
            $parameters['order'] = $this->options['sort'];
        }
        // LIMIT count
        if (isset($this->options['limit'])) {
            $parameters['limit'] = (integer) $this->options['limit'];
        }
        // WHERE filters
        if (isset($this->options['filters'])) {
            $parameters = $this->getFilters($parameters);
        }

        return $parameters;
    }

    /**
     * Get the filters.
     *
     * @param array $parameters
     *
     * @return array[]
     */
    private function getFilters(array $parameters)
    {
        foreach ($this->options['filters'] as $filter) {
            $parameters[$filter['field']] = $filter['value'];
        }

        return $parameters;
    }
}
