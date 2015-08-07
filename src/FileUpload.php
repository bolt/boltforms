<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Silex\Application;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * File upload handler for BoltForms
 *
 * Copyright (C) 2014-2015 Gawain Lynch
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
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile */
    private $file;
    /** @var array */
    private $config;
    /** @var string */
    private $fileName;
    /** @var string */
    private $baseDirName;
    /** @var string */
    private $fullPath;
    /** @var boolean */
    private $valid;
    /** @var boolean */
    private $final;

    /**
     * Constructor.
     *
     * @param Application $app
     * @param string      $formName
     * @param File        $file
     */
    public function __construct(Application $app, $formName, UploadedFile $file)
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
        return $this->fullPath;
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
     * Relative path of the file upload.
     *
     * @return string
     */
    public function relativePath()
    {
        if (!$this->config['uploads']['enabled']) {
            throw new \RuntimeException('The relative path is not valid when uploads are disabled!');
        }

        if (strpos($this->fullPath, $this->config['uploads']['base_directory']) !== 0) {
            throw new \RuntimeException('The relative path is not valid before the file is moved!');
        }

        return ltrim(str_replace($this->config['uploads']['base_directory'], '', $this->fullPath), '/');
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
            $this->fullPath = realpath($targetDir . DIRECTORY_SEPARATOR . $targetFile);
            $this->app['logger.system']->debug('[BoltForms] Moving uploaded file to ' . $this->fullPath . '.', array('event' => 'extensions'));
        } catch (FileException $e) {
            $error = 'File upload aborted as the target directory could not be writen to.';
            $this->app['logger.system']->error('[BoltForms] ' . $error . ' Check permissions on ' . $targetDir, array('event' => 'extensions'));
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
                $error = 'File upload aborted as the target directory could not be created.';
                $this->app['logger.system']->error('[BoltForms] ' . error . ' Check permissions on ' . $dir, array('event' => 'extensions'));
                throw new FileUploadException($error);
            }
        }

        if (!is_writeable($dir)) {
            $error = 'File upload aborted as the target directory is not writable.';
            $this->app['logger.system']->error('[BoltForms] ' . $error . ' Check permissions on ' . $dir, array('event' => 'extensions'));
            throw new FileUploadException($error);
        }

        $this->baseDirName = realpath($dir);
    }

    /**
     * Get the path of the directory that will be used.
     *
     * @return string
     */
    protected function getTargetFileDirectory()
    {
        if ($this->baseDirName !== null) {
            return $this->baseDirName;
        }

        if (isset($this->config[$this->formName]['uploads']['subdirectory'])) {
            return $this->baseDirName = $this->config['uploads']['base_directory'] . DIRECTORY_SEPARATOR . $this->config[$this->formName]['uploads']['subdirectory'];
        }

        return $this->baseDirName = $this->config['uploads']['base_directory'];
    }

    /**
     * Get the full target name for the uploaded file.
     *
     * @return string
     */
    protected function getTargetFileName()
    {
        if ($this->final) {
            return $this->fileName;
        }

        // Create a unique filename with a simple pattern
        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->guessExtension() ? : pathinfo($originalName, PATHINFO_EXTENSION);
        $pattern = $this->getTargetFileNamePattern();
        $fileName = sprintf(
            $pattern,
            pathinfo($originalName, PATHINFO_FILENAME),
            $extension
        );
        $this->fullPath = $this->getTargetFileDirectory() . DIRECTORY_SEPARATOR . $fileName;

        $i = 1;
        while (file_exists($this->fullPath)) {
            $fileName = sprintf(
                $pattern,
                pathinfo($originalName, PATHINFO_FILENAME) . "($i)",
                $extension
            );
            $this->fullPath = $this->getTargetFileDirectory() . DIRECTORY_SEPARATOR . $fileName;
            $i++;
        }

        $this->app['logger.system']->debug("[BoltForms] Setting uploaded file '$originalName' to use the name '$fileName'.", array('event' => 'extensions'));
        $this->final = true;

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
