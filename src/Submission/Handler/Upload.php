<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\Config;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Exception\FileUploadException;
use Bolt\Extension\Bolt\BoltForms\Exception\InternalProcessorException;
use Bolt\Extension\Bolt\BoltForms\Submission\File;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * File upload handler for BoltForms
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
class Upload
{
    /** @var Config */
    private $config;
    /** @var FormConfig */
    private $formConfig;
    /** @var UploadedFile */
    private $file;

    /** @var string */
    private $fileName;
    /** @var string */
    private $baseDirName;
    /** @var string */
    private $fullPath;
    /** @var boolean */
    private $final;

    /**
     * Constructor.
     *
     * @param Config       $config
     * @param FormConfig   $formConfig
     * @param UploadedFile $file
     */
    public function __construct(Config $config, FormConfig $formConfig, UploadedFile $file)
    {
        $this->config = $config;
        $this->formConfig = $formConfig;
        $this->file = $file;
    }

    /**
     * Move the uploaded file from the temporary location to the permanent one
     * if required by configuration.
     *
     * @param FlashBagInterface $feedback
     *
     * @throws FileUploadException
     *
     * @return File
     */
    public function handle(FlashBagInterface $feedback)
    {
        $this->checkDirectories();

        $targetDir = $this->getTargetFileDirectory();
        $targetFile = $this->getTargetFileName($feedback);

        try {
            $this->file->move($targetDir, $targetFile);
        } catch (FileException $e) {
            throw new FileUploadException($e->getMessage(), $e->getMessage(), $e->getCode(), $e, false);
        }
        $this->fullPath = realpath($targetDir . DIRECTORY_SEPARATOR . $targetFile);

        return new File($this->fullPath, true, $this->config->getUploads()->getBaseDirectory());
    }

    public function __toString()
    {
        return $this->fullPath;
    }

    /**
     * Get the uploaded file object.
     *
     * @return UploadedFile
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
        return $this->file->isValid();
    }

    /**
     * Full path of the file upload.
     *
     * @return string
     */
    public function fullPath()
    {
        return (string) $this->fullPath;
    }

    /**
     * Relative path of the file upload.
     *
     * @throws InternalProcessorException
     *
     * @return string
     */
    public function relativePath()
    {
        if (!$this->config->getUploads()->isEnabled()) {
            throw new InternalProcessorException('The relative path is not valid when uploads are disabled!', null, null, false);
        }

        $realUploadPath = realpath($this->config->getUploads()->getBaseDirectory());
        if (strpos($this->fullPath, $realUploadPath) !== 0) {
            throw new InternalProcessorException('The relative path is not valid before the file is moved!', null, null, false);
        }

        return ltrim(str_replace($realUploadPath, '', $this->fullPath), '/');
    }

    /**
     * Check that the base directory, and optional sub-directory, is/are valid
     * and exist.
     *
     * @throws FileUploadException
     */
    protected function checkDirectories()
    {
        $fs = new Filesystem();
        $dir = $this->getTargetFileDirectory();

        if (!$fs->exists($dir)) {
            try {
                $fs->mkdir($dir);
            } catch (IOException $e) {
                $error = 'File upload aborted as the target directory could not be created: ' . $e->getMessage();
                $systemMessage = sprintf('[BoltForms] %s Check permissions on %s', $error, $dir);

                throw new FileUploadException($error, $systemMessage, $e->getCode(), $e, false);
            }
        }

        if (!is_writeable($dir)) {
            $error = 'File upload aborted as the target directory is not writable.';
            $systemMessage = sprintf('[BoltForms] %s Check permissions on %s', $error, $dir);

            throw new FileUploadException($error, $systemMessage, 0, null, false);
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
        $baseDir = $this->config->getUploads()->getBaseDirectory();
        $subDir = $this->formConfig->getUploads()->getSubdirectory();
        if ($subDir !== null) {
            return $this->baseDirName = $baseDir . DIRECTORY_SEPARATOR . $subDir;
        }

        return $this->baseDirName = $baseDir;
    }

    /**
     * Get the full target name for the uploaded file.
     *
     * @param FlashBagInterface $feedback
     *
     * @return string
     */
    protected function getTargetFileName(FlashBagInterface $feedback)
    {
        if ($this->final) {
            return $this->fileName;
        }

        // Create a unique filename with a simple pattern
        $originalName = $this->file->getClientOriginalName();
        $extension = $this->file->guessExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION);
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

        $feedback->add('debug', "Setting uploaded file '$originalName' to use the name '$fileName'.");
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
        if ($this->config->getUploads()->getFilenameHandling() === 'keep') {
            return '%s.%s';
        }

        $key = bin2hex(random_bytes(12));
        if ($this->config->getUploads()->getFilenameHandling() === 'prefix') {
            return "%s.$key.%s";
        }

        return "%s.%s.$key";
    }
}
