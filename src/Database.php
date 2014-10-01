<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Silex\Application;

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
     * @param  string  $tablename
     * @param  array   $data
     * @return boolean
     */
    public function writeToTable($tablename, $data)
    {
        // Don't try to write to a non-existant table
        $sm = $this->app['db']->getSchemaManager();
        if (! $sm->tablesExist(array($tablename))) {
            return false;
        }

        // Build a new array with only keys that match the database table
        $columns = $sm->listTableColumns($tablename);
        foreach ($columns as $column) {
            $colname = $column->getName();
            $savedata[$colname] = $data[$colname];
        }

        // Don't try to insert NULLs
        foreach ($savedata as $key => $value) {
            if ($value === null) {
                $savedata[$key] = '';
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
    public function writeToContentype($contenttype, $data)
    {
        $record = $this->app['storage']->getEmptyContent($contenttype);
        $record->setValues($data);
        $this->app['storage']->saveContent($record);
    }
}
