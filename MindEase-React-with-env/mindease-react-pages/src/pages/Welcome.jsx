import React from "react";
import { Link } from "react-router-dom";
import { PhoneShell, LogoMark } from "../components";

export default function Welcome() {
  return (
    <PhoneShell className="welcome-page">
      <div className="welcome-art">
        <LogoMark />
        <h1>MindEase</h1>
        <p>A healthier mind<br/>for a brighter tomorrow</p>
      </div>
      <div className="welcome-mountains">
        <span></span><span></span><span></span>
      </div>
      <div className="welcome-bottom">
        <Link to="/home" className="gradient-btn wide">Get Started</Link>
        <p>Already have an account? <Link to="/login">Login</Link></p>
      </div>
    </PhoneShell>
  );
}