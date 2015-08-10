<?php
namespace Bolt\Extension\Bolt\BoltForms;

use Bolt;
use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Exception\EmailException;
use Silex\Application;

/**
 * Email functions for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
     * Create a notification message.
     *
     * @param array  $formconfig
     * @param array  $formdata
     */
    public function doNotification($formconfig, $formdata)
    {
        $emailConfig = new EmailConfig($this->config['debug'], $formconfig, $formdata);

        $this->doCompose($formconfig, $emailConfig, $formdata);
        $this->doAddress($emailConfig);
        $this->doSend($emailConfig);
    }

    /**
     * Compose the email data to be sent.
     *
     * @param array $formconfig
     * @param EmailConfig $emailConfig
     * @param array $formdata
     */
    private function doCompose($formconfig, EmailConfig $emailConfig, $formdata)
    {
        /*
         * Create message object
         */
        $this->message = \Swift_Message::newInstance();

        // Set our Twig lookup path
        $this->addTwigPath();

        // If the form has it's own templates defined, use those, else the globals.
        $templateSubject = isset($formconfig['templates']['subject'])
            ? $formconfig['templates']['subject']
            : $this->config['templates']['subject'];
        $templateEmail = isset($formconfig['templates']['email'])
            ? $formconfig['templates']['email']
            : $this->config['templates']['email'];

        /*
         * Subject
         */
        $html = $this->app['render']->render($templateSubject, array(
            'subject' => $formconfig['notification']['subject'],
            'config'  => $emailConfig,
            'data'    => $formdata
        ));

        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->app['render']->render($templateEmail, array(
            'fields' => $formconfig['fields'],
            'config' => $emailConfig,
            'data'   => $this->getBodyData($emailConfig, $formconfig, $formdata)
        ));

        $body = new \Twig_Markup($html, 'UTF-8');

        /*
         * Build email
         */
        $this->message
                ->setSubject($subject)
                ->setBody(strip_tags($body))
                ->addPart($body, 'text/html');
    }

    /**
     * Get the data suitable for using in TWig.
     *
     * @param EmailConfig $emailConfig
     * @param array       $formconfig
     * @param array       $formdata
     *
     * @return array
     */
    private function getBodyData(EmailConfig $emailConfig, array $formconfig, array $formdata)
    {
        // https://github.com/bolt/bolt/issues/3459
        // https://github.com/GawainLynch/bolt-extension-boltforms/issues/15
        $bodydata = array();
        foreach ($formdata as $key => $value) {
            if ($value instanceof \DateTime) {
                $bodydata[$key] = $value->format('c');
            } elseif ($value instanceof FileUpload) {
                if ($value->isValid() && $emailConfig->attachFiles()) {
                    $attachment = \Swift_Attachment::fromPath($value->fullPath())
                            ->setFilename($value->getFile()->getClientOriginalName());
                    $this->message->attach($attachment);
                }
            } else {
                $bodydata[$key] = $value;
            }
        }

        return $bodydata;
    }

    /**
     * Set the addresses.
     *
     * @param EmailConfig $emailConfig
     */
    private function doAddress(EmailConfig $emailConfig)
    {
        $this->setFrom($emailConfig);
        $this->setReplyTo($emailConfig);

        // If we're in debug mode, don't set anything more
        if ($emailConfig->isDebug()) {
            $this->message->setTo(array(
                $emailConfig->getDebugEmail() => $emailConfig->getToName() ?: 'BoltForms Debug'
            ));

            // Don't set any further recipients
            return;
        }

        $this->setTo($emailConfig);
        $this->setCc($emailConfig);
        $this->setBcc($emailConfig);
    }

    /**
     * Set From.
     *
     * @param EmailConfig $emailConfig
     */
    private function setFrom(EmailConfig $emailConfig)
    {
        if ($emailConfig->getFromEmail()) {
            $this->message->setFrom(array(
                $emailConfig->getFromEmail() => $emailConfig->getFromName()
            ));
        }
    }

    /**
     * Set To.
     *
     * @param EmailConfig $emailConfig
     */
    private function setTo(EmailConfig $emailConfig)
    {
        if ($emailConfig->getToEmail()) {
            $this->message->setTo(array(
                $emailConfig->getToEmail() => $emailConfig->getToName()
            ));
        }
    }

    /**
     * Set CC.
     *
     * @param EmailConfig $emailConfig
     */
    private function setCc(EmailConfig $emailConfig)
    {
        if ($emailConfig->getCcEmail()) {
            $this->message->setCc(array(
                $emailConfig->getCcEmail() => $emailConfig->getCcName()
            ));
        }
    }

    /**
     * Set bCC.
     *
     * @param EmailConfig $emailConfig
     */
    private function setBcc(EmailConfig $emailConfig)
    {
        if ($emailConfig->getBccEmail()) {
            $this->message->setCc(array(
                $emailConfig->getBccEmail() => $emailConfig->getBccName()
            ));
        }
    }

    /**
     * Set the ReplyTo.
     *
     * @param EmailConfig $emailConfig
     */
    private function setReplyTo(EmailConfig $emailConfig)
    {
        if ($emailConfig->getReplyToEmail()) {
            $this->message->setReplyTo(array(
                $emailConfig->getReplyToEmail() => $emailConfig->getReplyToName()
            ));
        }
    }

    /**
     * Send a notification
     *
     * @param EmailConfig $emailConfig
     */
    private function doSend(EmailConfig $emailConfig)
    {
        if ($this->app['mailer']->send($this->message)) {
            $this->app['logger.system']->info("Sent Bolt Forms notification to {$emailConfig->getToName()} <{$emailConfig->getToEmail()}>", array('event' => 'extensions'));
        } else {
            $this->app['logger.system']->info("Failed Bolt Forms notification to {$emailConfig->getToName()} <{$emailConfig->getToEmail()}>", array('event' => 'extensions'));
        }
    }

    private function addTwigPath()
    {
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');
    }
}
