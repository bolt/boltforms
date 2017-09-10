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
class ContentTypeResolver extends AbstractChoiceOptionResolver
{
    /** @var EntityManager */
    private $em;
    /** @var array */
    private $choices;

    /**
     * Constructor.
     *
     * @param string        $formName     Name of the form containing the field
     * @param string        $fieldName    Name of the field
     * @param array         $fieldOptions Options for field
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
        if ($this->choices !== null) {
            return $this->choices;
        }

        $params = isset($this->options['params'])
            ? array_merge($this->getDefaultParameters(), (array) $this->options['params'])
            : $this->getDefaultParameters()
        ;

        return $this->choices = $this->getParameterValues($params);
    }

    /**
     * Return a default set of parameter keys.
     *
     * @return array
     */
    protected function getDefaultParameters()
    {
        return [
            'contenttype' => null,
            'label'       => null,
            'value'       => null,
            'limit'       => null,
            'sort'        => null,
            'order'       => null,
            'where'       => [
                'and' => null,
                'or'  => null,
            ],
        ];
    }

    /**
     * Do a look up of values from records in the database.
     *
     * @param array $params
     *
     * @return array
     */
    protected function getParameterValues(array $params)
    {
        $choices = [];
        /** @var Repository\ContentRepository $repo */
        $repo = $this->em->getRepository($params['contenttype']);
        $query = $repo->createQueryBuilder();

        // Build the query
        $this->getQueryParameters($query, $params);

        /** @var $records Entity\Content[] */
        $records = $repo->findWith($query);

        if ($records === false) {
            return [];
        }

        foreach ($records as $record) {
            $choices[$record->get($params['label'])] = $record->get($params['value']);
        }

        return $choices;
    }

    /**
     * Determine the parameters passed to getContent() for sorting and filtering.
     *
     * @param QueryBuilder $query
     * @param array        $params
     *
     * @return QueryBuilder
     */
    protected function getQueryParameters(QueryBuilder $query, array $params)
    {
        $query->select('content.*');

        if ($params['sort'] !== null) {
            $query->orderBy($params['sort'], $params['order']);
        }

        if ($params['limit'] !== null) {
            $query->setMaxResults((int) $params['limit']);
        }

        // WHERE filters
        if ($params['where']['and'] !== null) {
            $this->getWhereAndFilters($query, $params);
        }
        if ($params['where']['or'] !== null) {
            $this->getWhereOrFilters($query, $params);
        }

        return $query;
    }

    /**
     * Set the WHERE (…) AND (…) filters.
     *
     * @param QueryBuilder $query
     * @param array        $params
     */
    protected function getWhereAndFilters(QueryBuilder $query, array $params)
    {
        foreach ($params['where']['and'] as $field => $value) {
            $parameterName = 'and_' . $field;
            $query
                ->andWhere($field . ' = :' . $parameterName)
                ->setParameter($parameterName, $value)
            ;
        }
    }

    /**
     * Set the WHERE (…) OR (…) filters.
     *
     * @param QueryBuilder $query
     * @param array        $params
     */
    protected function getWhereOrFilters(QueryBuilder $query, array $params)
    {
        foreach ($params['where']['or'] as $field => $value) {
            $parameterName = 'or_' . $field;
            $query
                ->orWhere($field . ' = :' . $parameterName)
                ->setParameter($parameterName, $value)
            ;
        }
    }
}
