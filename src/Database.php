<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Database functions for BoltForms
 *
 * Copyright (C) 2014 Gawain Lynch
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
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    public function __construct(Application $app)
    {
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     * Write out form data to a specified database table
     *
     * @param string $tablename
     * @param array  $data
     *
     * @return boolean
     */
    public function writeToTable($tablename, array $data)
    {
        $savedata = array();

        // Don't try to write to a non-existant table
        $sm = $this->app['db']->getSchemaManager();
        if (! $sm->tablesExist(array($tablename))) {
            // log failed attempt
            $this->app['logger.system']->info("Failed attempt to save Bolt Forms info: missing database table {$tablename}", array('event' => 'extensions'));
            return false;
        }

        // Build a new array with only keys that match the database table
        $columns = $sm->listTableColumns($tablename);
        foreach ($columns as $column) {
            $colname = $column->getName();
            // only attempt to insert fields with existing data
            // this way you can have fields in your table that are not in the form
            // eg. an auto increment id field of a field to track status of a submission
            if (array_key_exists($colname, $data)) {
                $savedata[$colname] = $data[$colname];
            }
        }

        $savedata = $this->getData($savedata);

        $this->app['db']->insert($tablename, $savedata);
    }

    /**
     * Write out form data to a specified contenttype table
     *
     * @param string $contenttype
     * @param array  $data
     */
    public function writeToContentype($contenttype, array $data)
    {
        // Get an empty record for out contenttype
        $record = $this->app['storage']->getEmptyContent($contenttype);

        $data = $this->getData($data);

        // Set a published date
        if (empty($data['datepublish'])) {
            $data['datepublish'] = date('Y-m-d H:i:s');
        }

        // Store the data array into the record
        $record->setValues($data);

        $this->app['storage']->saveContent($record);
    }

    /**
     * Get the data.
     *
     * @param array $data
     *
     * @return data
     */
    protected function getData(array $data)
    {
        foreach ($data as $key => $value) {
            // Don't try to insert NULLs
            if ($value === null) {
                $data[$key] = '';
            }

            // JSON encode arrays
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }

            // https://github.com/bolt/bolt/issues/3459
            // https://github.com/GawainLynch/bolt-extension-boltforms/issues/15
            if ($value instanceof \DateTime) {
                $data[$key] = $value->format('c');
            }

            // Handle file storage preparation here
            if ($value instanceof FileUpload && $value->getFile()->isValid()) {
                $data[$key] = $value->getTargetFileName();
            }
        }

        return $data;
    }
}
