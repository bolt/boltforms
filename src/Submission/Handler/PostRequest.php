<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\BoltForms;
use Bolt\Extension\Bolt\BoltForms\Event\BoltFormsEvents;
use Bolt\Extension\Bolt\BoltForms\Event\ProcessorEvent;
use Bolt\Storage\Entity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Request handler processing.
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
class PostRequest
{
    /** @var RequestStack */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Handle the request. Caller must test for POST.
     *
     * @param string                   $formName
     * @param BoltForms                $boltForms
     * @param EventDispatcherInterface $dispatcher
     *
     * @return Entity\Entity|null
     */
    public function handle($formName, BoltForms $boltForms, EventDispatcherInterface $dispatcher)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request->request->has($formName)) {
            return null;
        }

        /** @var Form $form */
        $form = $boltForms->get($formName)->getForm();
        // Handle the Request object to check if the data sent is valid
        $form->handleRequest($request);

        // Test if form, as submitted, passes validation
        if (!$form->isValid()) {
            return null;
        }

        // Submitted data
        $data = $form->getData();

        $event = new ProcessorEvent($formName, $data);
        $dispatcher->dispatch(BoltFormsEvents::SUBMISSION_PRE_PROCESSOR, $event);

        return $event->getData();
    }
}
