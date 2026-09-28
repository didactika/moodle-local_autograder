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
 * Switches an activity type on or off the moment its toggle is flipped.
 *
 * Each toggle on the activity types page sits in a small form of its own that
 * already carries everything the change needs, so all this does is submit it.
 * One listener on the table rather than one per toggle, which also covers any
 * row the page might add later.
 *
 * @module     local_autograder/modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Submits a toggle's form as soon as the toggle changes.
 *
 * @param {String} selector The table the toggles are in.
 */
export const init = (selector) => {
    const table = document.querySelector(selector);

    if (!table) {
        return;
    }

    table.addEventListener('change', (e) => {
        const toggle = e.target.closest('input[data-submitonchange]');

        if (toggle && toggle.form) {
            toggle.form.submit();
        }
    });
};
