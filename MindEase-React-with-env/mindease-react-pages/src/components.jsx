import React from "react";
import { Link, useLocation } from "react-router-dom";
import {
  ArrowLeft,
  Home as HomeIcon,
  Compass,
  Smile,
  BookOpen,
  UserRound,
} from "lucide-react";

export function PhoneShell({ children, className = "" }) {
  return <main className={`phone ${className}`}>{children}</main>;
}

export function TopBar({ title, back = true, backTo = "/home" }) {
  return (
    <header className="topbar">
      {back ? (
        <Link to={backTo} className="icon-btn" aria-label="Go back">
          <ArrowLeft size={20} />
        </Link>
      ) : (
        <span className="top-spacer" />
      )}
      <h1>{title}</h1>
      <span className="top-spacer" />
    </header>
  );
}

const navItems = [
  ["/home", HomeIcon, "Home"],
  ["/information", Compass, "Explore"],
  ["/mood", Smile, "Mood"],
  ["/journal", BookOpen, "Journal"],
  ["/progress", UserRound, "Profile"],
];

export function BottomNav() {
  const location = useLocation();
  return (
    <nav className="bottom-nav" aria-label="Main navigation">
      {navItems.map(([path, Icon, label]) => {
        const active = location.pathname === path;
        return (
          <Link className={active ? "nav-item active" : "nav-item"} to={path} key={path}>
            <Icon size={19} />
            <span>{label}</span>
          </Link>
        );
      })}
    </nav>
  );
}

export function Page({ children, nav = false, className = "" }) {
  return (
    <PhoneShell className={className}>
      {children}
      {nav && <BottomNav />}
    </PhoneShell>
  );
}

export function Pill({ children, active = false, onClick }) {
  return (
    <button type="button" onClick={onClick} className={active ? "pill active" : "pill"}>
      {children}
    </button>
  );
}

export function GradientButton({ children, onClick, type = "button", to, disabled = false }) {
  if (to) {
    return (
      <Link className={`gradient-btn wide ${disabled ? "disabled" : ""}`} to={to} aria-disabled={disabled}>
        {children}
      </Link>
    );
  }
  return (
    <button type={type} className="gradient-btn" onClick={onClick} disabled={disabled}>
      {children}
    </button>
  );
}

export function SectionTitle({ children, action }) {
  return (
    <div className="section-title">
      <h2>{children}</h2>
      {action && <span>{action}</span>}
    </div>
  );
}

export function LogoMark() {
  return (
    <div className="logo-mark" aria-label="MindEase logo">
      <div className="brain">♡</div>
    </div>
  );
}

export function InfoIcon({ children, tone = "" }) {
  return <div className={`round-icon ${tone}`}>{children}</div>;
}

export function MoodIcon({ emoji, label, selected = false, onClick }) {
  return (
    <button type="button" onClick={onClick} className={selected ? "mood-card selected" : "mood-card"}>
      <span className="mood-emoji">{emoji}</span>
      <span>{label}</span>
    </button>
  );
}
