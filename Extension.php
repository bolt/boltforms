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
            // Site wide recapture
            if ($this->config['recaptcha']['enabled'] && ! function_exists('recaptcha_check_answer')) {
                require_once 'recaptcha-php-1.11/recaptchalib.php';
            }

            // Twig functions
            $this->app['twig']->addExtension(new Twig\FormsExtension($this->app));
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
     * Set the defaults for configuration parameters
     *
     * @return array
     */
    protected function getDefaultConfig()
    {
        return array(
            'csrf' => true,
            'recaptcha' => array(
                'enabled'       => false,
                'public_key'    => '',
                'private_key'   => '',
                'error_message' => "The CAPTCHA wasn't entered correctly. Please try again.",
                'theme'         => 'clean'
            ),
            'templates' => array(
                'fields' => 'boltforms_fields.twig',
                'form'   => 'boltforms_form.twig',
                'email'  => 'boltforms_email.twig'
            ),
            'debug' => array(
                'enabled'       => true,
                'debug_address' => ''
            )
        );
    }

}
