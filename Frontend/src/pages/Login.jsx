import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Mail, Lock, Eye, EyeOff } from "lucide-react";
import { Page, LogoMark, GradientButton } from "../components";
import { useAuth } from "../auth";
export default function Login() {
  const nav = useNavigate(),
    { login } = useAuth();
  const [email, setEmail] = useState(""),
    [password, setPassword] = useState(""),
    [show, setShow] = useState(false),
    [error, setError] = useState(""),
    [busy, setBusy] = useState(false);
  async function submit(e) {
    e.preventDefault();
    setError("");
    setBusy(true);
    try {
      await login(email.trim(), password);
      nav("/home", { replace: true });
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  }
  return (
    <Page sidebar={false} className="login-page">
      <form className="login-inner" onSubmit={submit}>
        <h1>Welcome Back</h1>
        <p className="muted">Log in to continue your journey</p>
        <div className="login-illustration">
          <LogoMark />
        </div>
        <label className="field">
          <Mail size={17} />
          <input
            type="email"
            required
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="Email address"
          />
        </label>
        <label className="field">
          <Lock size={17} />
          <input
            required
            minLength={8}
            type={show ? "text" : "password"}
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder="Password"
          />
          <button
            type="button"
            className="field-action"
            onClick={() => setShow(!show)}
          >
            {show ? <EyeOff size={17} /> : <Eye size={17} />}
          </button>
        </label>
        {error && <p className="form-error">{error}</p>}
        <GradientButton type="submit">
          {busy ? "Logging in..." : "Log In"}
        </GradientButton>
        <p className="login-footer">
          Don't have an account? <Link to="/register">Sign Up</Link>
        </p>
      </form>
    </Page>
  );
}
