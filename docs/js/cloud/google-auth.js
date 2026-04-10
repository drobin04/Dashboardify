export const GOOGLE_OAUTH_SESSION_KEY = "dashboardify_google_oauth";

export class GoogleAuthProvider {
  constructor(config) {
    this.config = config;
    this.accessToken = "";
    this.tokenClient = null;
  }

  _persistFromResponse(response) {
    if (!response || !response.access_token || !response.expires_in) return;
    const expiresAtMs =
      Date.now() + (Number(response.expires_in) - 120) * 1000;
    try {
      sessionStorage.setItem(
        GOOGLE_OAUTH_SESSION_KEY,
        JSON.stringify({
          accessToken: response.access_token,
          expiresAtMs
        })
      );
    } catch {
      /* ignore quota / private mode */
    }
  }

  restoreSessionIfValid() {
    try {
      const raw = sessionStorage.getItem(GOOGLE_OAUTH_SESSION_KEY);
      if (!raw) return false;
      const data = JSON.parse(raw);
      if (
        !data.accessToken ||
        !data.expiresAtMs ||
        Date.now() >= data.expiresAtMs
      ) {
        sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
        return false;
      }
      this.accessToken = data.accessToken;
      return true;
    } catch {
      sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
      return false;
    }
  }

  clearPersistedSession() {
    try {
      sessionStorage.removeItem(GOOGLE_OAUTH_SESSION_KEY);
    } catch {
      /* ignore */
    }
    this.accessToken = "";
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
    if (this.accessToken && !forcePrompt) {
      return this.accessToken;
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
