<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Silex\Application;

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
    /**
     * @var Silex\Application
     */
    private $app;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $key;

    /**
     * @var array
     */
    private $choices;

    /**
     * @param Silex\Application $app
     * @param string            $name Name of the BoltForms field
     * @param string            $key  A string that takes the format of: 'contenttype::name::labelfield::valuefield'
     *                                Where:
     *                                'contenttype' - String constant that always equals 'contenttype'
     *                                'name'        - Name of the contenttype itself
     *                                'labelfield'  - Field to use for the UI displayed to the user
     *                                'valuefield'  - Field to use for the value stored
     */
    public function __construct(Application $app, $name, $key)
    {
        $this->app  = $app;
        $this->name = $name;
        $this->key  = $key;
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
        if (! $this->choices) {
            $this->choices = $this->getChoicesFromContenttypeRecords($this->key);
        }

        return $this->choices;
    }

    /**
     * Get choice values from Contenttype records
     *
     * @param string $str
     *
     * @return array
     */
    private function getChoicesFromContenttypeRecords($key)
    {
        $params = explode('::', $key);

        if ($params === false || count($params) !== 4) {
            throw new \UnexpectedValueException("The configured Contenttype choice field '$this->name' has an invalid key string: '$key'");
        }

        /** @var $records Bolt\Content[] */
        $records = $this->app['storage']->getContent($params[1]);
        $choices = array();

        foreach ($records as $record) {
            $choices[$record->get($params[3])] = $record->get($params[2]);
        }

        return $choices;
    }
}
