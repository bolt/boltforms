<?php
namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\BoltFormsExtension;
use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FieldMap;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEmailEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\UploadedFileHandler;
use Silex\Application;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\BufferedOutput;
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
class EmailHandler
{
    use FeedbackHandlerTrait;

    /** @var Application */
    private $app;
    /** @var array */
    private $config;
    /** \Swift_Mime_SimpleMessage */
    private $message;
    /** @var array */
    private $map = [
        'to'  => ['setTo'  => [
            'email' => 'getToEmail',
            'name'  => 'getToName',
        ]],
        'cc'  => ['setCc'  => [
            'email' => 'getCcEmail',
            'name'  => 'getCcName',
        ]],
        'bcc' => ['setBcc' => [
            'email' => 'getBccEmail',
            'name'  => 'getBccName',
        ]],
    ];

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
     * @param FormConfig   $formConfig
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function doNotification(FormConfig $formConfig, FormData $formData, FormMetaData $formMetaData)
    {
        $emailConfig = new EmailConfig($this->config['debug'], $formConfig, $formData);

        $event = new BoltFormsEmailEvent($emailConfig, $formConfig, $formData);
        $this->app['dispatcher']->dispatch(BoltFormsEvents::PRE_EMAIL_SEND, $event);

        $this->emailCompose($formConfig, $emailConfig, $formData);
        $this->emailAddress($emailConfig);
        $this->emailSend($emailConfig, $formData);

        $this-> doDebugLogging($emailConfig);
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
            ->addPart($body, 'text/html')
        ;
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

        $this->setEmailDeliveryField($emailConfig, 'to');
        $this->setEmailDeliveryField($emailConfig, 'cc');
        $this->setEmailDeliveryField($emailConfig, 'bcc');
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
     * Ensure email addresses are sanitised during debug.
     *
     * @param EmailConfig $emailConfig
     * @param string      $type
     */
    private function setEmailDeliveryField(EmailConfig $emailConfig, $type)
    {
        $swiftFunc = key($this->map[$type]);
        $configFunc = $this->map[$type][$swiftFunc];
        $email = call_user_func([$emailConfig, $configFunc['email']]);
        $name = call_user_func([$emailConfig, $configFunc['name']]);

        if ($email === null) {
            return;
        }

        if ($emailConfig->isDebug()) {
            $this->message->getHeaders()->addTextHeader("X-BoltForms-debug-$type", $email);
            call_user_func([$this->message, $swiftFunc], [$emailConfig->getDebugEmail() => $name ?: 'BoltForms Debug']);
        } else {
            call_user_func([$this->message, $swiftFunc], [$email => $name ?: $email]);
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
            $this->getMailer()->send($this->message);
            $this->message(sprintf('Sent Bolt Forms notification to "%s <%s>"', $emailConfig->getToName(), $emailConfig->getToEmail()));
        } catch (\Swift_TransportException $e) {
            $this->exception($e, false, sprintf('Failed sending Bolt Forms notification to "%s <%s>"', $emailConfig->getToName(), $emailConfig->getToEmail()));
        } catch (\Exception $e) {
            $this->exception($e, false, sprintf('An exception was thrown during email dispatch:'));
        }
    }

    /**
     * @param EmailConfig $emailConfig
     */
    private function doDebugLogging(EmailConfig $emailConfig)
    {
        if (!$emailConfig->isDebug()) {
            return;
        }

        $output = new BufferedOutput();
        $table = new Table($output);
        $style = new TableStyle();

        $style
            ->setHorizontalBorderChar(null)
            ->setVerticalBorderChar(null)
        ;
        $table->setStyle($style);
        $table->addRows([
            [$this->getHeader('X-BoltForms-debug-to')],
            [$this->getHeader('X-BoltForms-debug-cc')],
            [$this->getHeader('X-BoltForms-debug-bcc')],
            new TableSeparator(),
            [$this->getHeader('to')],
            [$this->getHeader('cc')],
            [$this->getHeader('bcc')],
            [$this->getHeader('from')],
            [$this->getHeader('reply-to')],
            [$this->getHeader('subject')],
            new TableSeparator(),
            [$this->message->getBody()],
        ]);
        $table->render();

        $this->message(sprintf('Compiled message:%s%s', "\n", $output->fetch()));
    }

    /**
     * Return a trimmed header.
     *
     * @param string $headerName
     *
     * @return string
     */
    private function getHeader($headerName)
    {
        return trim($this->message->getHeaders()->get($headerName));
    }

    /**
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        return $this->app['boltforms.feedback'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->app['logger.system'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getMailer()
    {
        return $this->app['mailer'];
    }
}
