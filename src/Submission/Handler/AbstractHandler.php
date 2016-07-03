<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Submission\FeedbackTrait;
use Bolt\Storage\EntityManager;
use Psr\Log\LoggerInterface;
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
    /** @var \Swift_Mailer */
    private $mailer;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param EntityManager   $entityManager
     * @param FlashBag        $feedback
     * @param LoggerInterface $logger
     * @param \Swift_Mailer   $mailer
     */
    public function __construct(
        Config $config,
        EntityManager $entityManager,
        FlashBag $feedback,
        LoggerInterface $logger,
        \Swift_Mailer $mailer
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
     * {@inheritdoc}
     */
    protected function getFeedback()
    {
        return $this->feedback;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * {@inheritdoc}
     */
    protected function getMailer()
    {
        return $this->mailer;
    }
}
