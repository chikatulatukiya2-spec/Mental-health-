import React, { createContext, useContext, useEffect, useState } from "react";
import { Navigate } from "react-router-dom";
import { api, clearSession, getStoredUser, getToken, setSession } from "./api";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(getStoredUser());
  const [loading, setLoading] = useState(Boolean(getToken()));

  useEffect(() => {
    if (!getToken()) {
      setLoading(false);
      return;
    }
    api
      .me()
      .then(({ user }) => setUser(user))
      .catch(() => {
        clearSession();
        setUser(null);
      })
      .finally(() => setLoading(false));
  }, []);

  async function login(email, password) {
    const data = await api.login({ email, password });
    setSession(data.token, data.user);
    setUser(data.user);
    return data;
  }

  async function register(payload) {
    const data = await api.register(payload);
    setSession(data.token, data.user);
    setUser(data.user);
    return data;
  }

  async function logout() {
    try {
      if (getToken()) await api.logout();
    } catch {
      /* token may already be expired; still sign out locally */
    } finally {
      clearSession();
      setUser(null);
    }
  }

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        isAuthenticated: Boolean(user && getToken()),
        login,
        register,
        logout,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}

export function Protected({ children }) {
  const { loading, isAuthenticated } = useAuth();
  if (loading)
    return (
      <div className="phone">
        <div className="loading-state">Loading MindEase...</div>
      </div>
    );
  if (!isAuthenticated) return <Navigate to="/login" replace />;
  return children;
}

export function GuestOnly({ children }) {
  const { loading, isAuthenticated } = useAuth();
  if (loading)
    return (
      <div className="phone">
        <div className="loading-state">Loading MindEase...</div>
      </div>
    );
  if (isAuthenticated) return <Navigate to="/home" replace />;
  return children;
}
