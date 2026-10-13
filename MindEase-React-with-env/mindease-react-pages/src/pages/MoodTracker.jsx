import React, { useState } from "react";
import { Check, Heart } from "lucide-react";
import { Page, TopBar, MoodIcon, GradientButton } from "../components";

const moods = [["😊","Happy"],["😌","Calm"],["😐","Okay"],["😔","Sad"],["😟","Anxious"],["😣","Overwhelmed"]];

export default function MoodTracker() {
  const [selected, setSelected] = useState("Happy");
  return (
    <Page>
      <TopBar title="Mood Tracker"/>
      <div className="mood-content">
        <h2>How are you feeling today?</h2><p className="muted">Select the mood that best describes you.</p>
        <div className="mood-grid">{moods.map(([emoji,label]) =>
          <button key={label} onClick={() => setSelected(label)} className={selected===label ? "mood-card selected" : "mood-card"}>
            <span className="mood-emoji">{emoji}</span><span>{label}</span>
            {selected===label && <Check className="mood-check" size={15}/>}
          </button>
        )}</div>
        <textarea placeholder="Add a note (optional)..."></textarea>
        <GradientButton>Save</GradientButton>
        <p className="tip"><Heart size={14}/> Small steps make big changes.</p>
      </div>
    </Page>
  );
}