<?php
namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\EmailConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FieldMap;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEmailEvent;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Extension\Bolt\BoltForms\UploadedFileHandler;
use Bolt\Storage\EntityManager;
use Psr\Log\LoggerInterface;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment as TwigEnvironment;

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
class Email extends AbstractHandler
{
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var TwigEnvironment */
    private $twig;
    /** @var UrlGeneratorInterface */
    private $urlGenerator;
    /** SwiftMessage */
    private $emailMessage;
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

    /**
     * Constructor.
     *
     * @param Config                   $config
     * @param EntityManager            $entityManager
     * @param FlashBag                 $feedback
     * @param LoggerInterface          $logger
     * @param SwiftMailer              $mailer
     * @param EventDispatcherInterface $dispatcher
     * @param TwigEnvironment          $twig
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        Config $config,
        EntityManager $entityManager,
        FlashBag $feedback,
        LoggerInterface $logger,
        SwiftMailer $mailer,
        EventDispatcherInterface $dispatcher,
        TwigEnvironment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($config, $entityManager, $feedback, $logger, $mailer);
        $this->dispatcher = $dispatcher;
        $this->twig = $twig;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return SwiftMessage
     */
    public function getEmailMessage()
    {
        return $this->emailMessage;
    }

    /**
     * @param SwiftMessage $emailMessage
     *
     * @return Email
     */
    public function setEmailMessage(SwiftMessage $emailMessage)
    {
        $this->emailMessage = $emailMessage;

        return $this;
    }

    /**
     * Create a notification message.
     *
     * @param FormConfig   $formConfig
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function handle(FormConfig $formConfig, FormData $formData, FormMetaData $formMetaData)
    {
        $emailConfig = new EmailConfig($this->getConfig()->getDebug()->all(), $formConfig, $formData);

        $event = new BoltFormsEmailEvent($emailConfig, $formConfig, $formData);
        $this->dispatcher->dispatch(BoltFormsEvents::PRE_EMAIL_SEND, $event);

        $this->compose($formConfig, $emailConfig, $formData, $formMetaData);
        $this->address($emailConfig);
        $this->send($emailConfig);

        $this->log($emailConfig);
    }

    /**
     * Compose the email data to be sent.
     *
     * @param FormConfig   $formConfig
     * @param EmailConfig  $emailConfig
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    private function compose(FormConfig $formConfig, EmailConfig $emailConfig, FormData $formData, FormMetaData $formMetaData)
    {
        // If the form has it's own templates defined, use those, else the globals.
        $templateSubject = $formConfig->getTemplates()->getSubject() ?: $this->getConfig()->getTemplates()->get('subject');
        $templateEmail = $formConfig->getTemplates()->getEmail() ?: $this->getConfig()->getTemplates()->get('email');
        /** @var FieldMap\Email $fieldMap */
        $fieldMap = $this->getConfig()->getFieldMap()->get('email');

        /*
         * Subject
         */
        $html = $this->twig->render($templateSubject, [
            $fieldMap->getSubject()  => $formConfig->getNotification()->getSubject(),
            $fieldMap->getConfig()   => $emailConfig,
            $fieldMap->getData()     => $formData,
            $fieldMap->getMetaData() => $formMetaData->getUsedMeta('email'),
        ]);
        $subject = new \Twig_Markup($html, 'UTF-8');

        /*
         * Body
         */
        $html = $this->twig->render($templateEmail, [
            $fieldMap->getFields()   => $formConfig->getFields(),
            $fieldMap->getConfig()   => $emailConfig,
            $fieldMap->getData()     => $this->getBodyData($formConfig, $emailConfig, $formData),
            $fieldMap->getMetaData() => $formMetaData->getUsedMeta('email'),
        ]);
        $body = new \Twig_Markup($html, 'UTF-8');

        $text = strip_tags(preg_replace('/<style\\b[^>]*>(.*?)<\\/style>/s', '', $body));

        /*
         * Build email
         */
        $this->emailMessage = SwiftMessage::newInstance()
            ->addPart($body, 'text/html')
            ->setSubject($subject)
            ->setBody($text)
            ->setEncoder(\Swift_Encoding::get8BitEncoding())
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
                    $this->getEmailMessage()->attach($attachment);
                }
                $relativePath = $formData->get($key, true);

                $bodyData[$key] = sprintf(
                    '<a href"%s">%s</a>',
                    $this->urlGenerator->generate('BoltFormsDownload', ['file' => $relativePath], UrlGeneratorInterface::ABSOLUTE_URL),

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
    private function address(EmailConfig $emailConfig)
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
            $this->getEmailMessage()->setFrom([
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
            $this->getEmailMessage()->setReplyTo([
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
        $emailMessage = $this->getEmailMessage();
        $swiftFunc = key($this->map[$type]);
        $configFunc = $this->map[$type][$swiftFunc];
        $email = call_user_func([$emailConfig, $configFunc['email']]);
        $name = call_user_func([$emailConfig, $configFunc['name']]);

        if ($email === null) {
            return;
        }

        if ($emailConfig->isDebug()) {
            $emailMessage->getHeaders()->addTextHeader("X-BoltForms-debug-$type", $email);
            call_user_func([$emailMessage, $swiftFunc], [$emailConfig->getDebugEmail() => $name ?: 'BoltForms Debug']);
        } else {
            call_user_func([$emailMessage, $swiftFunc], [$email => $name ?: $email]);
        }
    }

    /**
     * Send a notification
     *
     * @param EmailConfig $emailConfig
     */
    private function send(EmailConfig $emailConfig)
    {
        try {
            $this->getMailer()->send($this->emailMessage);
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
    private function log(EmailConfig $emailConfig)
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
            [$this->getEmailMessage()->getBody()],
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
        return trim($this->getEmailMessage()->getHeaders()->get($headerName));
    }
}
