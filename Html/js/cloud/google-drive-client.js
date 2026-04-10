const DRIVE_API_ROOT = "https://www.googleapis.com/drive/v3/files";
const UPLOAD_API_ROOT = "https://www.googleapis.com/upload/drive/v3/files";

export class GoogleDriveAppDataClient {
  constructor(authProvider, fileName = "dashboardify-data.json") {
    this.authProvider = authProvider;
    this.fileName = fileName;
  }

  async _fetchJson(url, options = {}) {
    const token = await this.authProvider.getAccessToken();
    const response = await fetch(url, {
      ...options,
      headers: {
        Authorization: `Bearer ${token}`,
        ...(options.headers || {})
      }
    });

    if (!response.ok) {
      const text = await response.text();
      throw new Error(`Drive API error ${response.status}: ${text}`);
    }

    return response.json();
  }

  async _fetchText(url, options = {}) {
    const token = await this.authProvider.getAccessToken();
    const response = await fetch(url, {
      ...options,
      headers: {
        Authorization: `Bearer ${token}`,
        ...(options.headers || {})
      }
    });
    if (!response.ok) {
      const text = await response.text();
      throw new Error(`Drive API error ${response.status}: ${text}`);
    }
    return response.text();
  }

  async findDataFile() {
    const q = encodeURIComponent(
      `name='${this.fileName}' and 'appDataFolder' in parents and trashed=false`
    );
    const url = `${DRIVE_API_ROOT}?spaces=appDataFolder&q=${q}&fields=files(id,name,modifiedTime,version)&pageSize=1`;
    const data = await this._fetchJson(url);
    return data.files && data.files.length ? data.files[0] : null;
  }

  async createDataFile(jsonText) {
    const boundary = "dashboardifyBoundary";
    const metadata = {
      name: this.fileName,
      parents: ["appDataFolder"],
      mimeType: "application/json",
      appProperties: {
        app: "Dashboardify",
        type: "primaryData"
      }
    };

    const body =
      `--${boundary}\r\n` +
      "Content-Type: application/json; charset=UTF-8\r\n\r\n" +
      `${JSON.stringify(metadata)}\r\n` +
      `--${boundary}\r\n` +
      "Content-Type: application/json\r\n\r\n" +
      `${jsonText}\r\n` +
      `--${boundary}--`;

    return this._fetchJson(
      `${UPLOAD_API_ROOT}?uploadType=multipart&fields=id,name,modifiedTime,version`,
      {
        method: "POST",
        headers: {
          "Content-Type": `multipart/related; boundary=${boundary}`
        },
        body
      }
    );
  }

  async readFileContent(fileId) {
    const url = `${DRIVE_API_ROOT}/${encodeURIComponent(fileId)}?alt=media`;
    return this._fetchText(url);
  }

  async updateFileContent(fileId, jsonText) {
    return this._fetchJson(
      `${UPLOAD_API_ROOT}/${encodeURIComponent(fileId)}?uploadType=media&fields=id,name,modifiedTime,version`,
      {
        method: "PATCH",
        headers: {
          "Content-Type": "application/json"
        },
        body: jsonText
      }
    );
  }
}
