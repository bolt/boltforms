<?php
namespace Bolt\Extension\Bolt\BoltForms\Controller;

use Bolt\Extension\Bolt\BoltForms\Config;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Controller for BoltForms upload management.
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
class UploadManagement implements ControllerProviderInterface
{
    /**
     * @param \Silex\Application $app
     *
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->match('/download', [$this, 'download'])
            ->bind('BoltFormsDownload')
            ->method('GET');

        return $ctr;
    }

    public function download(Application $app, Request $request)
    {
        $fs = new Filesystem();
        $file = $request->query->get('file');
        if ($file === null) {
            return new Response('File not given', Response::HTTP_BAD_REQUEST);
        }
        /** @var Config\Config $config */
        $config = $app['boltforms.config'];
        $fullPath = $config->getUploads()->getBaseDirectory() . '/' . $file;
        if (!$fs->exists($fullPath)) {
            return new Response('File not found', Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($fullPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, basename($fullPath));

        return $response;
    }
}
