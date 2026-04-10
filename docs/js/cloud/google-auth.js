export const GOOGLE_OAUTH_SESSION_KEY = "dashboardify_google_oauth";

/** Read persisted OAuth payload; prefers localStorage (shared across tabs), migrates legacy sessionStorage. */
function readPersistedOAuthRaw() {
  try {
    const fromLocal = localStorage.getItem(GOOGLE_OAUTH_SESSION_KEY);
    if (fromLocal) return fromLocal;
    const fromSession = sessionStorage.getItem(GOOGLE_OAUTH_SESSION_KEY);
    if (fromSession) {
      localStorage.setItem(GOOGLE_OAUTH_SESSION_KEY, fromSession);
      sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
      return fromSession;
    }
  } catch {
    /* ignore */
  }
  return null;
}

function writePersistedOAuthRaw(json) {
  try {
    localStorage.setItem(GOOGLE_OAUTH_SESSION_KEY, json);
    sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
  } catch {
    /* ignore quota / private mode */
  }
}

function removePersistedOAuth() {
  try {
    localStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
    sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
  } catch {
    /* ignore */
  }
}

/** True if localStorage (or legacy sessionStorage) has a non-expired OAuth payload. */
export function hasValidPersistedOAuthSession() {
  try {
    const raw = readPersistedOAuthRaw();
    if (!raw) return false;
    const data = JSON.parse(raw);
    return Boolean(
      data.accessToken && data.expiresAtMs && Date.now() < data.expiresAtMs
    );
  } catch {
    return false;
  }
}

export class GoogleAuthProvider {
  constructor(config) {
    this.config = config;
    this.accessToken = "";
    /** Wall-clock expiry for in-memory token (same as persisted `expiresAtMs`). */
    this.tokenExpiresAtMs = 0;
    this.tokenClient = null;
  }

  _persistFromResponse(response) {
    if (!response || !response.access_token) return;
    const expiresInSec = Number(response.expires_in);
    const expiresIn = Number.isFinite(expiresInSec) && expiresInSec > 0 ? expiresInSec : 3600;
    const expiresAtMs = Date.now() + (expiresIn - 120) * 1000;
    this.tokenExpiresAtMs = expiresAtMs;
    writePersistedOAuthRaw(
      JSON.stringify({
        accessToken: response.access_token,
        expiresAtMs
      })
    );
  }

  restoreSessionIfValid() {
    try {
      const raw = readPersistedOAuthRaw();
      if (!raw) return false;
      const data = JSON.parse(raw);
      if (
        !data.accessToken ||
        !data.expiresAtMs ||
        Date.now() >= data.expiresAtMs
      ) {
        removePersistedOAuth();
        this.tokenExpiresAtMs = 0;
        return false;
      }
      this.accessToken = data.accessToken;
      this.tokenExpiresAtMs = data.expiresAtMs;
      return true;
    } catch {
      removePersistedOAuth();
      this.tokenExpiresAtMs = 0;
      return false;
    }
  }

  /**
   * Use a valid stored access token, or obtain a new one without prompting if Google still
   * has consent and an active session (typical multi-day browser login to Google).
   */
  async restoreSessionOrSilentRefresh() {
    if (this.restoreSessionIfValid()) {
      return true;
    }
    try {
      await this.getAccessToken(false);
      return Boolean(this.accessToken);
    } catch {
      removePersistedOAuth();
      this.accessToken = "";
      this.tokenExpiresAtMs = 0;
      return false;
    }
  }

  clearPersistedSession() {
    removePersistedOAuth();
    this.accessToken = "";
    this.tokenExpiresAtMs = 0;
  }

  async init() {
    await this._loadScript("https://accounts.google.com/gsi/client");
    this.tokenClient = google.accounts.oauth2.initTokenClient({
      client_id: this.config.clientId,
      scope: this.config.scope,
      callback: () => {}
    });
  }

  _loadScript(src) {
    return new Promise((resolve, reject) => {
      const existing = document.querySelector(`script[src="${src}"]`);
      if (existing) {
        resolve();
        return;
      }
      const script = document.createElement("script");
      script.src = src;
      script.async = true;
      script.defer = true;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error(`Failed to load ${src}`));
      document.head.appendChild(script);
    });
  }

  async getAccessToken(forcePrompt = false) {
    const cachedOk =
      this.accessToken &&
      !forcePrompt &&
      this.tokenExpiresAtMs > 0 &&
      Date.now() < this.tokenExpiresAtMs;
    if (cachedOk) {
      return this.accessToken;
    }
    if (!forcePrompt) {
      this.accessToken = "";
    }

    return new Promise((resolve, reject) => {
      this.tokenClient.callback = (response) => {
        if (response && response.access_token) {
          this.accessToken = response.access_token;
          this._persistFromResponse(response);
          resolve(this.accessToken);
          return;
        }
        reject(new Error("Could not acquire Google access token."));
      };

      this.tokenClient.requestAccessToken({
        prompt: forcePrompt ? "consent" : ""
      });
    });
  }

  async signIn() {
    return this.getAccessToken(true);
  }

  signOut() {
    this.clearPersistedSession();
  }
}
