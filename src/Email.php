<?php

namespace Bolt\Extension\Bolt\Forms;

use Bolt;
use Silex\Application;

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

    /**
     * \Swift_Message
     */
    private $message;

    public function __construct(Application $app)
    {
        $this->app = $this->config = $app;
        $this->config = $app[Extension::CONTAINER]->config;
    }

    /**
     *
     */
    public function doNotification($formconfig, $emailconfig, $postdata)
    {
        //
        $this->doCompose($formconfig, $emailconfig, $postdata);

        //
        $this->doAddress($emailconfig);

        //
        $this->doSend($emailconfig);
    }

    /**
     * Compose the email data to be sent
     */
    private function doCompose($formconfig, $emailconfig, $postdata)
    {
        // Set our Twig lookup path
        $this->addTwigPath();

        /*
         * Subject
         */
        $html = $this->app['render']->render($this->config['templates']['subject'], array(
            'subject' => $formconfig['notification']['subject'],
            'config'  => $emailconfig,
            'data'    => $postdata
        ));

        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->app['render']->render($this->config['templates']['email'], array(
            'fields' => $formconfig['fields'],
            'config' => $emailconfig,
            'data'   => $postdata
        ));

        $body = new \Twig_Markup($html, 'UTF-8');

        /*
         * Build email
         */
        $this->message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setBody(strip_tags($body))
                ->addPart($body, 'text/html');
    }

    /**
     * Set the addresses
     *
     * @param array $emailconfig
     */
    private function doAddress($emailconfig)
    {
        /*
         * From
         */
        if (! empty($emailconfig['from_email'])) {
            $recipient = array(
                'from_email'   => $emailconfig['from_email'],
                'from_name' => isset($emailconfig['from_name']) ? $emailconfig['from_name'] : ''
            );
        }

        $this->message->setFrom(array(
            $recipient['from_email'] => $recipient['from_name']
        ));


        /*
         * To
         */
        if (! empty($emailconfig['to_email'])) {
            $recipient = array(
                'to_email'   => $emailconfig['to_email'],
                'to_name' => isset($emailconfig['to_name']) ? $emailconfig['to_name'] : ''
            );
        }

        $this->message->setTo(array(
            $recipient['to_email'] => $recipient['to_name']
        ));

        /*
         * CC
         */
        if (! empty($emailconfig['cc_email'])) {
            $recipient = array(
                'cc_email'   => $emailconfig['cc_email'],
                'cc_name' => isset($emailconfig['cc_name']) ? $emailconfig['cc_name'] : ''
            );

            if (isset($emailconfig['cc_email'])) {
                $this->message->setCc($emailconfig['cc_email']);
            }
        }

        /*
         * BCC
         */
        if (! empty($emailconfig['bcc_email'])) {
            $recipient = array(
                'bcc_email'   => $emailconfig['bcc_email'],
                'bcc_name' => isset($emailconfig['bcc_name']) ? $emailconfig['bcc_name'] : ''
            );

            if (isset($emailconfig['bcc_email'])) {
                $this->message->setBcc($emailconfig['bcc_email']);
            }
        }

    }

    /**
     * Send a notification
     *
     * @param array $emailconfig
     */
    private function doSend($emailconfig)
    {
        if ($this->app['mailer']->send($this->message)) {
            $this->app['log']->add("Sent Bolt Forms notification to {$emailconfig['to_name']} <{$emailconfig['to_email']}>", 3);
        } else {
            $this->app['log']->add("Failed Bolt Forms notification to {$emailconfig['to_name']} <{$emailconfig['to_email']}>", 3);
        }
    }

    private function addTwigPath()
    {
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');
    }
}
