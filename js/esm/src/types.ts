/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The types that the parts of the plugin share.
 *
 * @module     local_autograder/types
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** Looks a text of the page up, putting the value in the place that the text marks. */
export type Text = (name: string, value?: string | number | Record<string, string | number>) => string;

/** One activity type that Autograder may be switched on for. */
export interface ModuleType {
    type: string;
    name: string;
    iconurl: string;
    enabled: boolean;
}

export interface ModulesProps {
    types: ModuleType[];
    strings: Record<string, string>;
}

/** A user that the fallback grader can be. */
export interface Grader {
    id: number;
    name: string;
}

export interface GraderPickerProps {
    /** Name of the field that the form submits. */
    name: string;
    current: number;
    currentname: string;
    strings: Record<string, string>;
}
