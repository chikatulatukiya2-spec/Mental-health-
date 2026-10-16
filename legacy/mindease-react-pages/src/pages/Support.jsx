import React from "react";
import { Users, Phone, UserRound, AlertTriangle } from "lucide-react";
import { Page, TopBar } from "../components";

const support = [
  [Users,"Talk to a Trusted Person","Family, friends or a mentor"],
  [Phone,"Mental Health Helpline","+260 950 123 456"],
  [UserRound,"Professional Support","Find a counsellor or therapist"],
  [AlertTriangle,"Emergency Help","If you feel unsafe or at risk"]
];

export default function Support() {
  return (
    <Page>
      <TopBar title="Get Support"/>
      <div className="support-head"><h2>You're not alone</h2><p>If you're struggling, reach out to someone you trust or a professional.</p></div>
      <div className="support-list">
        {support.map(([Icon,title,desc], i) => <div className={i===3 ? "support-item emergency" : "support-item"} key={title}>
          <div className="support-icon"><Icon size={20}/></div><div><b>{title}</b><small>{desc}</small></div><span>›</span>
        </div>)}
      </div>
      <div className="support-banner"><h3>Help is always available.</h3><p>You matter.</p><span>♡</span></div>
    </Page>
  );
}