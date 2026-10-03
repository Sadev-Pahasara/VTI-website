// Disable right click
 document.addEventListener("contextmenu", function (e) {
 e.preventDefault();
 });

// chatbox

const chatIcon = document.getElementById("chatIcon");
const chatBox = document.getElementById("chatBox");

chatIcon.addEventListener("click", () => {
  chatBox.style.display = (chatBox.style.display === "block") ? "none" : "block";
});
