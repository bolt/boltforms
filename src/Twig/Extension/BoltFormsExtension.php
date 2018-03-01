<?php

namespace Bolt\Extension\Bolt\BoltForms\Twig\Extension;

use Twig_Extension as Extension;
use Twig_SimpleFunction;
use Twig_SimpleTest;

/**
 * Twig extension for BoltForms
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
class BoltFormsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $safe = ['is_safe' => ['html', 'is_safe_callback' => true]];
        $env  = ['needs_environment' => true];

        return [
            new Twig_SimpleFunction('boltforms', [BoltFormsRuntime::class, 'twigBoltForms'], $safe + $env),
            new Twig_SimpleFunction('boltforms_uploads', [BoltFormsRuntime::class, 'twigBoltFormsUploads']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests()
    {
        return array(
            new Twig_SimpleTest('rootform', [BoltFormsRuntime::class, 'twigIsRootForm']),
        );
    }

}
