document.addEventListener('DOMContentLoaded', () => {
    const settingsLink = document.getElementById('settingsLink');
    const settingsMenu = document.getElementById('settingsMenu');
    if (!settingsLink || !settingsMenu) return;

    let isMenuVisible = false;
    let menuBounds = { left: 0, right: 0, top: 0, bottom: 0 };

    const refreshMenuBounds = () => {
        const r = settingsMenu.getBoundingClientRect();
        menuBounds = {
            left: r.left,
            right: r.right,
            top: r.top,
            bottom: r.bottom
        };
    };

    const positionMenu = () => {
        const margin = 8;
        const rect = settingsLink.getBoundingClientRect();
        settingsMenu.style.position = 'fixed';
        settingsMenu.style.display = 'block';

        let top = rect.bottom + margin;
        const spaceBelow = window.innerHeight - top - margin;
        settingsMenu.style.maxHeight = Math.max(120, spaceBelow) + 'px';
        settingsMenu.style.overflowY = 'auto';

        let left = rect.right - settingsMenu.offsetWidth;
        if (left < margin) {
            left = margin;
        }
        const maxLeft = window.innerWidth - settingsMenu.offsetWidth - margin;
        if (left > maxLeft) {
            left = Math.max(margin, maxLeft);
        }

        settingsMenu.style.top = `${top}px`;
        settingsMenu.style.left = `${left}px`;
        refreshMenuBounds();
    };

    const showMenu = () => {
        settingsMenu.style.display = 'block';
        positionMenu();
        const m = 8;
        const menuRect = settingsMenu.getBoundingClientRect();
        if (menuRect.bottom > window.innerHeight - m) {
            const h = settingsMenu.offsetHeight;
            let top = settingsLink.getBoundingClientRect().top - m - h;
            if (top < m) top = m;
            settingsMenu.style.top = `${top}px`;
        }
        isMenuVisible = true;
        settingsLink.setAttribute('aria-expanded', 'true');
        refreshMenuBounds();
    };

    const hideMenu = () => {
        settingsMenu.style.display = 'none';
        isMenuVisible = false;
        settingsLink.setAttribute('aria-expanded', 'false');
    };

    settingsLink.addEventListener('click', (event) => {
        event.preventDefault();
        if (isMenuVisible) {
            hideMenu();
        } else {
            showMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (isMenuVisible) {
            positionMenu();
        }
    });

    document.addEventListener('click', (event) => {
        if (!settingsLink.contains(event.target) && !settingsMenu.contains(event.target)) {
            hideMenu();
        }
    });

    document.addEventListener('mousemove', (event) => {
        if (!isMenuVisible) return;
        const mouseX = event.clientX;
        const mouseY = event.clientY;
        const distanceX = Math.max(menuBounds.left - mouseX, mouseX - menuBounds.right);
        const distanceY = Math.max(menuBounds.top - mouseY, mouseY - menuBounds.bottom);
        const distance = Math.sqrt(distanceX ** 2 + distanceY ** 2);
        if (distance > 250) {
            hideMenu();
        }
    });
});
