document.addEventListener("DOMContentLoaded", () => {
  document.body.classList.add("loaded");
});

document.querySelectorAll("a").forEach(link => {
  // اشتغل فقط على الروابط الداخلية
  if (link.hostname === window.location.hostname) {
    link.addEventListener("click", e => {
      // ✅ السماح بالروابط التي تفتح في تبويب جديد
      if (link.target === "_blank") return;

      e.preventDefault();
      const href = link.getAttribute("href");
      document.body.classList.remove("loaded");

      setTimeout(() => {
        window.location.href = href;
      }, 300); // نفس وقت الـ transition
    });
  }
});