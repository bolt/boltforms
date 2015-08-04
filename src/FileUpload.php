<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Silex\Application;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * File upload handler for BoltForms
 *
 * Copyright (C) 2014 Gawain Lynch
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
 * @copyright Copyright (c) 2014, Gawain Lynch
 * @license   http://opensource.org/licenses/GPL-3.0 GNU Public License 3.0
 */
class FileUpload
{
    /** @var Application */
    private $app;
    /** @var string */
    private $formName;
    /** @var \Symfony\Component\HttpFoundation\File\File */
    private $file;
    /** @var array */
    private $config;
    /** @var string */
    private $fileName;
    /** @var string */
    private $dirName;
    /** @var string */
    private $fullPath;
    /** @var boolean */
    private $valid;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param string      $formName
     * @param File        $file
     */
    public function __construct(Application $app, $formName, File $file)
    {
        $this->app = $app;
        $this->formName = $formName;
        $this->file = $file;
        $this->fullPath = (string) $file;
        $this->fileName = basename($this->fullPath);
        $this->valid = $file->isValid();
        $this->config = $app[Extension::CONTAINER]->config;
    }

    public function __toString()
    {
        return (string) $this->file;
    }

    /**
     * Get the uploaded file object.
     *
     * @return \Symfony\Component\HttpFoundation\File\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Flag for a valid file upload.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Full path of the file upload.
     *
     * @return string
     */
    public function fullPath()
    {
        return $this->fullPath;
    }

    /**
     * Move the uploaded file from the temporary location to the permanent one
     * if required by configuration.
     *
     * @throws FileUploadException
     *
     * @return true
     */
    public function move()
    {
        $this->checkDirectories();

        $targetDir = $this->getTargetFileDirectory();
        $targetFile = $this->getTargetFileName();

        try {
            $this->file->move($targetDir, $targetFile);
            $this->fullPath = $targetDir . DIRECTORY_SEPARATOR. $targetFile;
            $this->app['logger.system']->debug('[BoltForms] Moving uploaded file to ' . $this->fullPath . '.', ['event' => 'extensions']);
        } catch (FileException $e) {
            $this->app['logger.system']->error('[BoltForms] File upload aborted as the target directory could not be writen to. Check permissions on ' . $targetDir, array('event' => 'extensions'));
            throw new FileUploadException('File upload aborted as the target directory could not be writen to.');
        }

        return true;
    }

    /**
     * Check that the base directory, and optional sub-directory, is/are valid
     * and exist.
     *
     * @throws FileUploadException
     *
     * @return boolean
     */
    protected function checkDirectories()
    {
        $fs = new Filesystem();
        $dir = $this->getTargetFileDirectory();

        if (!$fs->exists($dir)) {
            try {
                $fs->mkdir($dir);
            } catch (IOException $e) {
                $error = '[BoltForms] File upload aborted as the target directory could not be created. Check permissions on ' . $dir;
                $this->app['logger.system']->error($error, array('event' => 'extensions'));
                throw new FileUploadException($error);
            }
        }

        if (!is_writeable($dir)) {
            $error = '[BoltForms] File upload aborted as the target directory is not writable. Check permissions on ' . $dir;
            $this->app['logger.system']->error($error, array('event' => 'extensions'));
            throw new FileUploadException($error);
        }
    }

    /**
     * Get the path of the directory that will be used.
     *
     * @return string
     */
    protected function getTargetFileDirectory()
    {
        if (isset($this->config[$this->formName]['uploads']['subdirectory'])) {
            return $this->dirName = $this->config['uploads']['base_directory'] . DIRECTORY_SEPARATOR . $this->config[$this->formName]['uploads']['subdirectory'];
        }

        return $this->dirName = $this->config['uploads']['base_directory'];
    }

    /**
     * Get the full target name for the uploaded file.
     *
     * @return string
     */
    protected function getTargetFileName()
    {
        if ($this->fileName !== null) {
            return $this->fileName;
        }

        // Create a unique filename with a simple pattern
        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->guessExtension() ? : pathinfo($originalName, PATHINFO_EXTENSION);

        $fileName = sprintf(
            $this->getTargetFileNamePattern(),
            pathinfo($originalName, PATHINFO_FILENAME),
            $extension
        );
        $this->app['logger.system']->debug("[BoltForms] Setting uploaded file '$originalName' to use the name '$fileName'.", ['event' => 'extensions']);

        return $this->fileName = $fileName;
    }

    /**
     * Calculate the naming pattern for the new file name.
     *
     * @return string
     */
    protected function getTargetFileNamePattern()
    {
        if ($this->config['uploads']['filename_handling'] === 'keep') {
            return '%s.%s';
        }

        $key = $this->app['randomgenerator']->generateString(12, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890');
        if ($this->config['uploads']['filename_handling'] === 'prefix') {
            return "%s.$key.%s";
        } else {
            return "%s.%s.$key";
        }
    }
}
