// newtab.js

document.addEventListener('DOMContentLoaded', function () {
    chrome.storage.sync.get('useCachedPage', function (result) {
      const useCachedPage = result.useCachedPage || false;
  
      const newTabURL = useCachedPage
        ? 'https://dashboardify.app/Dashboardify/cachedpage.html'
        : 'https://dashboardify.app/Dashboardify/index.php';
  
      chrome.tabs.update({ url: newTabURL });
    });
  });
  