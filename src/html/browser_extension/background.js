// background.js

chrome.runtime.onInstalled.addListener(function () {
    // Set the new tab page to the desired URL when the extension is installed or updated
    chrome.tabs.query({ url: 'chrome://newtab/' }, function (tabs) {
      if (tabs.length > 0) {
        chrome.tabs.update(tabs[0].id, { url: 'https://dashboardify.app/Dashboardify/' });
      }
    });
  });
  