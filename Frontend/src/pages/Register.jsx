import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Mail, Lock, User, Eye, EyeOff } from 'lucide-react';
import { Page, LogoMark, GradientButton } from '../components';
import { useAuth } from '../auth';

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export default function Register() {
  const navigate = useNavigate();
  const { register } = useAuth();
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' });
  const [show, setShow] = useState(false);
  const [errors, setErrors] = useState({});
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const set = (key, value) => {
    setForm(prev => ({ ...prev, [key]: value }));
    setErrors(prev => ({ ...prev, [key]: undefined }));
  };

  function validate() {
    const next = {};
    if (!form.name.trim()) next.name = 'Please enter your name.';
    if (!EMAIL_RE.test(form.email.trim())) next.email = 'Enter a valid email address.';
    if (form.password.length < 8) next.password = 'Password must be at least 8 characters.';
    if (form.password !== form.password_confirmation) next.password_confirmation = 'Passwords do not match.';
    return next;
  }

  async function submit(e) {
    e.preventDefault();
    if (busy) return;
    setError('');
    const found = validate();
    setErrors(found);
    if (Object.keys(found).length) return;

    setBusy(true);
    try {
      await register({ ...form, name: form.name.trim(), email: form.email.trim().toLowerCase() });
      navigate('/home', { replace: true });
    } catch (err) {
      const apiErrors = err.data?.errors;
      if (apiErrors) {
        const mapped = {};
        Object.entries(apiErrors).forEach(([k, v]) => { mapped[k] = Array.isArray(v) ? v[0] : v; });
        setErrors(mapped);
        if (!mapped.email && !mapped.name && !mapped.password) setError(err.message);
      } else {
        setError(err.message === err
          ? 'Cannot reach the server. Make sure the backend is running on port 8000.'
          : err.message);
      }
    } finally {
      setBusy(false);
    }
  }

  const field = (key, icon, props) => (
    <div className="field-wrap">
      <label className={errors[key] ? 'field has-error' : 'field'}>
        {icon}
        <input value={form[key]} onChange={e => set(key, e.target.value)} {...props} />
        {(key === 'password' || key === 'password_confirmation') && (
          <button type="button" className="field-action" onClick={() => setShow(s => !s)} aria-label="Toggle password visibility">
            {show ? <EyeOff size={17} /> : <Eye size={17} />}
          </button>
        )}
      </label>
      {errors[key] && <small className="field-error">{errors[key]}</small>}
    </div>
  );

  return (
    <Page sidebar={false} className="login-page">
      <form className="login-inner register-form" onSubmit={submit} noValidate>
        <h1>Create Account</h1>
        <p className="muted">Start your MindEase journey</p>
        <div className="login-illustration compact"><LogoMark /></div>
        {field('name', <User size={17} />, { placeholder: 'Full name', autoComplete: 'name' })}
        {field('email', <Mail size={17} />, { type: 'email', placeholder: 'Email address', autoComplete: 'email' })}
        {field('password', <Lock size={17} />, { type: show ? 'text' : 'password', placeholder: 'Password (8+ characters)', autoComplete: 'new-password' })}
        {field('password_confirmation', <Lock size={17} />, { type: show ? 'text' : 'password', placeholder: 'Confirm password', autoComplete: 'new-password' })}
        {error && <p className="form-error">{error}</p>}
        <GradientButton type="submit" disabled={busy}>{busy ? 'Creating...' : 'Create Account'}</GradientButton>
        <p className="login-footer">Already have an account? <Link to="/login">Log In</Link></p>
      </form>
    </Page>
  );
}
