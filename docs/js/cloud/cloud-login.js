import { dashboardifyCloudConfig } from "./config.js";
import {
  GoogleAuthProvider,
  hasValidPersistedOAuthSession
} from "./google-auth.js";
import { readCloudDataCacheFromLocalStorage } from "./storage-adapter.js";

window.addEventListener("DOMContentLoaded", () => {
  const errEl = document.getElementById("loginError");
  const btn = document.getElementById("btnGoogleSignIn");
  const continueRow = document.getElementById("continueSessionRow");

  const actions = document.querySelector(".cloud-login-actions");
  if (actions && readCloudDataCacheFromLocalStorage()) {
    const wrap = document.createElement("div");
    wrap.className = "cloud-continue-row";
    wrap.innerHTML =
      "<p style=\"color: rgba(255,255,255,0.9); text-align: center; font-size: 13px; margin: 12px 0 6px;\">A saved copy of your dashboards is on this device.</p>" +
      "<button type=\"button\" id=\"btnOfflineDashboards\" class=\"cloud-google-btn\" style=\"max-width: 280px;\">Open saved dashboards</button>";
    actions.after(wrap);
    wrap.querySelector("#btnOfflineDashboards").addEventListener("click", () => {
      window.location.href = "cloud.html";
    });
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
