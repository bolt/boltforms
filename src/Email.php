<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;

class Email
{

    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    public function __construct(Silex\Application $app)
    {
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }
}