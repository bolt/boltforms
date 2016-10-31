<?php

namespace Bolt\Extension\Bolt\BoltForms\Exception;

/**
 * File upload exceptions for BoltForms.
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
class FileUploadException extends InternalProcessorException implements BoltFormsException
{
    /** @var string */
    private $systemMessage;

    /**
     * Constructor.
     *
     * @param string          $message
     * @param string          $systemMessage
     * @param int             $code
     * @param \Exception|null $previous
     * @param bool            $abort
     */
    public function __construct($message, $systemMessage, $code = 0, \Exception $previous = null, $abort)
    {
        $code = $previous ? $previous->getCode() : 1;
        parent::__construct($message, $code, $previous, $abort);
        $this->systemMessage = $systemMessage;
    }

    /**
     * @return string
     */
    public function getSystemMessage()
    {
        return $this->systemMessage;
    }
}
