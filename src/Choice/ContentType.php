<?php

namespace Bolt\Extension\Bolt\BoltForms\Choice;

use Bolt\Storage\Entity;
use Bolt\Storage\EntityManager;
use Bolt\Storage\Repository;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * ContentType choices for BoltForms
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
class ContentType extends AbstractChoiceType
{
    /** @var EntityManager */
    private $em;
    /** @var array */
    private $choices;


    /**
     * Constructor.
     *
     * Legacy:
     * Format of 'contenttype::name::labelfield::valuefield'
     *  'contenttype' - String constant that always equals 'contenttype'
     *  'name'        - Name of the contenttype itself
     *  'labelfield'  - Field to use for the UI displayed to the user
     *  'valuefield'  - Field to use for the value stored
     *
     * @param string        $fieldName    Name of the field
     * @param array         $fieldOptions Options for field
     * @param string        $formName     Name of the form containing the field
     * @param EntityManager $em
     */
    public function __construct($formName, $fieldName, array $fieldOptions, EntityManager $em)
    {
        parent::__construct($formName, $fieldName, $fieldOptions);

        $this->em = $em;
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
            $this->choices = $this->getChoicesFromContentTypeRecords();
        }

        return $this->choices;
    }

    /**
     * Get choice values from Contenttype records
     *
     * @return array
     */
    private function getChoicesFromContentTypeRecords()
    {
        $key = $this->options['choices'];
        $params = explode('::', $key);
        if ($params === false || count($params) !== 4) {
            throw new \UnexpectedValueException("The configured Contenttype choice field '$this->name' has an invalid key string: '$key'");
        }
        list($contentType, $name, $labelField, $valueField) = $params;

        $choices = [];
        /** @var Repository\ContentRepository $repo */
        $repo = $this->em->getRepository($name);
        $query = $repo->createQueryBuilder();
        /** @var $records Entity\Content[] */
        $records = $repo->findWith($this->getQueryParameters($query));

        foreach ($records as $record) {
            $choices[$record->get($valueField)] = $record->get($labelField);
        }

        return $choices;
    }

    /**
     * Determine the parameters passed to getContent() for sorting and filtering.
     *
     * @param QueryBuilder $query
     *
     * @return QueryBuilder
     */
    private function getQueryParameters(QueryBuilder $query)
    {
        $query
            ->select('content.*')
        ;

        // ORDER BY field
        if (isset($this->options['sort'])) {
            $query->orderBy($this->options['sort']);
        }
        // LIMIT count
        if (isset($this->options['limit'])) {
            $query->setMaxResults((integer) $this->options['limit']);
        }
        // WHERE filters
        if (isset($this->options['filters'])) {
            $this->getFilters($query);
        }

        return $query;
    }

    /**
     * Get the filters.
     *
     * @param QueryBuilder $query
     */
    private function getFilters(QueryBuilder $query)
    {
        foreach ($this->options['filters'] as $filter) {
            $parameters[$filter['field']] = $filter['value'];
            $query
                ->andWhere($filter['field'] . ' = :' . $filter['field'])
                ->setParameter($filter['field'], $filter['value'])
            ;
        }
    }
}
