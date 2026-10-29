const API_BASE_URL = (
  import.meta.env.VITE_API_BASE_URL || "http://127.0.0.1:8000/api"
).replace(/\/$/, "");

export function getToken() {
  return localStorage.getItem("mindease_token");
}

export function setSession(token, user) {
  localStorage.setItem("mindease_token", token);
  if (user) localStorage.setItem("mindease_user", JSON.stringify(user));
}

export function clearSession() {
  localStorage.removeItem("mindease_token");
  localStorage.removeItem("mindease_user");
}

export function getStoredUser() {
  try {
    return JSON.parse(localStorage.getItem("mindease_user") || "null");
  } catch {
    return null;
  }
}

export function isAuthenticated() {
  return Boolean(getToken());
}

async function request(path, options = {}) {
  const headers = {
    Accept: "application/json",
    ...(options.headers || {}),
  };
  if (options.body && !headers["Content-Type"])
    headers["Content-Type"] = "application/json";
  const token = getToken();
  if (token) headers.Authorization = `Bearer ${token}`;

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...options,
    headers,
  });
  const text = await response.text();
  let data = {};
  try {
    data = text ? JSON.parse(text) : {};
  } catch {
    data = { message: text };
  }

  if (!response.ok) {
    const message =
      data.message ||
      Object.values(data.errors || {}).flat()[0] ||
      `Request failed (${response.status})`;
    const error = new Error(message);
    error.status = response.status;
    error.data = data;
    throw error;
  }
  return data;
}

export const api = {
  register: (payload) =>
    request("/auth/register", {
      method: "POST",
      body: JSON.stringify(payload),
    }),
  login: (payload) =>
    request("/auth/login", { method: "POST", body: JSON.stringify(payload) }),
  me: () => request("/auth/me"),
  logout: () => request("/auth/logout", { method: "POST" }),
  information: (topic) =>
    request(
      topic
        ? `/information?topic=${encodeURIComponent(topic)}`
        : "/information",
    ),
  moods: () => request("/moods"),
  createMood: (payload) =>
    request("/moods", { method: "POST", body: JSON.stringify(payload) }),
  journals: () => request("/journals"),
  createJournal: (payload) =>
    request("/journals", { method: "POST", body: JSON.stringify(payload) }),
  updateJournal: (id, payload) =>
    request(`/journals/${id}`, {
      method: "PUT",
      body: JSON.stringify(payload),
    }),
  deleteJournal: (id) => request(`/journals/${id}`, { method: "DELETE" }),
  screeningQuestions: () => request("/screening/questions"),
  screenings: () => request("/screenings"),
  submitScreening: (answers) =>
    request("/screenings", {
      method: "POST",
      body: JSON.stringify({ answers }),
    }),
  goals: () => request("/goals"),
  createGoal: (payload) =>
    request("/goals", { method: "POST", body: JSON.stringify(payload) }),
  updateGoal: (id, payload) =>
    request(`/goals/${id}`, { method: "PUT", body: JSON.stringify(payload) }),
  deleteGoal: (id) => request(`/goals/${id}`, { method: "DELETE" }),
  progress: () => request("/progress"),
  selfCare: () => request("/self-care"),
  support: () => request("/support"),
  aiHistory: () => request("/ai/history"),
  aiClearHistory: () => request("/ai/history", { method: "DELETE" }),
  aiChat: (message) =>
    request("/ai/chat", { method: "POST", body: JSON.stringify({ message }) }),
};

export { API_BASE_URL };
