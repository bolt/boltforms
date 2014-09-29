<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;

/**
 *
 * WARNING: VERY PROOF-OF-CONCEPT IN ALL WAYS!
 *
 */
class Extension extends \Bolt\BaseExtension
{
    /**
     * @var Extension name
     */
    const NAME = "Forms";

    /**
     * Extension's container
     *
     * @var string
     */
    const CONTAINER = 'extensions.Forms';

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {

        /*
         * Config
         */
        $this->setConfig();

        /*
         * Backend
         */
        if ($this->app['config']->getWhichEnd() == 'backend') {
            //
        }

        /*
         * Frontend
         */
        if ($this->app['config']->getWhichEnd() == 'frontend') {
            //
        }
    }

    /**
     * Post config file loading configuration
     *
     * @return void
     */
    private function setConfig()
    {
        //
    }

    /**
     * Set the defaults for configuration parameters you want to use. These will
     * be overriden by whatever is in the extensions configuration file
     *
     * This is called by Bolt internally during extension initialisation
     *
     * These example below will be available as $this->config['foo']
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
        );
    }

}
