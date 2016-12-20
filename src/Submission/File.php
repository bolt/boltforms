<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission;

use ArrayAccess;
use JsonSerializable;
use Symfony\Component\HttpFoundation\File\File as HttpFile;

/**
 * File uploaded via form.
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
class File extends HttpFile implements ArrayAccess, JsonSerializable
{
    /** @var string */
    private $baseDir;

    /**
     * Constructor.
     *
     * @param string $path
     * @param bool   $checkPath
     * @param string $baseDir
     */
    public function __construct($path, $checkPath = true, $baseDir = '')
    {
        parent::__construct($path, $checkPath);
        $this->baseDir = $baseDir;
    }

    /**
     * Return the relative path to the file.
     *
     * @return string
     */
    public function getRelativePath()
    {
        return ltrim(str_replace($this->baseDir, '', $this->getPathname()), '/');
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'file'     => $this->getRelativePath(), // images
            'filename' => $this->getRelativePath(), // image lists
            'title'    => $this->getFilename(),
            'basepath' => $this->baseDir,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return in_array($offset, ['file', 'filename', 'title', 'basepath']);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if ($offset === 'file') {
            return $this->getRelativePath();
        } elseif ($offset === 'filename') {
            return $this->getRelativePath();
        } elseif ($offset === 'title') {
            return $this->getFilename();
        } elseif ($offset === 'basepath') {
            return $this->baseDir;
        };

        throw new \BadMethodCallException(sprintf('Property "%s" does not exists in %s.', $offset, __CLASS__));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
    }
}
