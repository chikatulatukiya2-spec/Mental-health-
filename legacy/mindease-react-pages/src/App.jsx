import React from "react";
import { Routes, Route, Navigate } from "react-router-dom";
import Welcome from "./pages/Welcome";
import Login from "./pages/Login";
import Home from "./pages/Home";
import MentalHealthInfo from "./pages/MentalHealthInfo";
import Screening from "./pages/Screening";
import MoodTracker from "./pages/MoodTracker";
import Journal from "./pages/Journal";
import SelfCare from "./pages/SelfCare";
import Progress from "./pages/Progress";
import Support from "./pages/Support";
import AIChat from "./pages/AIChat";


export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Welcome />} />
      <Route path="/login" element={<Login />} />
      <Route path="/home" element={<Home />} />
      <Route path="/information" element={<MentalHealthInfo />} />
      <Route path="/screening" element={<Screening />} />
      <Route path="/mood" element={<MoodTracker />} />
      <Route path="/journal" element={<Journal />} />
      <Route path="/self-care" element={<SelfCare />} />
      <Route path="/progress" element={<Progress />} />
      <Route path="/support" element={<Support />} />
      <Route path="/ai-chat" element={<AIChat />} />
      <Route path="*" element={<Navigate to="/" replace />} />
       
    </Routes>
  );
}