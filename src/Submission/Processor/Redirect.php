<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Submission\Handler;
use Pimple as Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Submission processor final redirection.
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
class Redirect extends AbstractProcessor
{
    /** @var RequestStack */
    private $requestStack;
    /** @var SessionInterface */
    private $session;

    /**
     * Constructor.
     *
     * @param Container        $handlers
     * @param RequestStack     $requestStack
     * @param SessionInterface $session
     */
    public function __construct(Container $handlers, RequestStack $requestStack, SessionInterface $session)
    {
        parent::__construct($handlers);
        $this->requestStack = $requestStack;
        $this->session = $session;
    }

    /**
     * Redirect if a redirect is set and the page exists.
     *
     * {@inheritdoc}
     *
     * @throws HttpException
     */
    public function process(LifecycleEvent $lifeEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();

        // Save our session to persist though redirects
        $this->session->save();

        if ($formConfig->getSubmission()->isAjax()) {
            return;
        }

        /** @var Handler\Redirect $handler */
        $handler = $this->handlers['redirect'];
        if ($formConfig->getFeedback()->getRedirectTarget() !== null) {
            $response = $handler->handle($formConfig, $formData);
            if ($response instanceof RedirectResponse) {
                return;
            }
        }

        // Do a get on the page as it was probably POSTed
        $request = $this->requestStack->getCurrentRequest();
        $handler->refresh($request);

        throw new HttpException(Response::HTTP_FOUND, '', null, []);
    }
}
