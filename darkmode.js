// darkmode.js
document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("darkToggle");      // dark mode toggle button
  const body = document.body;                                   // body element
  
  if (!toggleBtn) return;                                       // لو مفيش زرار في الصفحة متعملش حاجة

  //تحقق من التفضيل المحفوظ للمستخدم
  if (localStorage.getItem("theme") === "dark") {               // if dark mode was previously enabled
    body.classList.add("dark-mode");                            // enable dark mode
    toggleBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';    // change icon to sun
    }

  toggleBtn.addEventListener("click", () => {                   // on button click
    body.classList.toggle("dark-mode");                         // toggle dark mode class

    // Save the user's preference in localStorage
    if (body.classList.contains("dark-mode")) {                 // if dark mode is now enabled
        toggleBtn.innerHTML = '<i class="fa-solid fa-sun"></i>';  // change icon to sun
        localStorage.setItem("theme", "dark");                    // save preference
    } else {
        toggleBtn.innerHTML = '<i class="fa-solid fa-moon"></i>'; // change icon to moon
        localStorage.setItem("theme", "light");                   // save preference
    }
    });
});