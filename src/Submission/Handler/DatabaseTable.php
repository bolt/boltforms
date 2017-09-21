<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\MetaData;
use Bolt\Extension\Bolt\BoltForms\Exception\InternalProcessorException;
use Bolt\Storage\Entity;

/**
 * Database functions for BoltForms
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
class DatabaseTable extends AbstractHandler
{
    /**
     * Write out form data to a specified database table row.
     *
     * @param string        $tableName
     * @param Entity\Entity $formData
     * @param MetaData      $formMetaData
     *
     * @throws InternalProcessorException
     */
    public function handle($tableName, Entity\Entity $formData, MetaData $formMetaData)
    {
        $saveData = [];
        $connection = $this->getEntityManager()->getConnection();

        // Don't try to write to a non-existant table
        $sm = $connection->getSchemaManager();
        if (!$sm->tablesExist([$tableName])) {
            // log failed attempt
            throw new InternalProcessorException(sprintf('Failed attempt to save submission: missing database table `%s`', $tableName), 0, null, false);
        }

        // Build a new array with only keys that match the database table
        /** @var \Doctrine\DBAL\Schema\Column[] $columns */
        $columns = $sm->listTableColumns($tableName);

        foreach ($columns as $column) {
            $colName = $column->getName();
            // Only attempt to insert fields with existing data this way you can
            // have fields in your table that are not in the form eg. an auto
            // increment id field of a field to track status of a submission
            if (isset($formData[$colName])) {
                $data = $formData->get($colName, null, true);
                if (is_array($data)) {
                    $data = implode(', ', $data);
                }
                $saveData[$colName] = $data;
            }

            // Add any meta values that are requested for 'database' use
            foreach ($formMetaData->getUsedMeta('database') as $key => $value) {
                if ($key === $colName) {
                    $saveData[$colName] = $formMetaData->get($key)->getValue();
                }
            }
        }

        try {
            $connection->insert($tableName, $saveData);
        } catch (\Exception $e) {
            throw new InternalProcessorException(sprintf('An exception occurred saving submission to database table `%s`', $tableName), $e->getCode(), $e, false);
        }
    }
}
