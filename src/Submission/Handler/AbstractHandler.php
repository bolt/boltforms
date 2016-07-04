<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Submission\FeedbackTrait;
use Bolt\Storage\EntityManager;
use Closure;
use Psr\Log\LoggerInterface;
use Swift_Mailer as SwiftMailer;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

abstract class AbstractHandler
{
    use FeedbackTrait;

    /** @var Config */
    private $config;
    /** @var EntityManager */
    private $entityManager;
    /** @var FlashBag */
    private $feedback;
    /** @var LoggerInterface */
    private $logger;
    /** @var Closure */
    private $mailer;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param EntityManager   $entityManager
     * @param FlashBag        $feedback
     * @param LoggerInterface $logger
     * @param Closure         $mailer
     */
    public function __construct(
        Config $config,
        EntityManager $entityManager,
        FlashBag $feedback,
        LoggerInterface $logger,
        Closure $mailer
    ) {
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->feedback = $feedback;
        $this->logger = $logger;
        $this->mailer = $mailer;
    }

    /**
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return FlashBag
     */
    protected function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param bool $debug
     *
     * @return SwiftMailer
     */
    protected function getMailer($debug = false)
    {
        $mailer = $this->mailer;

        return $mailer($debug);
    }
}
