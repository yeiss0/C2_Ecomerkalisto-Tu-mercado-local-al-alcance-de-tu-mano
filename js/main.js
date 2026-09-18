import { initMenu } from "./menu.js";
import { initChatbot } from "./chatbot.js";

function init() {
  initMenu();
  initChatbot();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", init);
} else {
  init();
}
