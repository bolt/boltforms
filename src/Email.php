<?php
namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FieldMap;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEmailEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Silex\Application;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Email functions for BoltForms
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
class Email
{
    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** \Swift_Message */
    private $message;

    public function __construct(Application $app)
    {
        $this->app = $app;
        /** @var BoltFormsExtension $extension */
        $extension = $app['extensions']->get('Bolt/BoltForms');
        $this->config = $extension->getConfig();
    }

    /**
     * Create a notification message.
     *
     * @param FormConfig $formConfig
     * @param FormData   $formData
     */
    public function doNotification(FormConfig $formConfig, FormData $formData)
    {
        $emailConfig = new EmailConfig($this->config['debug'], $formConfig, $formData);

        $event = new BoltFormsEmailEvent($emailConfig, $formConfig, $formData);
        $this->app['dispatcher']->dispatch(BoltFormsEvents::PRE_EMAIL_SEND, $event);

        $this->emailCompose($formConfig, $emailConfig, $formData);
        $this->emailAddress($emailConfig);
        $this->emailSend($emailConfig, $formData);

        // @TODO
        //$this-> doDebugLogging();
    }

    /**
     * @TODO
     */
    private function doDebugLogging()
    {
        /** @var FlashBag $feedBack */
        $feedBack = $this->app['boltforms.feedback'];

        $from = trim($this->message->getHeaders()->get('from'));
        $to = trim($this->message->getHeaders()->get('to'));
        $cc = trim($this->message->getHeaders()->get('cc'));
        $bcc = trim($this->message->getHeaders()->get('bcc'));
        $replyTo = trim($this->message->getHeaders()->get('reply-to'));
        $subject = trim($this->message->getHeaders()->get('subject'));
        $body = $this->message->getBody();
    }

    /**
     * Compose the email data to be sent.
     *
     * @param FormConfig  $formConfig
     * @param EmailConfig $emailConfig
     * @param FormData    $formData
     */
    private function emailCompose(FormConfig $formConfig, EmailConfig $emailConfig, FormData $formData)
    {
        /*
         * Create message object
         */
        $this->message = \Swift_Message::newInstance();
        $this->message->setEncoder(\Swift_Encoding::get8BitEncoding());

        // If the form has it's own templates defined, use those, else the globals.
        $templateSubject = $formConfig->getTemplates()->getSubject() ?: $this->config['templates']['subject'];
        $templateEmail = $formConfig->getTemplates()->getEmail() ?: $this->config['templates']['email'];
        /** @var FieldMap\Email $fieldMap */
        $fieldMap = $this->config['fieldmap']['email'];

        /*
         * Subject
         */
        $html = $this->app['render']->render($templateSubject, [
            $fieldMap->getSubject() => $formConfig->getNotification()->getSubject(),
            $fieldMap->getConfig()  => $emailConfig,
            $fieldMap->getData()    => $formData,
        ]);
        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->app['render']->render($templateEmail, [
            $fieldMap->getFields() => $formConfig->getFields(),
            $fieldMap->getConfig() => $emailConfig,
            $fieldMap->getData()   => $this->getBodyData($formConfig, $emailConfig, $formData),
        ]);
        $body = new \Twig_Markup($html, 'UTF-8');

        $text = strip_tags(preg_replace('/<style\\b[^>]*>(.*?)<\\/style>/s', '', $body));

        /*
         * Build email
         */
        $this->message
                ->setSubject($subject)
                ->setBody($text)
                ->addPart($body, 'text/html');
    }

    /**
     * Get the data suitable for using in TWig.
     *
     * @param FormConfig  $formConfig
     * @param EmailConfig $emailConfig
     * @param FormData    $formData
     *
     * @return array
     */
    private function getBodyData(FormConfig $formConfig, EmailConfig $emailConfig, FormData $formData)
    {
        $bodyData = [];
        foreach ($formData->all() as $key => $value) {
            /** @var FormConfigSection $config */
            $config = $formConfig->getFields()->{$key}();
            $formValue = $formData->get($key);

            if ($formData->get($key) instanceof UploadedFileHandler) {
                if ($formData->get($key)->isValid() && $emailConfig->attachFiles()) {
                    $attachment = \Swift_Attachment::fromPath($formData->get($key)->fullPath())
                            ->setFilename($formData->get($key)->getFile()->getClientOriginalName());
                    $this->message->attach($attachment);
                }
                $relativePath = $formData->get($key, true);

                $bodyData[$key] = sprintf(
                    '<a href"%s">%s</a>',
                    $this->app['url_generator']->generate('BoltFormsDownload', ['file' => $relativePath], UrlGeneratorInterface::ABSOLUTE_URL),

                    $formData->get($key)->getFile()->getClientOriginalName()
                );
            } elseif ($config->get('type') === 'choice') {
                $choices = $config->getOptions()->getChoices();
                $bodyData[$key] = isset($choices[$formValue]) ? $choices[$formValue] : $formValue;
            } else {
                $bodyData[$key] = $formData->get($key, true);
            }
        }

        return $bodyData;
    }

    /**
     * Set the addresses.
     *
     * @param EmailConfig $emailConfig
     */
    private function emailAddress(EmailConfig $emailConfig)
    {
        $this->setFrom($emailConfig);
        $this->setReplyTo($emailConfig);

        // If we're in debug mode, don't set anything more
        if ($emailConfig->isDebug()) {
            $this->message->setTo([
                $emailConfig->getDebugEmail() => $emailConfig->getToName() ?: 'BoltForms Debug',
            ]);

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
            $this->message->setFrom([
                $emailConfig->getFromEmail() => $emailConfig->getFromName(),
            ]);
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
            $this->message->setTo([
                $emailConfig->getToEmail() => $emailConfig->getToName(),
            ]);
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
            $this->message->setCc([
                $emailConfig->getCcEmail() => $emailConfig->getCcName(),
            ]);
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
            $this->message->setBcc([
                $emailConfig->getBccEmail() => $emailConfig->getBccName(),
            ]);
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
            $this->message->setReplyTo([
                $emailConfig->getReplyToEmail() => $emailConfig->getReplyToName(),
            ]);
        }
    }

    /**
     * Send a notification
     *
     * @param EmailConfig $emailConfig
     * @param FormData    $formData
     */
    private function emailSend(EmailConfig $emailConfig, FormData $formData)
    {
        try {
            $result = $this->app['mailer']->send($this->message);
        } catch (\Swift_TransportException $e) {
            $result = false;
        }

        if ($result) {
            $this->app['logger.system']->info("Sent Bolt Forms notification to {$emailConfig->getToName()} <{$emailConfig->getToEmail()}>", ['event' => 'extensions']);
        } else {
            $data = json_encode($formData->all());
            $this->app['logger.system']->error("Failed Bolt Forms notification to {$emailConfig->getToName()} <{$emailConfig->getToEmail()}>", ['event' => 'exception', 'exception' => $data]);
        }
    }
}
