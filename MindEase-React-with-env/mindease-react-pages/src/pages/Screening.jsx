import React, { useState } from "react";
import { Page, TopBar, GradientButton } from "../components";

const questions = [
  "Little interest or pleasure in doing things?",
  "Feeling down, depressed or hopeless?",
  "Trouble falling or staying asleep, or sleeping too much?",
  "Feeling tired or having little energy?",
  "Poor appetite or overeating?",
  "Feeling bad about yourself — or that you are a failure?",
  "Trouble concentrating on things?",
  "Moving or speaking slowly, or being unusually restless?",
  "Thoughts that you would be better off dead or hurting yourself?",
];
const answers = ["Not at all", "Several days", "More than half the days", "Nearly every day"];

export default function Screening() {
  const [q, setQ] = useState(0);
  const [selected, setSelected] = useState(Array(questions.length).fill(null));
  const [finished, setFinished] = useState(false);

  const choose = (index) => setSelected((current) => current.map((value, i) => i === q ? index : value));
  const next = () => {
    if (selected[q] === null) return;
    if (q < questions.length - 1) setQ((value) => value + 1);
    else setFinished(true);
  };

  if (finished) {
    return (
      <Page>
        <TopBar title="Screening Complete" />
        <div className="result-card">
          <div className="result-icon">✓</div>
          <h2>Thank you for completing the screening</h2>
          <p>Your answers are ready to review. This screening is not a diagnosis; consider speaking with a qualified professional about your concerns.</p>
          <GradientButton to="/progress">View Progress</GradientButton>
        </div>
      </Page>
    );
  }

  return (
    <Page>
      <TopBar title="Self-Screening" />
      <div className="screening-head"><h2>Depression Screening</h2><p>Answer based on how you have felt during the last 2 weeks.</p></div>
      <div className="question-card">
        <div className="question-number">Question {q + 1} of {questions.length}</div>
        <h3>{questions[q]}</h3>
        {answers.map((answer, i) => (
          <button type="button" key={answer} onClick={() => choose(i)} className={selected[q] === i ? "answer selected" : "answer"}>
            <span className="radio">{selected[q] === i ? "●" : ""}</span>{answer}
          </button>
        ))}
      </div>
      <div className="progress-track"><span style={{ width: `${((q + 1) / questions.length) * 100}%` }} /></div>
      <div className="progress-row"><span>{q + 1} of {questions.length}</span><span>{selected.filter((v) => v !== null).length} answered</span></div>
      <GradientButton onClick={next} disabled={selected[q] === null}>{q === questions.length - 1 ? "Finish" : "Next"}</GradientButton>
    </Page>
  );
}
