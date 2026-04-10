import { dashboardifyCloudConfig } from "./config.js";
import {
  GoogleAuthProvider,
  hasValidPersistedOAuthSession
} from "./google-auth.js";

window.addEventListener("DOMContentLoaded", async () => {
  const errEl = document.getElementById("loginError");
  const btn = document.getElementById("btnGoogleSignIn");
  const continueRow = document.getElementById("continueSessionRow");

  if (!hasValidPersistedOAuthSession()) {
    try {
      const silentAuth = new GoogleAuthProvider(dashboardifyCloudConfig);
      await silentAuth.init();
      if (await silentAuth.restoreSessionOrSilentRefresh()) {
        window.location.replace("cloud.html");
        return;
      }
    } catch {
      /* stay on login */
    }
  }

  if (continueRow && hasValidPersistedOAuthSession()) {
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
