<?php
namespace Bolt\Extension\Bolt\BoltForms\Submission;

use Bolt\Extension\Bolt\BoltForms\Config\FormConfig;
use Bolt\Extension\Bolt\BoltForms\FormData;
use Bolt\Helpers\Arr;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;

/**
 * Redirect handler processing functions for BoltForms
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
class RedirectHandler
{
    /** @var RedirectableUrlMatcherInterface */
    private $urlMatcher;
    /** @var boolean */
    private $valid = true;

    /**
     * @param RedirectableUrlMatcherInterface $urlMatcher
     */
    public function __construct(RedirectableUrlMatcherInterface $urlMatcher)
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
     * @param array    $redirect
     * @param FormData $formData
     *
     * @return string
     */
    protected function getRedirectQuery(array $redirect, FormData $formData)
    {
        if (!isset($redirect['query']) || empty($redirect['query'])) {
            return '';
        }

        $query = array();
        if (is_array($redirect['query'])) {
            if (Arr::isIndexedArray($redirect['query'])) {
                foreach ($redirect['query'] as $param) {
                    $query[$param] = $formData->get($param);
                }
            } else {
                foreach ($redirect['query'] as $id => $param) {
                    $query[$id] = $formData->get($param);
                }
            }
        } else {
            $param = $redirect['query'];
            $query[$param] = $formData->get($param);
        }

        return '?' . http_build_query($query);
    }

    /**
     * Get the redirect response object.
     *
     * @param array  $redirect
     * @param string $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    protected function getRedirectResponse(array $redirect, $query)
    {
        if (strpos($redirect['target'], 'http') === 0) {
            return new RedirectResponse($redirect['target'] . $query);
        } else {
            try {
                $url = '/' . ltrim($redirect['target'], '/');
                $this->urlMatcher->match($url);

                return new RedirectResponse($url . $query);
            } catch (ResourceNotFoundException $e) {
                // No route found… Go home site admin, you're… um… putting a bad route in!
                return $this->valid = false;
            }
        }
    }
}
