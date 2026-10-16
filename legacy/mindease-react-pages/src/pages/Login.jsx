import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { Mail, Lock, Eye, EyeOff } from "lucide-react";
import { Page, LogoMark, GradientButton } from "../components";

export default function Login() {
  const navigate = useNavigate();
  const [showPassword, setShowPassword] = useState(false);
  const [form, setForm] = useState({ email: "", password: "" });
  const [error, setError] = useState("");

  const handleSubmit = (event) => {
    event.preventDefault();
    setError("");
    if (!form.email.trim() || !form.password) {
      setError("Please enter your email and password.");
      return;
    }
    navigate("/home");
  };

  return (
    <Page className="login-page">
      <form className="login-inner" onSubmit={handleSubmit}>
        <h1>Welcome Back</h1>
        <p className="muted">Log in to continue your journey</p>
        <div className="login-illustration"><LogoMark /></div>

        <label className="field">
          <Mail size={17} />
          <input
            type="email"
            value={form.email}
            onChange={(e) => setForm({ ...form, email: e.target.value })}
            placeholder="Email address"
            autoComplete="email"
          />
        </label>
        <label className="field">
          <Lock size={17} />
          <input
            type={showPassword ? "text" : "password"}
            value={form.password}
            onChange={(e) => setForm({ ...form, password: e.target.value })}
            placeholder="Password"
            autoComplete="current-password"
          />
          <button type="button" className="password-toggle" onClick={() => setShowPassword((v) => !v)} aria-label={showPassword ? "Hide password" : "Show password"}>
            {showPassword ? <EyeOff size={17} /> : <Eye size={17} />}
          </button>
        </label>

        <div className="form-row">
          <label className="check"><input type="checkbox" /> Remember me</label>
          <Link to="/login">Forgot password?</Link>
        </div>

        {error && <p className="form-error" role="alert">{error}</p>}
        <GradientButton type="submit">Log In</GradientButton>
        <p className="login-footer">Don't have an account? <Link to="/home">Sign Up</Link></p>
      </form>
    </Page>
  );
}
