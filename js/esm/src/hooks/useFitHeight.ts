/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The height of the box of a table, as the design system asks: what the window leaves it, so the table and its filters fit it.
 *
 * @module     local_autograder/hooks/useFitHeight
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useLayoutEffect} from 'react';
import type {RefObject} from 'react';

/** The least height of the box of a table, in pixels (18rem), however short the window is. */
const TABLE_MIN = 288;

/** The room left under the box of a table, in pixels, for the margin of the page. */
const TABLE_ROOM = 24;

/**
 * The height of the box of a table: what is left of the window from where the box starts, less what comes under it in its card
 * (the pager), so on a computer the filters, the table and the pager fit the window without scrolling the page. It is one height
 * whatever the table holds, measured again when the window changes size or what is above it moves it.
 *
 * @param box The box of the table.
 */
export function useFitHeight(box: RefObject<HTMLElement | null>): void {
    useLayoutEffect(() => {
        const fit = (): void => {
            const element = box.current;
            if (!element) {
                return;
            }
            const rect = element.getBoundingClientRect();
            const top = rect.top + window.scrollY;
            // What comes under the box inside its card, such as the pager, wherever it sits.
            const card = element.closest('.card')?.getBoundingClientRect();
            const below = card ? Math.max(0, card.bottom - rect.bottom) : 0;
            const height = Math.max(TABLE_MIN, window.innerHeight - top - below - TABLE_ROOM);
            // On the card, so the empty state that may take the place of the table has the same height.
            ((element.closest('.card') as HTMLElement | null) ?? element).style.setProperty('--dd-table-h', height + 'px');
        };
        fit();
        // Measured again when the window changes size, or when what is above the box finishes drawing and moves it.
        const observer = new ResizeObserver(() => fit());
        observer.observe(document.body);
        const card = box.current?.closest('.card');
        if (card) {
            observer.observe(card);
        }
        window.addEventListener('resize', fit);
        return () => {
            observer.disconnect();
            window.removeEventListener('resize', fit);
        };
    }, []);
}
