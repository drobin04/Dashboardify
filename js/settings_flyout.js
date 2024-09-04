document.addEventListener('DOMContentLoaded', () => {
    const settingsLink = document.getElementById('settingsLink');
    const settingsMenu = document.getElementById('settingsMenu');
    let isMenuVisible = false;
    let menuPosition = { top: 0, left: 0 };

    const showMenu = () => {
        settingsMenu.style.display = 'block';
        const rect = settingsLink.getBoundingClientRect();
        settingsMenu.style.top = `${rect.bottom + window.scrollY}px`;
        settingsMenu.style.left = `${rect.left}px`;
        menuPosition = {
            top: rect.bottom + window.scrollY,
            left: rect.left,
            right: rect.left + settingsMenu.offsetWidth,
            bottom: rect.bottom + window.scrollY + settingsMenu.offsetHeight
        };
        isMenuVisible = true;
    };

    const hideMenu = () => {
        settingsMenu.style.display = 'none';
        isMenuVisible = false;
    };

    settingsLink.addEventListener('click', (event) => {
        event.preventDefault();
        if (isMenuVisible) {
            hideMenu();
        } else {
            showMenu();
        }
    });

    document.addEventListener('click', (event) => {
        if (!settingsLink.contains(event.target) && !settingsMenu.contains(event.target)) {
            hideMenu();
        }
    });

    document.addEventListener('mousemove', (event) => {
        if (isMenuVisible) {
            const mouseX = event.clientX;
            const mouseY = event.clientY;

            const distanceX = Math.max(menuPosition.left - mouseX, mouseX - menuPosition.right);
            const distanceY = Math.max(menuPosition.top - mouseY, mouseY - menuPosition.bottom);
            const distance = Math.sqrt(distanceX ** 2 + distanceY ** 2);

            if (distance > 250) {
                hideMenu();
            }
        }
    });
});
