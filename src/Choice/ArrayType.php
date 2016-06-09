<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Bolt\Extension\Bolt\BoltForms\Exception\FormOptionException;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * Array choices for BoltForms
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
class ArrayType implements ChoiceInterface
{
    /** @var string */
    private $name;
    /** @var array */
    protected $baseOptions;
    /** @var array */
    private $choices;

    /**
     * @param string $name        Name of the BoltForms field
     * @param array  $baseOptions Options for field
     */
    public function __construct($name, array $baseOptions)
    {
        $this->name    = $name;
        $this->choices = isset($baseOptions['choices']) ? $baseOptions['choices'] : null;
        $this->baseOptions = $baseOptions;
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
        return $this->choices;
    }

    /**
     * @throws FormOptionException
     *
     * @return ChoiceLoaderInterface|null
     */
    public function getChoiceLoader()
    {
        if (!isset($this->baseOptions['choice_loader'])) {
            return null;
        }
        if (!class_exists($this->baseOptions['choice_loader'])) {
            throw new FormOptionException(sprintf('Specified choice_loader class does not exist!', $this->baseOptions['choice_loader']));
        }

        $loader = new $this->baseOptions['choice_loader']();
        if (!$loader instanceof ChoiceLoaderInterface) {
            throw new FormOptionException(sprintf('Specified choice_loader class does not implement Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface', $this->baseOptions['choice_loader']));
        }

        return $loader;
    }

    /**
     * @return callable|PropertyPath|null
     */
    public function getChoiceName()
    {
        if (!isset($this->baseOptions['choice_name'])) {
            return null;
        }

        if (is_callable($this->baseOptions['choice_name'])) {
            return $this->baseOptions['choice_name'];
        }

        return new PropertyPath($this->baseOptions['choice_name']);
    }

    /**
     * @return callable|PropertyPath|null
     */
    public function getChoiceValue()
    {
        if (!isset($this->baseOptions['choice_value'])) {
            return null;
        }

        if (is_callable($this->baseOptions['choice_value'])) {
            return $this->baseOptions['choice_value'];
        }

        return new PropertyPath($this->baseOptions['choice_value']);
    }

    /**
     * @return bool|callable|PropertyPath|null
     */
    public function getChoiceLabel()
    {
        if (!isset($this->baseOptions['choice_label'])) {
            return null;
        }

        if (is_bool($this->baseOptions['choice_label']) || is_callable($this->baseOptions['choice_label'])) {
            return $this->baseOptions['choice_label'];
        }

        return new PropertyPath($this->baseOptions['choice_label']);
    }

    /**
     * @return array|callable|PropertyPath|null
     */
    public function getChoiceAttr()
    {
        if (!isset($this->baseOptions['choice_attr'])) {
            return null;
        }

        if (is_array($this->baseOptions['choice_attr']) || is_callable($this->baseOptions['choice_attr'])) {
            return $this->baseOptions['choice_attr'];
        }

        return new PropertyPath($this->baseOptions['choice_attr']);
    }
}
