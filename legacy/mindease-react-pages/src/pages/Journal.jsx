import React, { useState } from "react";
import { PenLine, ChevronRight } from "lucide-react";
import { Page, TopBar, GradientButton } from "../components";

const initialEntries = [
  ["Feeling better today", "Apr 25, 2025", "😊"],
  ["A stressful day", "Apr 24, 2025", "😐"],
  ["Grateful for my friends", "Apr 20, 2025", "😊"],
];

export default function Journal() {
  const [entries, setEntries] = useState(initialEntries);
  const [openEditor, setOpenEditor] = useState(false);
  const [text, setText] = useState("");

  const addEntry = () => {
    if (!text.trim()) return;
    setEntries([[text.trim(), "Today", "📝"], ...entries]);
    setText("");
    setOpenEditor(false);
  };

  return (
    <Page nav>
      <TopBar title="Journal" />
      <div className="journal-intro"><PenLine size={44} /><h2>Your Safe Space</h2><p>Write your thoughts, feelings and experiences. It can help you feel lighter and more in control.</p></div>
      <GradientButton onClick={() => setOpenEditor((v) => !v)}>{openEditor ? "Close Editor" : "New Journal Entry"}</GradientButton>
      {openEditor && (
        <div className="journal-editor">
          <textarea value={text} onChange={(e) => setText(e.target.value)} placeholder="Write what is on your mind..." autoFocus />
          <button type="button" className="gradient-btn" onClick={addEntry}>Save Entry</button>
        </div>
      )}
      <h3 className="subheading">Recent Entries</h3>
      <div className="entry-list">
        {entries.map(([title, date, emoji], index) => (
          <div className="entry" key={`${title}-${index}`}><span className="entry-icon">{emoji}</span><div><b>{title}</b><small>{date}</small></div><ChevronRight size={17} /></div>
        ))}
      </div>
    </Page>
  );
}
