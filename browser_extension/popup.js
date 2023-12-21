// popup.js

document.addEventListener('DOMContentLoaded', function () {
    const useCachedPageCheckbox = document.getElementById('useCachedPage');
  
    useCachedPageCheckbox.addEventListener('change', function () {
      const useCachedPage = useCachedPageCheckbox.checked;
  
      // Save the user's preference to local storage
      chrome.storage.sync.set({ useCachedPage });
    });
  
    // Retrieve the user's preference and update the checkbox state
    chrome.storage.sync.get('useCachedPage', function (result) {
      useCachedPageCheckbox.checked = result.useCachedPage || false;
    });
  });
  