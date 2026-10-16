import React, { useEffect, useState } from 'react';
import { Check, Trash2, Plus, Target } from 'lucide-react';
import { Page, TopBar, GradientButton } from '../components';
import { api } from '../api';

const CATEGORIES = ['Wellbeing', 'Sleep', 'Exercise', 'Social', 'Mindfulness', 'Other'];
const EMPTY = { title: '', description: '', category: 'Wellbeing', target_date: '' };

export function GoalsPanel({ onChange }) {
  const [goals, setGoals] = useState(null);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(EMPTY);
  const [status, setStatus] = useState('');
  const [busy, setBusy] = useState(false);

  const load = () => api.goals()
    .then(r => { setGoals(r.data || []); onChange?.(r.data || []); })
    .catch(e => { setGoals([]); setStatus(e.message); });
  useEffect(() => { load(); }, []);

  async function add(e) {
    e.preventDefault();
    if (!form.title.trim() || busy) return;
    setBusy(true); setStatus('');
    try {
      await api.createGoal({ ...form, title: form.title.trim(), target_date: form.target_date || null });
      setForm(EMPTY); setOpen(false); await load();
    } catch (err) { setStatus(err.message); }
    finally { setBusy(false); }
  }

  async function toggle(goal) {
    setGoals(prev => prev.map(g => g.id === goal.id ? { ...g, completed: !g.completed } : g));
    try { await api.updateGoal(goal.id, { completed: !goal.completed }); await load(); }
    catch (err) { setStatus(err.message); load(); }
  }

  async function remove(goal) {
    if (!window.confirm('Delete this goal?')) return;
    try { await api.deleteGoal(goal.id); await load(); } catch (err) { setStatus(err.message); }
  }

  if (goals === null) return <div className="loading-state">Loading goals...</div>;

  const done = goals.filter(g => g.completed).length;
  const percent = goals.length ? Math.round((done / goals.length) * 100) : 0;

  return (
    <section className="goals-panel">
      <div className="goal-summary">
        <Target size={22} />
        <div>
          <b>{done} of {goals.length} goals completed</b>
          <div className="progress-bar"><span style={{ width: `${percent}%` }} /></div>
        </div>
      </div>

      <GradientButton onClick={() => setOpen(o => !o)}>{open ? 'Cancel' : <><Plus size={14} style={{ verticalAlign: '-2px' }} /> New Goal</>}</GradientButton>

      {open && (
        <form className="journal-form" onSubmit={add}>
          <input required maxLength={150} value={form.title} onChange={e => setForm({ ...form, title: e.target.value })} placeholder="e.g. Walk for 15 minutes daily" />
          <textarea style={{ minHeight: 70 }} maxLength={1000} value={form.description} onChange={e => setForm({ ...form, description: e.target.value })} placeholder="Details (optional)" />
          <select value={form.category} onChange={e => setForm({ ...form, category: e.target.value })}>
            {CATEGORIES.map(c => <option key={c}>{c}</option>)}
          </select>
          <input type="date" value={form.target_date} onChange={e => setForm({ ...form, target_date: e.target.value })} />
          <GradientButton type="submit" disabled={busy}>{busy ? 'Saving...' : 'Save Goal'}</GradientButton>
        </form>
      )}
      {status && <p className="form-error">{status}</p>}

      <div className="goal-list">
        {goals.length === 0
          ? <p className="muted center">No goals yet. Start with one small, realistic step.</p>
          : goals.map(g => (
            <div className={g.completed ? 'goal-item done' : 'goal-item'} key={g.id}>
              <button type="button" className="goal-check" onClick={() => toggle(g)} aria-label="Toggle complete">{g.completed && <Check size={14} />}</button>
              <div>
                <b>{g.title}</b>
                <small>{g.category}{g.target_date ? ` · by ${g.target_date}` : ''}</small>
                {g.description && <small>{g.description}</small>}
              </div>
              <button type="button" className="delete-btn" onClick={() => remove(g)} aria-label="Delete goal"><Trash2 size={15} /></button>
            </div>
          ))}
      </div>
    </section>
  );
}

export default function Goals() {
  return (
    <Page nav>
      <TopBar title="My Goals" />
      <GoalsPanel />
    </Page>
  );
}
