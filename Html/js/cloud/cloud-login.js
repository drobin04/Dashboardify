import { dashboardifyCloudConfig } from "./config.js";
import { GoogleAuthProvider, GOOGLE_OAUTH_SESSION_KEY } from "./google-auth.js";

function hasValidStoredSession() {
  try {
    const raw = sessionStorage.getItem(GOOGLE_OAUTH_SESSION_KEY);
    if (!raw) return false;
    const data = JSON.parse(raw);
    return Boolean(
      data.accessToken && data.expiresAtMs && Date.now() < data.expiresAtMs
    );
  } catch {
    return false;
  }
}

window.addEventListener("DOMContentLoaded", () => {
  const errEl = document.getElementById("loginError");
  const btn = document.getElementById("btnGoogleSignIn");
  const continueRow = document.getElementById("continueSessionRow");
  if (continueRow && hasValidStoredSession()) {
    continueRow.style.display = "block";
    document.getElementById("btnContinueDashboards").addEventListener("click", () => {
      window.location.href = "cloud.html";
    });
  }

  btn.addEventListener("click", async () => {
    errEl.textContent = "";
    btn.disabled = true;
    try {
      const auth = new GoogleAuthProvider(dashboardifyCloudConfig);
      await auth.init();
      await auth.signIn();
      window.location.href = "cloud.html";
    } catch (e) {
      errEl.textContent = e.message || String(e);
    } finally {
      btn.disabled = false;
    }
  });
});
