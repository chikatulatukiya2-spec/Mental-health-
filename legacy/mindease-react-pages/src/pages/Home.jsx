import React, { useState } from "react";
import { Link } from "react-router-dom";
import {
  Search,
  Sun,
  UserCircle,
  Brain,
  HeartPulse,
  BookOpen,
  Headphones,
  ShieldCheck,
  Bot,
} from "lucide-react";
import { Page, SectionTitle, InfoIcon } from "../components";

const categories = [
  [Brain, "blue", "Depression", "/information"],
  [HeartPulse, "green", "Anxiety", "/information"],
  [HeartPulse, "pink", "Self-Care", "/self-care"],
  [BookOpen, "purple", "Education", "/information"],
  [ShieldCheck, "cyan", "Resources", "/support"],
  [Headphones, "blue", "Support", "/support"],
];

export default function Home() {
  const [search, setSearch] = useState("");
  const visibleCategories = categories.filter(([, , label]) =>
    label.toLowerCase().includes(search.trim().toLowerCase())
  );

  return (
    <Page nav>
      <div className="home-content">
        <div className="home-header">
          <div>
            <p className="muted">Good morning,</p>
            <h1>Tukiya <Sun size={17} /></h1>
          </div>
          <Link to="/progress" aria-label="Open profile">
            <UserCircle size={39} className="profile-circle" />
          </Link>
        </div>

        <label className="search">
          <Search size={17} />
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search for help, articles, or tools..."
            aria-label="Search MindEase"
          />
        </label>

        <div className="hero-card">
          <div>
            <h2>You are not alone</h2>
            <p>It's okay to ask for help.<br />Your mental health matters.</p>
            <Link to="/support" className="hero-action">Talk to Someone <span>›</span></Link>
          </div>
          <div className="hero-orb">♡</div>
        </div>

        <div className="ai-card">
          <div className="ai-icon"><Bot size={24} /></div>
          <div className="ai-content">
            <h3>MindEase AI Assistant</h3>
            <p>Get supportive guidance and mental health information in a private chat.</p>
            <Link to="/ai-chat" className="ai-button">Start Chat</Link>
          </div>
        </div>

        <SectionTitle>Explore Categories</SectionTitle>
        <div className="category-grid">
          {visibleCategories.map(([Icon, tone, label, path]) => (
            <Link to={path} className="category-item" key={label} aria-label={label}>
              <InfoIcon tone={tone}><Icon size={19} /></InfoIcon>
              <span>{label}</span>
            </Link>
          ))}
        </div>
        {visibleCategories.length === 0 && <p className="empty-state">No matching resources found.</p>}
      </div>
    </Page>
  );
}
