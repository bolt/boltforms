<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\Submission\FeedbackTrait;
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
class DatabaseTable
{
    use FeedbackTrait;

    /** @var Application */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Write out form data to a specified database table row.
     *
     * @param string       $tableName
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function save($tableName, FormData $formData, FormMetaData $formMetaData)
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
     * @deprecated
     */
    public function writeToTable($tableName, FormData $formData, FormMetaData $formMetaData)
    {
        $this->save($tableName, $formData, $formMetaData);
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
