<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt;
use Silex\Application;

/**
 * Email functions for BoltForms
 *
 * Copyright (C) 2014 Gawain Lynch
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
     *
     */
    public function doNotification($formname, $formconfig, $formdata)
    {
        $emailconfig = $this->getEmailConfig($formname, $formdata);

        //
        $this->doCompose($formconfig, $emailconfig, $formdata);

        //
        $this->doAddress($emailconfig);

        //
        $this->doSend($emailconfig);
    }

    /**
     * Compose the email data to be sent
     */
    private function doCompose($formconfig, $emailconfig, $formdata)
    {
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
            'config'  => $emailconfig,
            'data'    => $formdata
        ));

        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->app['render']->render($templateEmail, array(
            'fields' => $formconfig['fields'],
            'config' => $emailconfig,
            'data'   => $formdata
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
                'from_email' => $emailconfig['from_email'],
                'from_name'  => isset($emailconfig['from_name']) ? $emailconfig['from_name'] : 'BoltForms'
            );
        }

        $this->message->setFrom(array(
            $recipient['from_email'] => $recipient['from_name']
        ));

        /*
         * Debug
         */
        if (! empty($emailconfig['debug']) && $emailconfig['debug']) {
            $recipient = array(
                'to_email' => $emailconfig['debug_address'],
                'to_name'  => isset($emailconfig['to_name']) ? $emailconfig['to_name'] : 'BoltForms Debug'
            );

            $this->message->setTo(array(
                $recipient['to_email'] => $recipient['to_name']
            ));

            // Don't set any further recipients
            return;
        }

        /*
         * To
         */
        if (! empty($emailconfig['to_email'])) {
            $recipient = array(
                'to_email' => $emailconfig['to_email'],
                'to_name'  => isset($emailconfig['to_name']) ? $emailconfig['to_name'] : ''
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
                'cc_email' => $emailconfig['cc_email'],
                'cc_name'  => isset($emailconfig['cc_name']) ? $emailconfig['cc_name'] : ''
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
                'bcc_email' => $emailconfig['bcc_email'],
                'bcc_name'  => isset($emailconfig['bcc_name']) ? $emailconfig['bcc_name'] : ''
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
            $this->app['logger.system']->info("Sent Bolt Forms notification to {$emailconfig['to_name']} <{$emailconfig['to_email']}>", array('event' => 'extensions'));
        } else {
            $this->app['logger.system']->info("Failed Bolt Forms notification to {$emailconfig['to_name']} <{$emailconfig['to_email']}>", array('event' => 'extensions'));
        }
    }

    /**
     * Get a usable email configuration array
     *
     * @param string $formname
     * @param array  $formdata
     */
    private function getEmailConfig($formname, $formdata)
    {
        $notify_form = $this->config[$formname]['notification'];

        /*
         * Global debug enabled
         *   - Messages should always go to the global debug address only
         *   - Takes preference over form specific settings
         *   - To address also takes precidence
         *
         * Global debug disabled
         *   - Form specific debug settings are applied
         *
         * Form debug enabled
         *   - For debug address takes priority if set
         */
        if ($this->config['debug']['enabled']) {
            $debug = true;

            if (empty($this->config['debug']['address'])) {
                trigger_error('[BoltForms] Debug email address can not be empty if debugging enabled!', E_USER_ERROR);
            } else {
                $debug_address = $this->config['debug']['address'];
            }
        } else {
            if (isset($notify_form['debug']) && $notify_form['debug']) {
                $debug = true;
            }

            if (isset($notify_form['debug_address'])) {
                $debug_address = $notify_form['debug_address'];
            } else {
                $debug_address = $this->config['debug']['address'];
            }
        }

        $emailconfig = array(
            'debug'         => $debug,
            'debug_address' => $debug_address,
            'to_name'       => isset($notify_form['to_name'])    ? $notify_form['to_name']    : '',
            'to_email'      => isset($notify_form['to_email'])   ? $notify_form['to_email']   : '',
            'from_name'     => isset($notify_form['from_name'])  ? $notify_form['from_name']  : '',
            'from_email'    => isset($notify_form['from_email']) ? $notify_form['from_email'] : '',
            'cc_name'       => isset($notify_form['cc_name'])    ? $notify_form['cc_name']    : '',
            'cc_email'      => isset($notify_form['cc_email'])   ? $notify_form['cc_email']   : '',
            'bcc_name'      => isset($notify_form['bcc_name'])   ? $notify_form['bcc_name']   : '',
            'bcc_email'     => isset($notify_form['bcc_email'])  ? $notify_form['bcc_email']  : ''
        );

        // If any fields rely on posted data populate them now
        foreach ($emailconfig as $key => $value) {
            if ($key == 'debug' || $key == 'debug_address') {
                continue;
            }

            if (isset($formdata[$value])) {
                $emailconfig[$key] = $formdata[$value];
            }
        }

        return $emailconfig;
    }

    private function addTwigPath()
    {
        $this->app['twig.loader.filesystem']->addPath(dirname(__DIR__) . '/assets');
    }
}
