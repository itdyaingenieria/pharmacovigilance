import "./bootstrap";
import { createApp } from "vue";
import PharmacovigilanceApp from "./pharmacovigilance/PharmacovigilanceApp.vue";

const mountNode = document.getElementById("pharmacovigilance-app");

if (mountNode) {
    createApp(PharmacovigilanceApp).mount(mountNode);
}
