// Load AOS library dynamically
const script = document.createElement("script");
script.src = "https://unpkg.com/aos@2.3.4/dist/aos.js";
script.onload = () => {
  AOS.init({
    duration: 1000, // animation duration in ms
    once: true,     // animate only once
  });
};
document.head.appendChild(script);