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

    public function writeToTable($tablename, $data)
    {
        // Don't try to write to a non-existant table
        $sm = $this->app['db']->getSchemaManager();
        if (! $sm->tablesExist(array($tablename))) {
            return false;
        }

        // Don't try to insert NULLs
        foreach($data as $key => $value) {
            if ($value === null) {
                $data[$key] = '';
            }
        }

        //
        $this->app['db']->insert($tablename, $data);
    }

    public function writeToContentype($contenttype, $data)
    {
        //
        $record = $this->app['storage']->getEmptyContent($contenttype);
        $record->setValues($values);
        $id = $this->app['storage']->saveContent($record);
    }
}