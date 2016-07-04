<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Swift_FileSpool as SwiftFileSpool;
use Swift_Mailer as SwiftMailer;
use Swift_TransportException as SwiftTransportException;
use Swift_Transport_SpoolTransport as SwiftTransportSpoolTransport;
use Silex\Application;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

/**
 * Email queue handler.
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
class MailerQueue
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle the processing of the SMTP queue.
     *
     * @param Event|null $event
     */
    public function flush(Event $event = null)
    {
        /** @var SwiftMailer $mailer */
        $mailer = $this->app['boltforms.mailer'];
        /** @var SwiftTransportSpoolTransport $transport */
        $transport = $mailer->getTransport();
        /** @var SwiftFileSpool $spool */
        $spool = $transport->getSpool();
        if ($event instanceof PostResponseEvent) {
            try {
                $spool->flushQueue($this->app['swiftmailer.transport']);
            } catch (SwiftTransportException $e) {
            }
        } else {
            $spool->flushQueue($this->app['swiftmailer.transport']);
        }
    }
}
