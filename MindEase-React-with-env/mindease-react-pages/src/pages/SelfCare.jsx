import React from "react";
import { Wind, Brain, Music, Heart, Leaf, Moon, ChevronRight } from "lucide-react";
import { Page, TopBar } from "../components";

const items = [
  [Wind,"Breathing Exercises","Calm your mind"],
  [Brain,"Mindfulness","Be present"],
  [Music,"Relaxation Music","Feel more relaxed"],
  [Heart,"Positive Affirmations","Boost your mood"],
  [Leaf,"Healthy Habits","Build a better you"],
  [Moon,"Sleep Better","Rest and recharge"]
];

export default function SelfCare() {
  return (
    <Page>
      <TopBar title="Self-Care"/>
      <div className="selfcare-head"><h2>Take care of yourself</h2><p>Small actions can make a big difference.</p></div>
      <div className="care-list">{items.map(([Icon,title,desc]) =>
        <div className="care-item" key={title}><div className="care-icon"><Icon size={20}/></div><div><b>{title}</b><small>{desc}</small></div><ChevronRight size={18}/></div>
      )}</div>
    </Page>
  );
}