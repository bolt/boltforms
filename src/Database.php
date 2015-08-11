<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Silex\Application;

/**
 * Database functions for BoltForms
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
 */
class Database
{
    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Write out form data to a specified database table
     *
     * @param string   $tablename
     * @param FormData $formData
     *
     * @return boolean
     */
    public function writeToTable($tablename, FormData $formData)
    {
        $saveData = array();

        // Don't try to write to a non-existant table
        $sm = $this->app['db']->getSchemaManager();
        if (!$sm->tablesExist(array($tablename))) {
            // log failed attempt
            $this->app['logger.system']->error("[Bolt Forms] Failed attempt to save submission: missing database table `$tablename`", array('event' => 'extensions'));

            return false;
        }

        // Build a new array with only keys that match the database table
        /** @var \Doctrine\DBAL\Schema\Column[] $columns */
        $columns = $sm->listTableColumns($tablename);

        foreach ($columns as $column) {
            $colname = $column->getName();
            // Only attempt to insert fields with existing data this way you can
            // have fields in your table that are not in the form eg. an auto
            // increment id field of a field to track status of a submission
            if ($formData->has($colname)) {
                $saveData[$colname] = $formData->get($colname, true);
            }
        }

        try {
            $this->app['db']->insert($tablename, $saveData);
        } catch (\Exception $e) {
            $this->app['logger.system']->critical("[Bolt Forms] An exception occurred saving submission to database table `$tablename`", array('event' => 'extensions', 'exception' => $e));
        }
    }

    /**
     * Write out form data to a specified contenttype table
     *
     * @param string   $contenttype
     * @param FormData $formData
     */
    public function writeToContentype($contenttype, FormData $formData)
    {
        // Get an empty record for out contenttype
        $record = $this->app['storage']->getEmptyContent($contenttype);

        // Set a published date
        if (! $formData->has('datepublish')) {
            $formData->set('datepublish', date('Y-m-d H:i:s'));
        }

        foreach ($formData->keys() as $name) {
            // Store the data array into the record
            $record->setValue($name, $formData->get($name, true));
        }

        try {
            $this->app['storage']->saveContent($record);
        } catch (\Exception $e) {
            $this->app['logger.system']->critical("[Bolt Forms] An exception occurred saving submission to ContentType table `$contenttype`", array('event' => 'extensions', 'exception' => $e));
        }
    }
}
