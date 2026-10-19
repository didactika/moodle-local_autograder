/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The field of the fallback grader: a search box that asks the server for users as the administrator types, because a site can
 * have a hundred thousand teachers and none of them is sent to the page. What is chosen is written in the field that the settings
 * page saves.
 *
 * Mounted by core/react_autoinit from the data-react-component that the setting writes.
 *
 * @module     local_autograder/GraderPicker
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useEffect, useRef, useState} from 'react';
import {useDebounced} from './hooks/useDebounced';
import {searchGraders} from './services/api';
import type {Grader, GraderPickerProps} from './types';

/**
 * The field.
 *
 * @param props The name of the field, the user that is chosen now and the text.
 * @returns The field.
 */
export default function GraderPicker({name, current, currentname, strings}: GraderPickerProps) {
    const root = useRef<HTMLDivElement>(null);
    const [chosen, setChosen] = useState<Grader | null>(current > 0 ? {id: current, name: currentname} : null);
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const typed = useDebounced(query.trim(), 250);
    const [found, setFound] = useState<Grader[]>([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!open) {
            return undefined;
        }
        let latest = true;
        setLoading(true);
        searchGraders(typed).then((users) => {
            if (latest) {
                setFound(users);
                setLoading(false);
            }
            return users;
        }).catch(() => {
            if (latest) {
                setFound([]);
                setLoading(false);
            }
        });
        return () => {
            latest = false;
        };
    }, [open, typed]);

    useEffect(() => {
        const outside = (event: PointerEvent): void => {
            if (!root.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };
        document.addEventListener('pointerdown', outside, true);
        return () => document.removeEventListener('pointerdown', outside, true);
    }, []);

    const pick = (user: Grader | null): void => {
        setChosen(user);
        setOpen(false);
        setQuery('');
    };

    return <div className="lag lag-picker" ref={root}>
        <input type="hidden" name={name} value={chosen?.id ?? 0} />
        <div className="lag-picker-box">
            <button
                type="button"
                className="lag-picker-button"
                aria-haspopup="listbox"
                aria-expanded={open}
                onClick={() => setOpen(!open)}
            >
                {chosen ? chosen.name : strings.none}
            </button>
            {chosen && <button type="button" className="lag-picker-clear" aria-label={strings.clear} onClick={() => pick(null)}>
                <i className="fa-solid fa-xmark" aria-hidden="true" />
            </button>}
        </div>
        {open && <div className="lag-picker-panel">
            <input
                type="search"
                className="lag-picker-field"
                value={query}
                placeholder={strings.placeholder}
                aria-label={strings.placeholder}
                onChange={(event) => setQuery(event.target.value)}
                onKeyDown={(event) => {
                    // A search box inside the form must not submit the settings page.
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                }}
            />
            <ul className="lag-picker-list" role="listbox" aria-busy={loading}>
                {found.map((user) => <li key={user.id} role="option" aria-selected={chosen?.id === user.id}>
                    <button type="button" className="lag-picker-option" onClick={() => pick(user)}>{user.name}</button>
                </li>)}
            </ul>
            {loading && <p className="lag-picker-status" role="status">{strings.loading}</p>}
            {!loading && found.length === 0 && <p className="lag-picker-status" role="status">{strings.nomatches}</p>}
        </div>}
    </div>;
}
