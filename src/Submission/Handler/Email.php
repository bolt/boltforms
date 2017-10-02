<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config;
use Bolt\Extension\Bolt\BoltForms\Event;
use Bolt\Extension\Bolt\BoltForms\Exception\InternalProcessorException;
use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Bolt\Extension\Bolt\EmailSpooler\EventListener\QueueListener;
use Bolt\Storage\Entity;
use Bolt\Storage\EntityManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Swift_Mailer as SwiftMailer;
use Swift_Message as SwiftMessage;
use Swift_RfcComplianceException as SwiftRfcComplianceException;
use Swift_TransportException as SwiftTransportException;
use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Environment as TwigEnvironment;

/**
 * Email functions for BoltForms
 *
 * Copyright (c) 2014-2016 Gawain Lynch
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License or GNU Lesser
 * General Public License as published by the Free Software Foundation,
 * either version 3 of the Licenses, or (at your option) any later version.
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
 * @license   http://opensource.org/licenses/LGPL-3.0 GNU Lesser General Public License 3.0
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
     * @param Config\Config            $config
     * @param EntityManager            $entityManager
     * @param SessionInterface         $session
     * @param LoggerInterface          $logger
     * @param SwiftMailer              $mailer
     * @param EventDispatcherInterface $dispatcher
     * @param TwigEnvironment          $twig
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        Config\Config $config,
        EntityManager $entityManager,
        SessionInterface $session,
        LoggerInterface $logger,
        SwiftMailer $mailer,
        EventDispatcherInterface $dispatcher,
        TwigEnvironment $twig,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($config, $entityManager, $session, $logger, $mailer);
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
     * @param Config\FormConfig $formConfig
     * @param Entity\Entity     $formData
     * @param Config\MetaData   $formMetaData
     */
    public function handle(Config\FormConfig $formConfig, Entity\Entity $formData, Config\MetaData $formMetaData)
    {
        $emailConfig = new Config\EmailConfig($formConfig, $formData);

        $event = new Event\EmailEvent($emailConfig, $formConfig, $formData);
        $this->dispatcher->dispatch(Event\BoltFormsEvents::PRE_EMAIL_SEND, $event);

        $this->compose($formConfig, $emailConfig, $formData, $formMetaData);
        $this->address($emailConfig);
        $this->send($emailConfig);

        $this->log($emailConfig);
    }

    /**
     * Compose the email data to be sent.
     *
     * @param Config\FormConfig  $formConfig
     * @param Config\EmailConfig $emailConfig
     * @param Entity\Entity      $formData
     * @param Config\MetaData    $formMetaData
     */
    private function compose(
        Config\FormConfig $formConfig,
        Config\EmailConfig $emailConfig,
        Entity\Entity $formData,
        Config\MetaData $formMetaData
    ) {
        // If the form has it's own templates defined, use those, else the globals.
        $templateSubject = $formConfig->getTemplates()->getSubject() ?: $this->getConfig()->getTemplates()->get('subject');
        $templateEmail = $formConfig->getTemplates()->getEmail() ?: $this->getConfig()->getTemplates()->get('email');
        /** @var Config\FieldMap\Email $fieldMap */
        $fieldMap = $this->getConfig()->getFieldMap()->get('email');

        /*
         * Build empty email
         */
        $this->emailMessage = SwiftMessage::newInstance();

        /*
         * Subject
         */
        $html = $this->twig->render($templateSubject, [
            $fieldMap->getSubject()  => $formConfig->getNotification()->getSubject(),
            $fieldMap->getConfig()   => $emailConfig,
            $fieldMap->getData()     => $formData,
            $fieldMap->getMetaData() => $formMetaData->getUsedMeta('email'),
            'templates'              => $this->getConfig()->getTemplates(),
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
            'templates'              => $this->getConfig()->getTemplates(),
        ]);
        $body = new \Twig_Markup($html, 'UTF-8');

        $text = strip_tags(preg_replace('/<style\\b[^>]*>(.*?)<\\/style>/s', '', $body));

        /*
         * Append email parts
         */
        $this->emailMessage
            ->addPart($body, 'text/html')
            ->setSubject($subject)
            ->setBody($text)
            ->setEncoder(\Swift_Encoding::get8BitEncoding())
        ;
    }

    /**
     * Get the data suitable for using in TWig.
     *
     * @param Config\FormConfig  $formConfig
     * @param Config\EmailConfig $emailConfig
     * @param Entity\Entity      $formData
     *
     * @return array
     */
    private function getBodyData(Config\FormConfig $formConfig, Config\EmailConfig $emailConfig, Entity\Entity $formData)
    {
        $bodyData = [];
        foreach ($formData->toArray() as $fieldName => $value) {
            if (!$formConfig->getFields()->has($fieldName)) {
                continue;
            }
            /** @var Config\Form\FieldBag $fieldConfig */
            $fieldConfig = $formConfig->getFields()->{$fieldName}();
            $formValue = $formData->get($fieldName);

            $type = $fieldConfig->get('type');
            if ($type === 'file') {
                $files = $formValue;
                if ($emailConfig->attachFiles()) {
                    $this->setFileAttachments($files);
                }

                /** @var \Bolt\Extension\Bolt\BoltForms\Submission\File $file */
                foreach ($files as $file) {
                    $relativePath = $file->getRelativePath();
                    $fileName = $file->getFilename();
                    try {
                        $link = $this->urlGenerator->generate(
                            'BoltFormsDownload',
                            ['file' => $relativePath],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        );
                    } catch (RouteNotFoundException $e) {
                        $link = '';
                    }
                    $bodyData[$fieldName][$fileName] = $link;
                }
            } elseif ($type === 'choice') {
                $options = $fieldConfig->getOptions();
                $bodyData[$fieldName] = $options->get($fieldName, $formValue);
            } else {
                $bodyData[$fieldName] = $formData->get($fieldName, null, true);
            }
        }

        return $bodyData;
    }

    /**
     * Attach uploaded files to the message body.
     *
     * @param File|File[] $formValues
     */
    private function setFileAttachments($formValues)
    {
        if ($formValues instanceof File) {
            $this->attachFile($formValues);

            return;
        }

        /** @var File $uploadedFile */
        foreach ($formValues as $uploadedFile) {
            if ($uploadedFile instanceof File) {
                $this->attachFile($uploadedFile);
            }
        }
    }

    /**
     * Attach a single uploaded file to the message body.
     *
     * @param File $uploadedFile
     */
    private function attachFile(File $uploadedFile)
    {
        $fromPath = $uploadedFile->getPathname();
        if ($uploadedFile instanceof UploadedFile) {
            $fileName = $uploadedFile->getClientOriginalName();
        } else {
            $fileName = $uploadedFile->getFilename();
        }
        $attachment = \Swift_Attachment::fromPath($fromPath)->setFilename($fileName);
        $this->getEmailMessage()->attach($attachment);
    }

    /**
     * Set the addresses.
     *
     * @param Config\EmailConfig $emailConfig
     */
    private function address(Config\EmailConfig $emailConfig)
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
     * @param Config\EmailConfig $emailConfig
     */
    private function setFrom(Config\EmailConfig $emailConfig)
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
     * @param Config\EmailConfig $emailConfig
     */
    private function setReplyTo(Config\EmailConfig $emailConfig)
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
     * @param Config\EmailConfig $emailConfig
     * @param string             $type
     */
    private function setEmailDeliveryField(Config\EmailConfig $emailConfig, $type)
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
     * @param Config\EmailConfig $emailConfig
     *
     * @throws InternalProcessorException
     */
    private function send(Config\EmailConfig $emailConfig)
    {
        /** @var SwiftMailer $mailer */
        $mailer = $this->getMailer();
        $failed = [];

        try {
            // Queue the message in the mailer
            $mailer->send($this->emailMessage, $failed);
            if ($emailConfig->isDebug()) {
                $this->dispatcher->dispatch(QueueListener::FLUSH);
            }
            $this->message(sprintf('Sent Bolt Forms notification to "%s <%s>"', $emailConfig->getToName(), $emailConfig->getToEmail()), Processor::FEEDBACK_DEBUG, LogLevel::DEBUG);
        } catch (SwiftTransportException $e) {
            $this->exception($e, false, sprintf('Failed sending Bolt Forms notification to "%s <%s>"', $emailConfig->getToName(), $emailConfig->getToEmail()));
            throw new InternalProcessorException($e->getMessage(), $e->getCode(), $e, false);
        } catch (SwiftRfcComplianceException $e) {
            $message = 'Failed sending Bolt Forms notification due to an invalid email address:' . PHP_EOL;
            foreach ($failed as $fail) {
                $message .= sprintf('  * %s%s', $fail, PHP_EOL);
            }
            throw new InternalProcessorException($message, $e->getCode(), $e, false);
        } catch (\Exception $e) {
            throw new InternalProcessorException('An exception was thrown during email dispatch:', $e->getCode(), $e, false);
        }
    }

    /**
     * @param Config\EmailConfig $emailConfig
     */
    private function log(Config\EmailConfig $emailConfig)
    {
        if (!$emailConfig->isDebug() || !$emailConfig->isDebugSmtp()) {
            return;
        }

        $output = new BufferedOutput();
        $table = new Helper\Table($output);
        $style = new Helper\TableStyle();

        $style
            ->setHorizontalBorderChar(null)
            ->setVerticalBorderChar(null)
        ;
        $table->setStyle($style);
        $table->addRows([
            [$this->getHeader('X-BoltForms-debug-to')],
            [$this->getHeader('X-BoltForms-debug-cc')],
            [$this->getHeader('X-BoltForms-debug-bcc')],
            new Helper\TableSeparator(),
            [$this->getHeader('to')],
            [$this->getHeader('cc')],
            [$this->getHeader('bcc')],
            [$this->getHeader('from')],
            [$this->getHeader('reply-to')],
            [$this->getHeader('subject')],
            new Helper\TableSeparator(),
            [$this->getEmailMessage()->getBody()],
        ]);
        $table->render();

        $this->message(sprintf('Compiled message:%s%s', "\n", $output->fetch()), Processor::FEEDBACK_DEBUG, LogLevel::DEBUG);
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
