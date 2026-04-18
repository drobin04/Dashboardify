/**
 * Starter widgets for first-time Google Drive app data (see CloudStorageAdapter.maybeSeedWelcomePack).
 * Notes content is rendered as markdown (md-block).
 */
export function buildWelcomePackWidgets(dashboardRecId) {
  const dash = String(dashboardRecId);
  const note = (overrides) => ({
    RecID: `cloud-nux-${crypto.randomUUID()}`,
    DashboardRecID: dash,
    WidgetType: "Notes",
    BookmarkDisplayText: "",
    WidgetURL: "",
    WidgetCSSClass: "nux-welcome",
    Notes: "",
    PositionX: "0",
    PositionY: "0",
    SizeX: "380",
    SizeY: "260",
    ...overrides
  });

  return [
    note({
      PositionX: "166",
      PositionY: "116",
      Notes: `## Welcome to Dashboardify

Use the toolbar to **New Widget** — pick a type (bookmark, web page in an iframe, notes, images, timers, and more), then **Add widget**.

Turn on **Edit Widgets** to show edit and delete controls on each tile. Under **Settings**, use **Move Widgets** and **Resize Widgets** to arrange your board.`
    }),
    note({
      PositionX: "570",
      PositionY: "116",
      Notes: `## What you can build here

- **Links hub** — bookmarks to docs, repos, and tools you open every day  
- **Embedded tools** — iframes for calendars, boards, or internal pages  
- **Scratch notes** — markdown note tiles for checklists or context  
- **Visuals** — background images per dashboard plus optional countdowns  

Everything saves to your **Google Drive** app data when you are signed in.`
    }),
    note({
      PositionX: "166",
      PositionY: "400",
      Notes: `## Custom CSS

Open **Settings → Edit CSS** for the **selected dashboard**. You can paste CSS for the page and set a **background image URL**.

Per-widget styling: when you add or edit a widget, set **CSS class** on the tile, then target it from dashboard CSS (for example \`.nux-welcome { ... }\`).`
    }),
    note({
      PositionX: "570",
      PositionY: "400",
      Notes: `## More dashboards

Click **New Dashboard** in the toolbar, enter a name, and **Create**. Switch boards with the **Dashboard** dropdown.

Rename or remove a board from **Settings → Edit Dashboard** (you cannot delete the last dashboard). Each dashboard has its own widgets and CSS.`
    })
  ];
}
