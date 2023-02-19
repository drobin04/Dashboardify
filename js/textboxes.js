//Allows for drawing textboxes on the screen for temporary notes. 
const container = document.getElementById("container");
		container.addEventListener("mousedown", (event) => {
			if (event.detail === 2) {
				event.preventDefault();
				const startX = event.clientX;
				const startY = event.clientY;
				const width = 0;
				const height = 0;
				const textbox = document.createElement("div");
				textbox.classList.add("textbox");
				textbox.style.left = startX + "px";
				textbox.style.top = startY + "px";
				textbox.style.width = width + "px";
				textbox.style.height = height + "px";
				container.appendChild(textbox);
				container.addEventListener("mousemove", (event) => {
					if (event.buttons === 1) {
						const endX = event.clientX;
						const endY = event.clientY;
						const newWidth = Math.abs(endX - startX);
						const newHeight = Math.abs(endY - startY);
						textbox.style.width = newWidth + "px";
						textbox.style.height = newHeight + "px";
						if (endX > startX) {
							textbox.style.left = startX + "px";
						} else {
							textbox.style.left = endX + "px";
						}
						if (endY > startY) {
							textbox.style.top = startY + "px";
						} else {
							textbox.style.top = endY + "px";
						}
					}
				});
				container.addEventListener("mouseup", (event) => {
					container.removeEventListener("mousemove", () => {});
				});
			}
		});