'use client';

import { useId, useState } from 'react';

export function PasswordField({ label, name = 'password', required = false, hint, autoComplete = 'new-password' }: {
  label: string; name?: string; required?: boolean; hint?: string; autoComplete?: 'new-password' | 'current-password';
}) {
  const inputId = useId();
  const [visible, setVisible] = useState(false);
  return <div className="password-field"><label htmlFor={inputId}>{label}</label>
    <span className="password-input">
      <input id={inputId} name={name} type={visible ? 'text' : 'password'}
        minLength={autoComplete === 'new-password' ? 10 : undefined} maxLength={autoComplete === 'new-password' ? 128 : undefined}
        required={required} autoComplete={autoComplete} aria-describedby={hint ? `${inputId}-hint` : undefined}/>
      <button type="button" className="secondary" aria-controls={inputId} aria-pressed={visible}
        aria-label={`${visible ? 'Sembunyikan' : 'Tampilkan'} ${label.toLowerCase()}`} onClick={() => setVisible(!visible)}>
        {visible ? 'Sembunyikan' : 'Lihat'}
      </button>
    </span>
    {hint && <small id={`${inputId}-hint`} className="muted">{hint}</small>}
  </div>;
}
