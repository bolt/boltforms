<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Submission\Processor;
use Pimple as Container;
use Psr\Log\LogLevel;

/**
 * Submission processor parent class.
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
abstract class AbstractProcessor implements ProcessorInterface
{
    /** @var Container */
    protected $handlers;
    /** @var array */
    protected $messages;

    /**
     * Constructor.
     *
     * @param Container $handlers
     */
    public function __construct(Container $handlers)
    {
        $this->handlers = $handlers;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return (array) $this->messages;
    }

    /**
     * Log a feedback message, at the required level per-target.
     *
     * @param string $message
     * @param string $feedbackLogLevel
     * @param string $systemLogLevel
     */
    protected function message($message, $feedbackLogLevel = Processor::FEEDBACK_DEBUG, $systemLogLevel = LogLevel::DEBUG)
    {
        $this->messages[] = [$message, $feedbackLogLevel, $systemLogLevel];
    }
}
