// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Searches the fallback grader setting from the server as the reader types.
 *
 * The shape core/form-autocomplete asks of an ajax handler: transport fetches,
 * processResults shapes. The setting used to render every eligible user as an
 * option and let the module filter them in the browser, which on a site with a
 * hundred thousand teachers meant a hundred thousand options in the page
 * before anybody typed anything.
 *
 * @module     local_autograder/grader_search
 * @copyright   2026 Didactika.org
 * @author      Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Fetches the users matching what has been typed.
 *
 * @param {String} selector The enhanced select.
 * @param {String} query What the reader has typed.
 * @param {Function} success
 * @param {Function} failure
 */
export const transport = (selector, query, success, failure) => {
    Ajax.call([{
        methodname: 'local_autograder_search_graders',
        args: { query: query || '' },
    }])[0].then(success).catch(failure);
};

/**
 * Turns the response into what the module draws.
 *
 * @param {String} selector The enhanced select.
 * @param {Array} results What transport returned.
 * @returns {Array} Each as a value and a label.
 */
export const processResults = (selector, results) => {
    if (!Array.isArray(results)) {
        return [];
    }

    return results.map((user) => ({ value: user.id, label: user.name }));
};
