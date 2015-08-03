<?php

namespace Bolt\Extension\Bolt\BoltForms;

use Silex\Application;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    /** @var \Symfony\Component\HttpFoundation\File\File */
    private $file;
    /** @var boolean */
    private $validDirectories = false;
    /** @var array */
    private $config;
    /** @var string */
    private $fileName;

    /**
     * Constructor.
     *
     * @param File $file
     */
    public function __construct(Application $app, File $file)
    {
        $this->app = $app;
        $this->file = $file;
        $this->config = $app[Extension::CONTAINER]->config;
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
     * Handle the upload, um, handling.
     *
     * @param string $formname
     *
     * @return true
     */
    public function handleUpload($formname)
    {
        if (!$this->checkDirectories($formname)) {
            return false;
        }

        try {
            $targetDir = $this->getTargetFileDirectory($formname);
            $targetFile = $this->getTargetFileName();
            $this->systemLogger->debug('[BoltForms] Moving uloaded file to use ' . $targetFile . DIRECTORY_SEPARATOR. $targetFile . '.', ['event' => 'extensions']);

            $this->file->move($targetDir, $targetFile);
        } catch (FileException $e) {
            $this->app['logger.system']->error('[BoltForms] File upload aborted as the target directory could not be writen to. Check permissions on ' . $targetDir, array('event' => 'extensions'));

            return false;
        }

        return true;
    }

    /**
     * Check that the base, and optional sub-, directories are valid and exist.
     *
     * @param string $formname
     *
     * @return boolean
     */
    public function checkDirectories($formname)
    {
        $fs = new Filesystem();
        $dir = $this->getTargetFileDirectory($formname);

        if (!$fs->exists($dir)) {
            try {
                $fs->mkdir($dir);
            } catch (IOException $e) {
                $this->app['logger.system']->error('[BoltForms] File upload aborted as the target directory could not be created. Check permissions on ' . $dir, array('event' => 'extensions'));

                return $this->validDirectories = false;
            }
        }

        if (!is_writeable($dir)) {
            $this->app['logger.system']->error('[BoltForms] File upload aborted as the target directory is not writable. Check permissions on ' . $dir, array('event' => 'extensions'));

            return $this->validDirectories = false;
        }

        return $this->validDirectories = true;
    }

    /**
     * Get the path of the directory that will be used.
     *
     * @param string $formname
     *
     * @return string
     */
    protected function getTargetFileDirectory($formname)
    {
        if (isset($this->config[$formname]['uploads']['subdirectory'])) {
            return $this->config['uploads']['base_directory'] . DIRECTORY_SEPARATOR . $this->config[$formname]['uploads']['subdirectory'];
        }

        return $this->config['uploads']['base_directory'];
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
        $this->systemLogger->debug("[BoltForms] Setting uloaded file '$originalName' to use the name '$fileName'.", ['event' => 'extensions']);

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