<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_quickmail
 * @copyright  2008 onwards Louisiana State University
 * @copyright  2008 onwards Chad Mazilly, Robert Russo, Jason Peak, Dave Elliott, Adam Zapletal, Philip Cali
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_quickmail\messenger\message;

defined('MOODLE_INTERNAL') || die();

use block_quickmail\persistents\signature;

class signature_appender {

    public $body;
    public $user_id;
    public $signature_id;
    public $format;
    public $context;

    /**
     * Construct the message signature appender
     *
     * @param string  $body           the message body
     * @param int     $userid        the user id of the user sending the message
     * @param int     $signatureid   the signature id to be appended
     * @param int     $format        the message format for format_text() processing
     * @param object  $context       the context for format_text() processing
     */
    public function __construct($body, $userid, $signatureid = 0, $format = FORMAT_HTML, $context = null) {
        $this->body = $body;
        $this->user_id = $userid;
        $this->signature_id = $signatureid;
        $this->format = $format;
        $this->context = $context ?: \context_system::instance();
    }

    public static function append_user_signature_to_body($body, $userid, $signatureid = 0) {
        $appender = new self($body, $userid, $signatureid);

        return $appender->get_signature_appended_body();
    }

    public function get_signature_appended_body() {
        if (!$this->signature_id) {
            return $this->body;
        }

        if (!$signature = signature::find_user_signature_or_null($this->signature_id, $this->user_id)) {
            return $this->body;
        }

        // Apply format_text() processing to the signature using the message's format
        $formatted_signature = format_text($signature->get('signature'), $this->format, [
            'context' => $this->context,
            'trusted' => false,
            'noclean' => false
        ]);

        $this->body = $this->body . '<br><br>' . $formatted_signature;

        return $this->body;
    }

}
