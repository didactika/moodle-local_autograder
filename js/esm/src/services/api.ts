/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * Every web service call of the plugin, in one place.
 *
 * @module     local_autograder/services/api
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {fetchOne} from '@moodle/lms/core/ajax';
import type {Grader} from '../types';

/**
 * Allow or disallow Autograder for one activity type.
 *
 * @param modname The activity type.
 * @param enabled Whether Autograder may be turned on for it.
 * @returns The state it has now.
 */
export const setModuleType = (modname: string, enabled: boolean): Promise<{modname: string; enabled: boolean}> =>
    fetchOne<{modname: string; enabled: boolean}>({methodname: 'local_autograder_set_module_type', args: {modname, enabled}});

/**
 * Search the users who could stand in as the fallback grader.
 *
 * @param query What was typed.
 * @returns The users that match.
 */
export const searchGraders = (query: string): Promise<Grader[]> =>
    fetchOne<Grader[]>({methodname: 'local_autograder_search_graders', args: {query}});

/**
 * What a failed web service call says, whether it came as an error or as the object that the web service gives.
 *
 * @param error What the call failed with.
 * @returns The message.
 */
export const describeError = (error: unknown): string => {
    if (error instanceof Error) {
        return error.message;
    }
    if (error !== null && typeof error === 'object' && 'message' in error) {
        return String((error as {message: unknown}).message);
    }
    return String(error);
};
