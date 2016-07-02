<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Exception\StorageException;
use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Storage\EntityManager;
use Psr\Log\LogLevel;
use Silex\Application;

/**
 * Database functions for BoltForms
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
class Database
{
    use FeedbackHandlerTrait;

    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Write out form data to a specified database table
     *
     * @param string       $tableName
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function writeToTable($tableName, FormData $formData, FormMetaData $formMetaData)
    {
        $saveData = [];

        // Don't try to write to a non-existant table
        $sm = $this->app['db']->getSchemaManager();
        if (!$sm->tablesExist([$tableName])) {
            // log failed attempt
            $this->message(sprintf('Failed attempt to save submission: missing database table `%s`', $tableName), 'debug', LogLevel::ERROR);
        }

        // Build a new array with only keys that match the database table
        /** @var \Doctrine\DBAL\Schema\Column[] $columns */
        $columns = $sm->listTableColumns($tableName);

        foreach ($columns as $column) {
            $colName = $column->getName();
            // Only attempt to insert fields with existing data this way you can
            // have fields in your table that are not in the form eg. an auto
            // increment id field of a field to track status of a submission
            if ($formData->has($colName)) {
                $saveData[$colName] = $formData->get($colName, true);
            }

            // Add any meta values that are requested for 'database' use
            foreach ($formMetaData->keys() as $key) {
                if ($key === $colName && in_array('database', (array) $formMetaData->get($key)->getUse())) {
                    $saveData[$colName] = $formMetaData->get($key)->getValue();
                }
            }
        }

        try {
            $this->app['db']->insert($tableName, $saveData);
        } catch (\Exception $e) {
            $this->exception($e, false, sprintf('An exception occurred saving submission to database table `%s`', $tableName));
        }
    }

    /**
     * Write out form data to a specified contenttype table
     *
     * @param string       $contentType
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function writeToContenType($contentType, FormData $formData, FormMetaData $formMetaData)
    {
        /** @var EntityManager $em */
        $em = $this->app['storage'];

        try {
            $repo = $em->getRepository($contentType);
        } catch (StorageException $e) {
            $this->exception($e, false, sprintf('Invalid ContentType name `%s` specified.', $contentType));

            return;
        }

        // Get an empty record for out contenttype
        $record = $repo->getEntityBuilder()->getEntity();

        // Set a published date
        $record->setStatus('published');
        if (!$formData->has('datepublish')) {
            $record->setDatepublish(date('Y-m-d H:i:s'));
        }

        foreach ($formData->keys() as $name) {
            // Store the data array into the record
            $record->set($name, $formData->get($name, true));
        }

        // Add any meta values that are requested for 'database' use
        foreach ($formMetaData->keys() as $key) {
            if (in_array('database', (array) $formMetaData->get($key)->getUse())) {
                $record->set($key, $formMetaData->get($key)->getValue());
            }
        }

        try {
            $repo->save($record);
        } catch (\Exception $e) {
            $this->exception($e, false, sprintf('An exception occurred saving submission to ContentType table `%s`', $contentType));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        return $this->app['boltforms.feedback'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->app['logger.system'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getMailer()
    {
        return $this->app['mailer'];
    }
}
