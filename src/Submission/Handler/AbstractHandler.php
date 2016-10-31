<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Submission\FeedbackTrait;
use Bolt\Storage\EntityManager;
use Psr\Log\LoggerInterface;
use Swift_Mailer as SwiftMailer;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * Base handler.
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
    /** @var SwiftMailer */
    private $mailer;

    /**
     * Constructor.
     *
     * @param Config          $config
     * @param EntityManager   $entityManager
     * @param FlashBag        $feedback
     * @param LoggerInterface $logger
     * @param SwiftMailer     $mailer
     */
    public function __construct(
        Config $config,
        EntityManager $entityManager,
        FlashBag $feedback,
        LoggerInterface $logger,
        SwiftMailer $mailer
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
     * @return SwiftMailer
     */
    protected function getMailer()
    {
        return $this->mailer;
    }
}
