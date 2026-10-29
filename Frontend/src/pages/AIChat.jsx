import React, { useEffect, useRef, useState } from "react";
import { Send, Bot, ArrowLeft, Trash2, AlertTriangle, Phone, ExternalLink } from "lucide-react";
import { Link } from "react-router-dom";
import { Page } from "../components";
import { api } from "../api";

const GREETING = {
  sender: "ai",
  message: "Hi! I'm MindEase AI. How are you feeling today?",
};

export default function AIChat() {
  const [messages, setMessages] = useState([GREETING]);
  const [message, setMessage] = useState("");
  const [busy, setBusy] = useState(false);
  const [clearing, setClearing] = useState(false);
  // Set when the backend flags a message as a crisis: { resources: [...] }.
  const [crisis, setCrisis] = useState(null);
  const scrollRef = useRef(null);

  useEffect(() => {
    api
      .aiHistory()
      .then((r) => {
        if (r.data?.length) setMessages([GREETING, ...r.data]);
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    scrollRef.current?.scrollTo({
      top: scrollRef.current.scrollHeight,
      behavior: "smooth",
    });
  }, [messages, busy]);

  async function sendMessage() {
    if (!message.trim() || busy) return;
    const text = message.trim();
    setMessage("");
    setMessages((m) => [...m, { sender: "user", message: text }]);
    setBusy(true);
    try {
      const r = await api.aiChat(text);
      const data = r.data || {};
      if (data.emergency) setCrisis({ resources: data.resources || [] });
      setMessages((m) => [
        ...m,
        { sender: "ai", message: data.reply || "Thank you for sharing that." },
      ]);
    } catch (e) {
      setMessages((m) => [...m, { sender: "ai", message: e.message }]);
    } finally {
      setBusy(false);
    }
  }

  async function clearChat() {
    if (clearing) return;
    if (!window.confirm("Clear this conversation? This cannot be undone.")) return;
    setClearing(true);
    try {
      await api.aiClearHistory();
      setCrisis(null);
      setMessages([GREETING]);
    } catch {
      // Keep the conversation if clearing fails; user can retry.
    } finally {
      setClearing(false);
    }
  }

  return (
    <Page>
      <div className="ai-header">
        <Link to="/home"><ArrowLeft size={20} /></Link>
        <div>
          <h2>MindEase AI</h2>
          <span>Support Assistant</span>
        </div>
        <button
          type="button"
          className="ai-clear-btn"
          onClick={clearChat}
          disabled={clearing}
          title="Clear conversation"
        >
          <Trash2 size={14} />{clearing ? "Clearing..." : "Clear"}
        </button>
        <Bot size={25} />
      </div>

      <p className="chat-disclaimer">
        MindEase AI provides general support and information, not a diagnosis
        or emergency service.
      </p>

      {crisis && (
        <div className="crisis-banner" role="alert">
          <h3><AlertTriangle size={18} /> Immediate support is available</h3>
          <p>
            What you're feeling right now is temporary. Please reach out to one
            of these free, confidential services — someone is ready to listen.
          </p>
          {crisis.resources.map((r, i) => (
            <div className="crisis-resource" key={i}>
              <div>
                <b>{r.title}</b>
                {r.description && <small>{r.description}</small>}
              </div>
              {r.phone && (
                <a className="crisis-phone" href={`tel:${r.phone}`}>
                  <Phone size={14} /> {r.phone}
                </a>
              )}
              {r.url && (
                <a
                  className="crisis-phone"
                  href={r.url}
                  target="_blank"
                  rel="noreferrer"
                >
                  <ExternalLink size={14} /> Open
                </a>
              )}
            </div>
          ))}
          <Link to="/support" className="secondary-btn">
            View all support options
          </Link>
        </div>
      )}

      <div className="chat-area" ref={scrollRef}>
        {messages.map((m, i) => (
          <div
            key={m.id ?? i}
            className={
              m.sender === "user"
                ? "chat-message user-message"
                : "chat-message ai-message"
            }
          >
            {m.message}
          </div>
        ))}
        {busy && (
          <div className="chat-message ai-message typing">
            <span></span><span></span><span></span>
          </div>
        )}
      </div>

      <div className="chat-input">
        <input
          value={message}
          onChange={(e) => setMessage(e.target.value)}
          placeholder="Tell me how you're feeling..."
          onKeyDown={(e) => e.key === "Enter" && sendMessage()}
        />
        <button disabled={busy} onClick={sendMessage}>
          <Send size={18} />
        </button>
      </div>
    </Page>
  );
}
