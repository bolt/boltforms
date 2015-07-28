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

        foreach ($savedata as $key => $value) {
            // Don't try to insert NULLs
            if ($value === null) {
                $savedata[$key] = '';
            }

            // JSON encode arrays
            if (is_array($value)) {
                $savedata[$key] = json_encode($value);
            }

            // https://github.com/bolt/bolt/issues/3459
            // https://github.com/GawainLynch/bolt-extension-boltforms/issues/15
            if ($value instanceof \DateTime) {
                $savedata[$key] = $value->format('c');
            }

            // handle file storage preparation here
            // TODO: make this less hacky and check if it is an uploaded file, in stead of the existing property
            if (is_object($value) && ($value instanceof UploadedFile)) {
                $savedata[$key] = $this->handleUpload($value, $key, null);
            }
        }

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

        foreach ($data as $key => $value) {
            // Symfony makes empty fields NULL, PostgreSQL gets mad.
            if (is_null($value)) {
                $data[$key] = '';
            }

            // JSON encode arrays
            if (is_array($value)) {
                $data[$key] = json_encode($value);
            }

            // handle file storage preparation here
            // TODO: make this less hacky and check if it is an uploaded file, instead of the existing property
            if (is_object($value) && ($value instanceof UploadedFile)) {
                $data[$key] = $this->handleUpload($value, $key, $record);
            }
        }

        // Set a published date
        if (empty($data['datepublish'])) {
            $data['datepublish'] = date('Y-m-d H:i:s');
        }

        // Store the data array into the record
        $record->setValues($data);

        $this->app['storage']->saveContent($record);
    }

    /**
     * Save a file to the filesystem and return the correct filename
     *
     * @param UploadedFile       $filefield
     * @param string             $key
     * @param \Bolt\Content|null $record
     *
     * @return string[]|string
     */
    private function handleUpload(UploadedFile $filefield, $key, $record)
    {
        // use the default bolt file upload path
        $upload_root = $this->app['paths']['filespath'];

        // refine the upload root with an upload location from the content type
        if ($record !== null) {
            // there is a record
            $contenttype = $record->contenttype;
            if ($contenttype['fields'][$key] && $contenttype['fields'][$key]['upload']) {
                // set the new upload location
                $upload_location = '/'.$contenttype['fields'][$key]['upload'] . '/';
                // make sure that there are no double slashes if someone
                // has added them to the config somewhere
                $upload_location = str_replace('//', '/', $upload_location);
            }
        } else {
            // use the bolt default
            $upload_location = $this->app['paths']['upload'];
        }

        // create a unique filename with a simple pattern
        $original_filename = $filefield->getClientOriginalName();
        $proposed_extension = $filefield->guessExtension() ? $filefield->guessExtension() : pathinfo($original_filename, PATHINFO_EXTENSION);
        $proposed_filename = sprintf(
            "%s-upload-%s.%s",
            date('Y-m-d'),
            $this->app['randomgenerator']->generateString(12,
                'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890'),
            $proposed_extension
        );

        // the location of the file on the server
        $proposed_file_location = $upload_root . $upload_location;

        // the name of the file in bolt content types
        $proposed_bolt_filename = $upload_location . $proposed_filename;

        // move the temporary file
        $newfile = $filefield->move($proposed_file_location, $proposed_filename);

        if (is_object($newfile) && property_exists($filefield, 'originalName')) {
            if ($record !== null) {
                return array('file' => $proposed_bolt_filename);
            } else {
                // if we don't have a record
                // we need to preserialize the file field because we like to see
                // the same structure in the values even then
                return json_encode(array('file' => $proposed_bolt_filename));
            }
        } else {
            // this means something is wrong on your server
            // leave a nice note in the log
            $this->app['logger.system']->error("Boltforms failed to store a file upload. Check the form configuration and your server.", array('event' => 'extensions'));
            // and continue with an empty file
            return '';
        }
    }
}
