import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { Page, TopBar, GradientButton } from '../components';
import { api } from '../api';

const DEFAULT_ANSWERS = ['Not at all', 'Several days', 'More than half the days', 'Nearly every day'];

export default function Screening() {
  const [meta, setMeta] = useState(null);
  const [answers, setAnswers] = useState([]);
  const [index, setIndex] = useState(0);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  function load() {
    setError('');
    api.screeningQuestions()
      .then(r => {
        setMeta(r);
        setAnswers(Array((r.questions || []).length).fill(null));
        setIndex(0);
        setResult(null);
      })
      .catch(e => setError(e.message === 'Failed to fetch' ? 'Cannot reach the server. Is the backend running?' : e.message));
  }
  useEffect(load, []);

  if (result) {
    return (
      <Page nav>
        <TopBar title="Screening Result" />
        <div className="question-card result-card">
          <p className="muted">Your score</p>
          <div className="score-badge">{result.score}<small>/ 27</small></div>
          <h2>{result.result_label}</h2>
          <p className="muted">{result.disclaimer || 'This screening is not a diagnosis. Consider discussing concerns with a qualified professional.'}</p>
        </div>
        {result.self_harm_flag && (
          <div className="form-error crisis">
            <b>You may need support right now.</b> You indicated thoughts of harming yourself. Please contact a trusted person,
            a qualified professional, or your local emergency service immediately. <Link to="/support">Open support options</Link>
          </div>
        )}
        <div className="stack-buttons">
          <GradientButton to="/goals">Set a Goal</GradientButton>
          <GradientButton to="/progress">View Progress</GradientButton>
          <button type="button" className="secondary-btn" onClick={load}>Retake Screening</button>
        </div>
      </Page>
    );
  }

  if (!meta) {
    return (
      <Page><TopBar title="Self-Screening" />
        <div className="loading-state">{error || 'Loading questions...'}
          {error && <div style={{ marginTop: 14 }}><button type="button" className="secondary-btn" onClick={load}>Try again</button></div>}
        </div>
      </Page>
    );
  }

  const questions = meta.questions || [];
  const options = (meta.answers || []).map(a => a.label);
  const labels = options.length ? options : DEFAULT_ANSWERS;
  const current = questions[index];
  const isLast = index === questions.length - 1;
  const percent = Math.round(((index + 1) / questions.length) * 100);

  function choose(value) {
    setAnswers(prev => prev.map((a, i) => (i === index ? value : a)));
    setError('');
  }

  async function next() {
    if (answers[index] === null) return setError('Please select an answer to continue.');
    setError('');
    if (!isLast) return setIndex(index + 1);
    setBusy(true);
    try { setResult(await api.submitScreening(answers)); }
    catch (e) { setError(e.message); }
    finally { setBusy(false); }
  }

  return (
    <Page>
      <TopBar title="Self-Screening" />
      <div className="screening-head">
        <h2>{meta.title || 'Depression Screening'}</h2>
        <p>{meta.instruction || 'Answer based on how you have felt during the last 2 weeks.'}</p>
      </div>
      <div className="progress-bar"><span style={{ width: `${percent}%` }} /></div>
      <div className="question-card">
        <div className="question-number">Question {index + 1} of {questions.length}</div>
        <h3>{current.text}</h3>
        {labels.map((label, i) => (
          <button type="button" key={label} onClick={() => choose(i)} className={answers[index] === i ? 'answer selected' : 'answer'}>
            <span className="radio">{answers[index] === i ? '●' : ''}</span>{label}
          </button>
        ))}
      </div>
      {error && <p className="form-error">{error}</p>}
      <div className="progress-row"><span>{index + 1} of {questions.length}</span><span>{percent}%</span></div>
      <div className="nav-buttons">
        {index > 0 && <button type="button" className="secondary-btn" onClick={() => { setError(''); setIndex(index - 1); }}>Back</button>}
        <GradientButton onClick={next} disabled={busy}>{busy ? 'Submitting...' : isLast ? 'Finish' : 'Next'}</GradientButton>
      </div>
      <p className="muted disclaimer">{meta.disclaimer}</p>
    </Page>
  );
}
