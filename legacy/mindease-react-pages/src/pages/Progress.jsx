import React from "react";
import { Page, TopBar, Pill } from "../components";

export default function Progress() {
  return (
    <Page nav>
      <TopBar title="My Progress"/>
      <div className="tabs"><Pill active>Mood</Pill><Pill>Screenings</Pill><Pill>Goals</Pill></div>
      <section className="progress-section">
        <div className="section-title"><h3>Mood Overview</h3><span>Last 7 days</span></div>
        <div className="chart">
          <svg viewBox="0 0 320 120" preserveAspectRatio="none">
            <polyline points="8,82 52,74 95,78 140,48 182,81 225,72 270,52 312,38" fill="none" stroke="currentColor" strokeWidth="4"/>
            {[["8","82"],["52","74"],["95","78"],["140","48"],["182","81"],["225","72"],["270","52"],["312","38"]].map(([x,y])=><circle key={x} cx={x} cy={y} r="4" fill="currentColor"/>)}
          </svg>
        </div>
        <div className="chart-labels"><span>Apr 19</span><span>20</span><span>21</span><span>22</span><span>23</span><span>24</span><span>25</span></div>
      </section>
      <section className="wellbeing">
        <h3>Wellbeing Score</h3>
        <div className="score-row"><div className="score-circle">72%</div><div><b>Good</b><p>You're making progress!<br/>Keep it up.</p></div></div>
      </section>
      <div className="progress-note"><b>Depression & Anxiety</b><small>Last screening: Apr 25, 2025</small><button>View Details</button></div>
    </Page>
  );
}