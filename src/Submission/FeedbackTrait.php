<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;

/**
 * Submission service exception handling and reporting.
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
trait FeedbackTrait
{
    /**
     * Log a feedback message, at the required level per-target.
     *
     * @param string $message
     * @param string $feedbackLogLevel
     * @param string $systemLogLevel
     */
    protected function message($message, $feedbackLogLevel = Processor::FEEDBACK_DEBUG, $systemLogLevel = LogLevel::DEBUG)
    {
        $this->getFeedback()->add($feedbackLogLevel, $message);
        $this->getLogger()->log($systemLogLevel, $message, ['event' => 'extensions']);
    }

    /**
     * Log and optionally re-throw an exception.
     *
     * @param \Exception $e
     * @param bool       $rethrow
     * @param string     $messagePrefix
     *
     * @throws \Exception
     */
    protected function exception(\Exception $e, $rethrow = true, $messagePrefix = 'An exception has occurred during form processing:')
    {
        $message = sprintf('%s%s%s', $messagePrefix, "\n", $e->getMessage());
        $this->getFeedback()->add('debug', $message);
        $this->getLogger()->critical('[BoltForms]: ' . $message, ['event' => 'exception', 'exception' => $e]);

        if ($rethrow) {
            throw $e;
        }
    }

    /**
     * Return the BoltForms feedback service.
     *
     * @return FlashBag
     */
    abstract protected function getFeedback();

    /**
     * Return the Bolt system logger service.
     *
     * @return LoggerInterface
     */
    abstract protected function getLogger();
}
