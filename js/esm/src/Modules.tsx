/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The page of the activity types: the types that take the work of students and have a grade that a teacher gives, each with the icon
 * and the colour that Moodle gives its purpose, what Autograder reads in it and a switch that saves the moment it is flipped.
 *
 * Mounted by core/react_autoinit from the data-react-component that the page writes. It is written with the classes of Bootstrap
 * and Moodle (card, table, form-switch, activityiconcontainer) and the chips of the design system (dd-chips); the styles only adjust them inside the page.
 *
 * @module     local_autograder/Modules
 * @copyright  2026 Didactika.org
 * @author     Hector Arrechea <hectorlazaroarrechea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import {useId, useMemo, useState} from 'react';
import {createText} from './engine/text';
import {addToast} from './engine/toast';
import {describeError, setModuleType} from './services/api';
import type {ModulesProps} from './types';

type Filter = 'all' | 'on' | 'off';

/** From how many types the page offers a search. */
const SEARCH_FROM = 8;

/**
 * The page.
 *
 * @param props The types that qualify, which of them are on and the texts.
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
    const searchId = useId();

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
        <p className="text-muted mb-0">{text('modules:lead')}</p>
        <div className="dd-chips" role="group" aria-label={text('settings:modulestab')}>
            {(['all', 'on', 'off'] as Filter[]).map((name) => <button
                key={name}
                type="button"
                className="dd-chip"
                aria-pressed={filter === name}
                onClick={() => setFilter(name)}
            >
                {text(`modules:filter_${name}`)} <span className="dd-chip-count">{counts[name]}</span>
            </button>)}
        </div>
        <div className="card">
            {types.length >= SEARCH_FROM && <div className="table-toolbar">
                <div className="input-group">
                    <label className="visually-hidden sr-only" htmlFor={searchId}>{text('modules:search')}</label>
                    <input
                        id={searchId}
                        type="search"
                        className="form-control"
                        value={query}
                        placeholder={`${text('modules:search')}…`}
                        autoComplete="off"
                        onChange={(event) => setQuery(event.target.value)}
                    />
                </div>
            </div>}
            <div className="table-responsive" data-region="tablewrap">
                {shown.length === 0 && <div className="dd-empty" data-region="empty" role="status">
                    <span className="dd-tile" aria-hidden="true" dangerouslySetInnerHTML={{__html: text('pix:a/search')}} />
                    <p className="dd-empty-text">{text('modules:empty')}</p>
                </div>}
                {shown.length > 0 && <table className="table">
                    <caption className="visually-hidden sr-only">{text('settings:modulestab')}</caption>
                    <thead>
                        <tr>
                            <th scope="col">{text('modules:name')}</th>
                            <th scope="col" className="d-none d-md-table-cell">{text('modules:reads')}</th>
                            <th scope="col" className="text-end">{text('modules:state')}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {shown.map((item) => {
                            const on = state[item.type];
                            const label = text(on ? 'setting:modules_disable' : 'setting:modules_enable', item.name);
                            const reads = text(`modules:reads_${item.reads}`) || text('modules:reads_completion');
                            return <tr key={item.type}>
                                <td>
                                    <span className="d-inline-flex align-items-center gap-3">
                                        <span className={`activityiconcontainer ${item.purpose}`}>
                                            <img className="activityicon" src={item.iconurl} alt="" width={24} height={24} />
                                        </span>
                                        <span className="d-flex flex-column">
                                            <strong>{item.name}</strong>
                                            <span className="small text-muted d-md-none">{reads}</span>
                                        </span>
                                    </span>
                                </td>
                                <td className="text-muted d-none d-md-table-cell">{reads}</td>
                                <td className="text-end">
                                    <div className="form-check form-switch d-inline-block mb-0">
                                        <input
                                            type="checkbox"
                                            role="switch"
                                            className="form-check-input"
                                            checked={on}
                                            aria-label={label}
                                            title={label}
                                            disabled={busy === item.type}
                                            onChange={() => flip(item.type, item.name, !on)}
                                        />
                                    </div>
                                </td>
                            </tr>;
                        })}
                    </tbody>
                </table>}
            </div>
            <div className="table-foot text-muted small">{text('modules:count', types.length)}</div>
        </div>
        <p className="small text-muted mb-0">{text('modules:intro')}</p>
    </div>;
}
