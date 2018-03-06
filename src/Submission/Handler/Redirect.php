<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Helpers\Arr;
use Bolt\Storage\Entity\Entity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher;

/**
 * Redirect handler processing functions for BoltForms
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
class Redirect
{
    /** @var RedirectableUrlMatcher */
    private $urlMatcher;
    /** @var boolean */
    private $valid = true;

    /**
     * @param RedirectableUrlMatcher $urlMatcher
     */
    public function __construct(RedirectableUrlMatcher $urlMatcher)
    {
        $this->urlMatcher = $urlMatcher;
    }

    /**
     * Do a redirect.
     *
     * @param FormConfig $formConfig
     * @param Entity $formData
     * @return RedirectResponse
     */
    public function handle(FormConfig $formConfig, Entity $formData)
    {
        $response = $this->getRedirectResponse($formConfig, $formData);
        if ($response instanceof RedirectResponse) {
            return $response->send();
        }
    }

    /**
     * Refresh the current page.
     *
     * @param Request $request
     */
    public function refresh(Request $request)
    {
        $response = new RedirectResponse($request->getRequestUri());

        $response->send();
    }

    /**
     * Check if the redirect is valid.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Build a GET query if required.
     *
     * @param FormConfig $formConfig
     * @param Entity     $formData
     *
     * @return string
     */
    protected function getRedirectQuery(FormConfig $formConfig, Entity $formData)
    {
        $query = $formConfig->getFeedback()->getRedirectQuery();
        if ($query === null) {
            return '';
        }

        $queryParams = [];
        if (is_array($query)) {
            if (Arr::isIndexedArray($query)) {
                foreach ($query as $param) {
                    $queryParams[$param] = $formData->get($param);
                }
            } else {
                foreach ($query as $id => $param) {
                    $queryParams[$id] = $formData->get($param);
                }
            }
        } else {
            $param = $query;
            $queryParams[$param] = $formData->get($param);
        }

        return '?' . http_build_query($queryParams);
    }

    /**
     * Get the redirect response object.
     *
     * @param FormConfig $formConfig
     * @param Entity     $formData
     *
     * @return RedirectResponse|false
     */
    protected function getRedirectResponse(FormConfig $formConfig, Entity $formData)
    {
        $redirect = $formConfig->getFeedback()->getRedirectTarget();
        $query = $this->getRedirectQuery($formConfig, $formData);

        if (strpos($redirect, 'http') === 0) {
            return new RedirectResponse($redirect . $query);
        } else {
            try {
                $url = '/' . ltrim($redirect, '/');
                $this->urlMatcher->match($url);

                return new RedirectResponse($url . $query);
            } catch (ResourceNotFoundException $e) {
                // No route found… Go home site admin, you're… um… putting a bad route in!
                return $this->valid = false;
            }
        }
    }
}
