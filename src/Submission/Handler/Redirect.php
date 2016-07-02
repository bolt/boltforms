<?php

namespace Bolt\Extension\Bolt\BoltForms\Submission\Handler;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\Config\FormConfigSection;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Helpers\Arr;
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
     * @param FormData   $formData
     */
    public function redirect(FormConfig $formConfig, FormData $formData)
    {
        $redirect = $formConfig->getFeedback()->getRedirect();
        $query = $this->getRedirectQuery($redirect, $formData);

        $response = $this->getRedirectResponse($redirect, $query);
        if ($response instanceof RedirectResponse) {
            $response->send();
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
     * @param FormConfigSection $redirect
     * @param FormData          $formData
     *
     * @return string
     */
    protected function getRedirectQuery(FormConfigSection $redirect, FormData $formData)
    {
        $query = $redirect->getQuery();

        if ($query === null) {
            return '';
        }

        $queryParams = [];
        if (is_array($query)) {
            if (Arr::isIndexedArray($redirect->getQuery())) {
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
     * @param FormConfigSection $redirect
     * @param string            $query
     *
     * @return null|RedirectResponse
     */
    protected function getRedirectResponse(FormConfigSection $redirect, $query)
    {
        if (strpos($redirect->getTarget(), 'http') === 0) {
            return new RedirectResponse($redirect->getTarget() . $query);
        } else {
            try {
                $url = '/' . ltrim($redirect->getTarget(), '/');
                $this->urlMatcher->match($url);

                return new RedirectResponse($url . $query);
            } catch (ResourceNotFoundException $e) {
                // No route found… Go home site admin, you're… um… putting a bad route in!
                return $this->valid = false;
            }
        }
    }
}
