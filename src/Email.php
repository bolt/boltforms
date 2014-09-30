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

        $this->debug = $this->config['notifications']['debug'];
        $this->debug_address = $this->config['notifications']['debug_address'];
        $this->from_address = $this->config['notifications']['from_address'];
    }

    /**
     *
     */
    public function doNotification()
    {
        // Sort out the "to whom" list
        if ($this->debug) {
            $this->recipients = array(
                array(
                    'firstName' => 'Test',
                    'lastName' => 'Notifier',
                    'displayName' => 'Test Notifier',
                    'email' => $this->debug_address
                ));

        } else {
            // Get the subscribers to the topic and it's forum
            $subscriptions = new Subscriptions($this->app);
            $this->recipients = $subscriptions->getSubscribers($this->record->values['topic']);
        }

        // Get the email template
        $this->doCompose();

        // Get the email template
        foreach ($this->recipients as $recipient) {
            $this->doSend($this->message, $recipient);
        }
    }

    /**
     * Compose the email data to be sent
     */
    private function doCompose()
    {
        // Set our Twig lookup path
        $this->addTwigPath();

        /*
         * Subject
         */
        $html = $this->app['render']->render($this->config['templates']['email']['subject'], array(
            'forum'       => $forum['title'],
            'contenttype' => $this->record->contenttype['singular_name'],
            'title'       => $title,
            'author'      => $this->record->values['authorprofile']['displayname']
        ));

        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
        */
        $html = $this->app['render']->render($this->config['templates']['email']['body'], array(
            'forum'       => $forum['title'],
            'contenttype' => $this->record->contenttype['singular_name'],
            'title'       => $title,
            'author'      => $this->record->values['authorprofile']['displayname'],
            'uri'         => $uri,
            'body'        => $this->record->values['body']
        ));

        $body = new \Twig_Markup($html, 'UTF-8');

        /*
         * Build email
        */
        $this->message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->from_address)
                ->setBody(strip_tags($body))
                ->addPart($body, 'text/html');
        // SwiftMail barfs on this, despite it being documented to work!
        //->setFrom(array($this->from_address, $this->config['boltbb']['title']))
    }

    /**
     * Send a notification
     *
     * @param \Swift_Message $message
     * @param array          $recipient
     */
    private function doSend(\Swift_Message $message, $recipient)
    {
        // Set the recipient for *this* message
        $message->setTo(array(
            $recipient['email'] => $recipient['displayName']
        ));

        if ($this->app['mailer']->send($message)) {
            $this->app['log']->add("Sent Bolt Forms notification to {$recipient['displayName']} <{$recipient['email']}>", 3);
        } else {
            $this->app['log']->add("Failed Bolt Forms notification to {$recipient['displayName']} <{$recipient['email']}>", 3);
        }
    }

    private function addTwigPath()
    {
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');
    }
}