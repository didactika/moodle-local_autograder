/**
 * This file is part of Moodle - https://moodle.org/
 * Moodle is free software under the GNU General Public License, version 3 or later.
 *
 * The activity types that Autograder may be switched on for: one row and one switch for each gradeable type. A switch saves the
 * moment it is flipped and says so, and goes back where it was if the server refuses.
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

    return <div className="lag">
        <p className="lag-intro">{text('modules:intro')}</p>
        <ul className="lag-types" aria-label={text('settings:modulestab')}>
            {types.map((item) => {
                const on = state[item.type];
                const label = text(on ? 'setting:modules_disable' : 'setting:modules_enable', item.name);
                return <li key={item.type} className={`lag-type${on ? ' lag-type-on' : ''}`}>
                    <img className="lag-type-icon" src={item.iconurl} alt="" width={28} height={28} />
                    <span className="lag-type-name">{item.name}</span>
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
        </ul>
    </div>;
}
