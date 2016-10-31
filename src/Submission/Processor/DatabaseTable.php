<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Processor;

use Bolt\Extension\Bolt\BoltForms\Event\LifecycleEvent;
use Bolt\Extension\Bolt\BoltForms\Submission\Handler;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Submission processor direct database table transactions.
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
class DatabaseTable extends AbstractProcessor
{
    /**
     * Commit submitted data to the database if configured.
     *
     * {@inheritdoc}
     */
    public function process(LifecycleEvent $lifeEvent, $eventName, EventDispatcherInterface $dispatcher)
    {
        $formConfig = $lifeEvent->getFormConfig();
        $formData = $lifeEvent->getFormData();
        $formMeta = $lifeEvent->getFormMetaData();

        // Write to a normal database table
        if ($formConfig->getDatabase()->getTable() !== null) {
            /** @var Handler\DatabaseTable $handler */
            $handler = $this->handlers['database'];
            $handler->handle($formConfig->getDatabase()->getTable(), $formData, $formMeta);
        }
    }
}
