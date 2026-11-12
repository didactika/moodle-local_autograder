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
 * The page of the activity types: switches a type on or off the moment its toggle is flipped, and filters the rows with the chips
 * and the search box.
 *
 * Each toggle sits in a small form of its own that already carries everything the change needs, so all the switch does is submit
 * it. One listener on the page rather than one per toggle, which also covers any row the page might add later.
 *
 * @module     local_autograder/modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Shows the rows that match the chip that is active and what was typed, and the empty message when none does.
 *
 * @param {HTMLElement} page The page.
 */
const apply = (page) => {
    const active = page.querySelector('.lag-chip-active');
    const state = active ? active.dataset.filter : 'all';
    const search = page.querySelector('[data-region="search"]');
    const needle = search ? search.value.trim().toLowerCase() : '';
    let shown = 0;

    page.querySelectorAll('.lag-type').forEach((row) => {
        const inState = state === 'all' || row.dataset.state === state;
        const match = inState && (needle === '' || row.dataset.name.toLowerCase().includes(needle));
        row.hidden = !match;
        shown += match ? 1 : 0;
    });

    const empty = page.querySelector('[data-region="empty"]');
    if (empty) {
        empty.hidden = shown > 0;
    }
};

/**
 * Starts the page.
 *
 * @param {String} selector The element that holds the page.
 */
export const init = (selector) => {
    const page = document.querySelector(selector);

    if (!page) {
        return;
    }

    page.addEventListener('change', (e) => {
        const toggle = e.target.closest('input[data-submitonchange]');

        if (toggle && toggle.form) {
            toggle.form.submit();
        }
    });

    page.addEventListener('click', (e) => {
        const chip = e.target.closest('.lag-chip');

        if (!chip) {
            return;
        }

        page.querySelectorAll('.lag-chip').forEach((other) => {
            const on = other === chip;
            other.classList.toggle('lag-chip-active', on);
            other.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        apply(page);
    });

    const search = page.querySelector('[data-region="search"]');
    if (search) {
        search.addEventListener('input', () => apply(page));
    }
};
