/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The activity types that Autograder may be switched on for: the ones that take the work of students and have a grade that a
 * teacher gives, one row and one switch for each. A switch saves the moment it is flipped and says so, and goes back where it was
 * if the server refuses.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useMemo, useState} from 'react';
import {createText} from './engine/text';
import {addToast} from './engine/toast';
import {describeError, setModuleType} from './services/api';
import type {ModulesProps} from './types';

type Filter = 'all' | 'on' | 'off';

/** The types from which a search box is worth having. */
const SEARCH_FROM = 8;

/**
 * The page of the activity types.
 *
 * @param props What the server gives the page: the types, whether each is on, and the text.
 * @returns The page.
 */
export default function Modules({types, strings}: ModulesProps) {
    const text = useMemo(() => createText(strings), [strings]);
    const [state, setState] = useState<Record<string, boolean>>(
        () => Object.fromEntries(types.map((item) => [item.type, item.enabled]))
    );
    const [busy, setBusy] = useState('');
    const [filter, setFilter] = useState<Filter>('all');
    const [query, setQuery] = useState('');

    const flip = (type: string, name: string, enabled: boolean): void => {
        setBusy(type);
        setState((before) => ({...before, [type]: enabled}));
        setModuleType(type, enabled).then((result) => {
            setState((before) => ({...before, [type]: result.enabled}));
            return addToast(text(result.enabled ? 'setting:modules_enabled' : 'setting:modules_disabled', name), {type: 'success'});
        }).catch((error: unknown) => {
            setState((before) => ({...before, [type]: !enabled}));
            return addToast(text('modules:failed', describeError(error)), {type: 'danger'});
        }).finally(() => setBusy(''));
    };

    const onCount = types.filter((item) => state[item.type]).length;
    const counts: Record<Filter, number> = {all: types.length, on: onCount, off: types.length - onCount};
    const needle = query.trim().toLowerCase();
    const shown = types.filter((item) => {
        const on = state[item.type];
        return (filter === 'all' || (filter === 'on') === on) && (needle === '' || item.name.toLowerCase().includes(needle));
    });

    return <div className="lag">
        <div className="lag-head">
            <span className="lag-tile" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M9 16.2 4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4z" /></svg>
            </span>
            <p className="lag-lead">{text('modules:lead')}</p>
        </div>

        <div className="lag-chips" role="group" aria-label={text('settings:modulestab')}>
            {(['all', 'on', 'off'] as Filter[]).map((name) => <button
                key={name}
                type="button"
                className={`lag-chip${filter === name ? ' lag-chip-active' : ''}`}
                aria-pressed={filter === name}
                onClick={() => setFilter(name)}
            >
                {text(`modules:filter_${name}`)} <span className="lag-chip-count">{counts[name]}</span>
            </button>)}
        </div>

        <div className="lag-card">
            {types.length >= SEARCH_FROM && <div className="lag-search">
                <input
                    type="search"
                    className="lag-search-field"
                    value={query}
                    placeholder={text('modules:search')}
                    aria-label={text('modules:search')}
                    onChange={(event) => setQuery(event.target.value)}
                />
            </div>}
            <ul className="lag-types" aria-label={text('settings:modulestab')}>
                {shown.map((item) => {
                    const on = state[item.type];
                    const label = text(on ? 'setting:modules_disable' : 'setting:modules_enable', item.name);
                    return <li key={item.type} className={`lag-type${on ? ' lag-type-on' : ''}`}>
                        <span className="lag-type-tile"><img className="lag-type-icon" src={item.iconurl} alt="" width={24} height={24} /></span>
                        <span className="lag-type-text">
                            <span className="lag-type-name">{item.name}</span>
                            <span className="lag-type-reads">{text(`modules:reads_${item.reads}`) || text('modules:reads_completion')}</span>
                        </span>
                        <button
                            type="button"
                            role="switch"
                            className="lag-switch"
                            aria-checked={on}
                            aria-label={label}
                            title={label}
                            disabled={busy === item.type}
                            onClick={() => flip(item.type, item.name, !on)}
                        >
                            <span className="lag-switch-knob" />
                        </button>
                    </li>;
                })}
                {shown.length === 0 && <li className="lag-empty">{text('modules:empty')}</li>}
            </ul>
            <div className="lag-foot">{text('modules:count', types.length)}</div>
        </div>

        <p className="lag-note">{text('modules:intro')}</p>
    </div>;
}
