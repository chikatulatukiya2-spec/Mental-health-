import React, { useState } from "react";
import { Send, Bot, ArrowLeft } from "lucide-react";
import { Link } from "react-router-dom";
import { Page } from "../components";

export default function AIChat() {
  const [message, setMessage] = useState("");
  const [messages, setMessages] = useState([
    { sender: "ai", text: "Hi! I'm MindEase AI. How are you feeling today?" },
  ]);

  const sendMessage = () => {
    const trimmed = message.trim();
    if (!trimmed) return;
    setMessages((current) => [
      ...current,
      { sender: "user", text: trimmed },
      { sender: "ai", text: "Thank you for sharing that. I'm here to listen. Would you like to tell me more about how you're feeling?" },
    ]);
    setMessage("");
  };

  return (
    <Page className="ai-page">
      <div className="ai-header">
        <Link to="/home" aria-label="Back to home"><ArrowLeft size={20} /></Link>
        <div><h2>MindEase AI</h2><span>Support Assistant</span></div>
        <Bot size={25} />
      </div>

      <div className="chat-area" aria-live="polite">
        {messages.map((msg, index) => (
          <div key={`${msg.sender}-${index}`} className={msg.sender === "user" ? "chat-message user-message" : "chat-message ai-message"}>
            {msg.text}
          </div>
        ))}
      </div>

      <form className="chat-input" onSubmit={(e) => { e.preventDefault(); sendMessage(); }}>
        <input value={message} onChange={(e) => setMessage(e.target.value)} placeholder="Tell me how you're feeling..." aria-label="Message MindEase AI" />
        <button type="submit" aria-label="Send message"><Send size={18} /></button>
      </form>
      <p className="ai-disclaimer">MindEase AI provides general support and information, not a diagnosis or emergency service.</p>
    </Page>
  );
}
