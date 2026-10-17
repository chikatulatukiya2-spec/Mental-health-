import React, { useState } from "react";
import { useAuth } from "./auth";
import { Link, useLocation, useNavigate } from "react-router-dom";
import {
  ArrowLeft, Home as HomeIcon, Search, Smile, BookOpen,
  UserRound, Heart, ClipboardCheck, Headphones, TrendingUp, LogOut
} from "lucide-react";

export function PhoneShell({ children, className="" }) {
  return <main className={`phone ${className}`}>{children}</main>;
}

export function TopBar({ title, back=true }) {
  return (
    <header className="topbar">
      {back ? <Link to="/home" className="icon-btn"><ArrowLeft size={20}/></Link> : <span className="top-spacer"/>}
      <h1>{title}</h1>
      <span className="top-spacer"/>
    </header>
  );
}

const navItems = [
  ["/home", HomeIcon, "Home"],
  ["/information", Search, "Explore"],
  ["/mood", Smile, "Mood"],
  ["/journal", BookOpen, "Journal"],
  ["/progress", UserRound, "Profile"],
];

export function BottomNav() {
  const location = useLocation();
  return (
    <nav className="bottom-nav">
      {navItems.map(([path, Icon, label]) => {
        const active = location.pathname === path;
        return (
          <Link className={active ? "nav-item active" : "nav-item"} to={path} key={path}>
            <Icon size={19}/><span>{label}</span>
          </Link>
        );
      })}
    </nav>
  );
}

export function Sidebar() {
  const location = useLocation();
  return (
    <aside className="sidebar">
      <Link to="/home" className="sidebar-brand">
        <span className="sidebar-logo">♡</span>
        <span className="sidebar-name">MindEase</span>
      </Link>
      <nav className="sidebar-nav">
        {navItems.map(([path, Icon, label]) => {
          const active = location.pathname === path;
          return (
            <Link className={active ? "sidebar-item active" : "sidebar-item"} to={path} key={path}>
              <Icon size={20}/><span>{label}</span>
            </Link>
          );
        })}
      </nav>
      <div className="sidebar-foot">
        <LogoutButton variant="full" />
      </div>
    </aside>
  );
}

export function Page({ children, nav=false, sidebar=true, className="" }) {
  return (
    <div className={`app-shell ${sidebar ? "with-sidebar" : ""} ${className}`.trim()}>
      {sidebar && <Sidebar />}
      <main className="app-main">
        <div className="page-content">{children}</div>
        {nav && <BottomNav />}
      </main>
    </div>
  );
}

export function Pill({ children, active=false }) {
  return <span className={active ? "pill active" : "pill"}>{children}</span>;
}

export function GradientButton({ children, onClick, type="button", to, disabled=false }) {
  if (to) return <Link to={to} className="gradient-btn wide">{children}</Link>;
  return <button type={type} className="gradient-btn" onClick={onClick} disabled={disabled}>{children}</button>;
}

export function LogoutButton({ variant="icon" }) {
  const { logout } = useAuth();
  const navigate = useNavigate();
  const [busy, setBusy] = useState(false);
  async function handleLogout() {
    if (busy) return;
    setBusy(true);
    await logout();
    navigate("/login", { replace: true });
  }
  if (variant === "full") {
    return <button type="button" className="logout-btn" onClick={handleLogout} disabled={busy}><LogOut size={16}/>{busy ? "Logging out..." : "Log Out"}</button>;
  }
  return <button type="button" className="logout-icon-btn" onClick={handleLogout} disabled={busy} aria-label="Log out" title="Log out"><LogOut size={19}/></button>;
}

export function SectionTitle({ children, action }) {
  return <div className="section-title"><h2>{children}</h2>{action && <span>{action}</span>}</div>;
}

export function LogoMark() {
  return (
    <div className="logo-mark">
      <div className="brain">♡</div>
    </div>
  );
}

export function InfoIcon({ children, tone="" }) {
  return <div className={`round-icon ${tone}`}>{children}</div>;
}

export function MoodIcon({ emoji, label, selected=false }) {
  return (
    <button className={selected ? "mood-card selected" : "mood-card"}>
      <span className="mood-emoji">{emoji}</span><span>{label}</span>
    </button>
  );
}