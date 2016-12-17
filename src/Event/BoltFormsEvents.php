<?php

namespace Bolt\Extension\Bolt\BoltForms\Event;

/**
 * External event constants for BoltForms
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
final class BoltFormsEvents
{
    /*
     * Symfony Forms events
     */
    const PRE_SUBMIT = 'boltforms.pre_bind';
    const SUBMIT = 'boltforms.bind';
    const POST_SUBMIT = 'boltforms.post_bind';
    const PRE_SET_DATA = 'boltforms.pre_set_data';
    const POST_SET_DATA = 'boltforms.post_set_data';

    /*
     * Events in the data processor
     */
    const SUBMISSION_PRE_PROCESSOR = 'boltforms.submission_pre_processor';
    const SUBMISSION_POST_PROCESSOR = 'boltforms.submission_post_processor';

    const SUBMISSION_PROCESS_FIELDS = 'boltforms.submission_process_fields';
    const SUBMISSION_PROCESS_UPLOADS = 'boltforms.submission_process_uploads';
    const SUBMISSION_PROCESS_CONTENTTYPE = 'boltforms.submission_process_contenttype';
    const SUBMISSION_PROCESS_DATABASE = 'boltforms.submission_process_database';
    const SUBMISSION_PROCESS_EMAIL = 'boltforms.submission_process_email';
    const SUBMISSION_PROCESS_FEEDBACK = 'boltforms.submission_process_feedback';
    const SUBMISSION_PROCESS_REDIRECT = 'boltforms.submission_process_redirect';

    /*
     * Shortcut priorities for processor event priority
     */
    const PRIORITY_EARLY = 128;
    const PRIORITY_INTERNAL = 0;
    const PRIORITY_LATE = -128;

    /*
     * Custom data events
     */
    const DATA_NEXT_INCREMENT = 'boltforms.next_increment';
    const DATA_RANDOM_STRING = 'boltforms.random_string';
    const DATA_SERVER_VALUE = 'boltforms.server_value';
    const DATA_SESSION_VALUE = 'boltforms.session_value';
    const DATA_TIMESTAMP = 'boltforms.timestamp';

    const DATA_CHOICE_EVENT = 'boltforms.choice';

    /*
     * Email notification
     */
    const PRE_EMAIL_SEND = 'boltforms.pre_email_send';

    private function __construct()
    {
    }
}
