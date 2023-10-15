const searchParams = new URLSearchParams(window.location.search); // Get the search parameters from the URL
const bookUrl = searchParams.get('bookurl'); // Get the value of the 'bookurl' parameter

// Print the value of the 'bookurl' parameter
console.log('Loading ' + bookUrl);
const bookname = bookUrl;
//Get friendly name for localstorage
const parts = bookname.split('/');
const bookfriendlyname = parts.pop();
//const bookfriendlyname = bookname;

var book = ePub(bookUrl); // Load the opf
var rendition = book.renderTo("area", {
    width: "100%",
    height: 600,
    spread: "always"
});

function init() {



rendition.display();

book.ready.then(() => { //Set up event listeners

    var next = document.getElementById("next");

    next.addEventListener("click", function(e) {
        book.package.metadata.direction === "rtl" ? rendition.prev() : rendition.next();
        e.preventDefault();
        saveViewerState();
    })

}, false);

var prev = document.getElementById("prev");
prev.addEventListener("click", function(e) {
    book.package.metadata.direction === "rtl" ? rendition.next() : rendition.prev();
    e.preventDefault();
    saveViewerState();
}, false);

var keyListener = function(e) {

    // Left Key
    if ((e.keyCode || e.which) == 37) {
        book.package.metadata.direction === "rtl" ? rendition.next() : rendition.prev();
    }

    // Right Key
    if ((e.keyCode || e.which) == 39) {
        book.package.metadata.direction === "rtl" ? rendition.prev() : rendition.next();
    }

};

rendition.on("keyup", keyListener);
document.addEventListener("keyup", keyListener, false);

getLocation(localStorage.getItem('userID'), bookfriendlyname);

setInterval(CheckBookProgressForSync, 20000);
setTimeout(setBookToSavedState, 1200);
} // end of init() function

window.onload = init;

function saveViewerState() {
    if (rendition && rendition.location && rendition.location.start) {
        var currentLocation = rendition.currentLocation(); // get the current location
        // save the current location in localStorage
        localStorage.setItem(bookfriendlyname, JSON.stringify(currentLocation));
    }
}

// Retrieve the saved state from local storage and set the book to the saved page
function setBookToSavedState() {
    // get the saved location from localStorage
    var savedLocation = localStorage.getItem(bookfriendlyname);

    if (savedLocation) {
        // display the saved location
        var parsedLocation = JSON.parse(savedLocation);
        if (parsedLocation && parsedLocation.start && parsedLocation.start.cfi) {
            rendition.display(parsedLocation.start.cfi);
        }
    }
}

function getSavedLocation() {
    return JSON.parse(localStorage.getItem(bookfriendlyname));
}



function getLocation(userID) {
    if (localStorage.getItem(bookfriendlyname + '_serverstate')) {
        try {
            // Set the data to be sent in the message body as an object
            const data = {
                userID: userID,
                bookname: bookfriendlyname
            };

            // Send a POST request to the PHP page with the data in the message body
            fetch('getbookprogress.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    // Update the local storage item with the retrieved location data
                    localStorage.setItem(bookfriendlyname, JSON.stringify(data));
                    // Update serverstate key so comparisons can be made to preserve network traffic / CPU traffic
                    localStorage.setItem(bookfriendlyname + '_serverstate', JSON.stringify(data));
                })
                .catch(error => {
                    console.error('Error retrieving location:', error);
                });
        } catch (error) {
            console.log('Failed to get location data for book from server');
        }
    }
}

function syncbookstatus() {

    // Retrieve the localstorage item
    const localstorageItem = localStorage.getItem(bookfriendlyname);

    // Parse the localstorage item to a JSON object
    const data = JSON.parse(localstorageItem);

    // Create a new object with the userID, bookname, and location
    const payload = {
        userID: localStorage.getItem('userID'),
        bookname: bookfriendlyname,
        location: data
    };

    // Send the payload to the PHP web page using an API request
    fetch('syncbookprogress.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(response => {
            if (response.ok) {
                console.log('Data sent successfully, updating sync state.');
                localStorage.setItem(bookfriendlyname + '_serverstate', localStorage.getItem(bookfriendlyname));
            } else {
                console.error('Error sending data');
            }
        })
        .catch(error => {
            console.error('Error sending data:', error);
        });
}

function CheckBookProgressForSync() {
    const serverbookprogresskey = bookfriendlyname + '_serverstate';

    if (localStorage.getItem(serverbookprogresskey)) {
        const valueToCheck = localStorage.getItem(serverbookprogresskey);
        const valueToCompare = localStorage.getItem(bookfriendlyname);
        if (valueToCheck !== valueToCompare) {
            console.log('Values didnt match, syncing');
            syncbookstatus();
        } else {
            console.log('Values dont need to be synced to server for ebook, no progress.');
        }
    } else {
        console.log('server progress key seemingly not found, syncing. Key: ' + serverbookprogresskey);
        syncbookstatus();
    }

}