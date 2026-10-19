'use client';

import { useId, useState } from 'react';

export function PasswordField({ label, name = 'password', required = false, hint }: {
  label: string; name?: string; required?: boolean; hint?: string;
}) {
  const inputId = useId();
  const [visible, setVisible] = useState(false);
  return <div className="password-field"><label htmlFor={inputId}>{label}</label>
    <span className="password-input">
      <input id={inputId} name={name} type={visible ? 'text' : 'password'} minLength={10} maxLength={128}
        required={required} autoComplete="new-password" aria-describedby={hint ? `${inputId}-hint` : undefined}/>
      <button type="button" className="secondary" aria-controls={inputId} aria-pressed={visible}
        aria-label={`${visible ? 'Sembunyikan' : 'Tampilkan'} ${label.toLowerCase()}`} onClick={() => setVisible(!visible)}>
        {visible ? 'Sembunyikan' : 'Lihat'}
      </button>
    </span>
    {hint && <small id={`${inputId}-hint`} className="muted">{hint}</small>}
  </div>;
}
