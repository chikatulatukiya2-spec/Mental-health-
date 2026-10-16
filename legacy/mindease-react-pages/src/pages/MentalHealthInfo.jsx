import React, { useState } from "react";
import { Brain, CheckCircle2 } from "lucide-react";
import { Page, TopBar, Pill, GradientButton } from "../components";

const content = {
  Depression: {
    title: "What is Depression?",
    text: "Depression is a common but serious mental health condition that can affect how you feel, think, and act. Symptoms can persist and may make everyday activities difficult.",
    symptoms: ["Persistent sadness or low mood", "Loss of interest in activities", "Changes in sleep or appetite", "Low energy and fatigue", "Difficulty concentrating"],
  },
  Anxiety: {
    title: "What is Anxiety?",
    text: "Anxiety can involve persistent worry, fear, or nervousness. Occasional anxiety is common, but ongoing symptoms can interfere with daily life and wellbeing.",
    symptoms: ["Excessive worry", "Feeling restless or on edge", "Difficulty concentrating", "Muscle tension", "Sleep difficulties"],
  },
  Wellbeing: {
    title: "Mental Wellbeing",
    text: "Mental wellbeing includes how you manage everyday stress, connect with others, and maintain a sense of purpose. Small, consistent habits can support wellbeing.",
    symptoms: ["Healthy sleep routine", "Regular movement", "Supportive relationships", "Time for rest", "Healthy coping strategies"],
  },
};

export default function MentalHealthInfo() {
  const [tab, setTab] = useState("Depression");
  const active = content[tab];
  return (
    <Page>
      <TopBar title="Mental Health Information" />
      <div className="tabs">
        {Object.keys(content).map((name) => <Pill key={name} active={tab === name} onClick={() => setTab(name)}>{name}</Pill>)}
      </div>
      <div className="info-banner"><div className="head-orb"><Brain size={42} /></div></div>
      <article className="article">
        <h2>{active.title}</h2>
        <p>{active.text}</p>
        <h3>{tab === "Wellbeing" ? "Helpful habits" : "Common experiences"}</h3>
        {active.symptoms.map((x) => <div className="symptom" key={x}><CheckCircle2 size={16} />{x}</div>)}
      </article>
      <GradientButton to="/screening">Take a Screening</GradientButton>
    </Page>
  );
}
