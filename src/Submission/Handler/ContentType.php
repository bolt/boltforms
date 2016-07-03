<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Exception\StorageException;
use Bolt\Extension\Bolt\BoltForms\Config\FormMetaData;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Carbon\Carbon;

/**
 * ContentType storage.
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
class ContentType extends AbstractHandler
{
    /**
     * Write out form data to a specified ContentType record.
     *
     * @param string       $contentType
     * @param FormData     $formData
     * @param FormMetaData $formMetaData
     */
    public function handle($contentType, FormData $formData, FormMetaData $formMetaData)
    {
        try {
            $repo = $this->getEntityManager()->getRepository($contentType);
        } catch (StorageException $e) {
            $this->exception($e, false, sprintf('Invalid ContentType name `%s` specified.', $contentType));

            return;
        }

        // Get an empty record for out contenttype
        $record = $repo->getEntityBuilder()->getEntity();

        // Set a published date
        $record->setStatus('published');
        if (!$formData->has('datepublish')) {
            $record->setDatepublish(Carbon::now());
        }

        foreach ($formData->keys() as $name) {
            // Store the data array into the record
            $record->set($name, $formData->get($name, true));
        }

        // Add any meta values that are requested for 'database' use
        foreach ($formMetaData->getUsedMeta('database') as $key => $value) {
            $record->set($key, $value);
        }

        try {
            $repo->save($record);
        } catch (\Exception $e) {
            $this->exception($e, false, sprintf('An exception occurred saving submission to ContentType table `%s`', $contentType));
        }
    }

    /**
     * @deprecated
     */
    public function writeToContenType($contentType, FormData $formData, FormMetaData $formMetaData)
    {
        $this->handle($contentType, $formData, $formMetaData);
    }
}
