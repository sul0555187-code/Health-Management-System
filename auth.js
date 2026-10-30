// ================================
// 🔐 Authentication Helper Functions
// ================================

// ================================
// ✅ تحسين تجربة المستخدم
// ================================

// التحقق من تطابق كلمتي المرور في صفحة Register
document.addEventListener("DOMContentLoaded", function () {
  const password = document.getElementById("password");
  const confirmPassword = document.getElementById("confirm_password");

  if (password && confirmPassword) {
    confirmPassword.addEventListener("input", () => {
      if (confirmPassword.value !== password.value) {
        confirmPassword.setCustomValidity("Passwords do not match");
      } else {
        confirmPassword.setCustomValidity("");
      }
    });
  }
});

function togglePassword(inputId, iconElement) {
  const input = document.getElementById(inputId);
  const icon = iconElement.querySelector("i");

  if (input.type === "password") {
    input.type = "text";            // 👁️ يعرض الباسورد
    icon.classList.remove("fa-eye-slash");
    icon.classList.add("fa-eye"); // يغير شكل الأيقونة
  } else {
    input.type = "password";        // 🔒 يخفي الباسورد
    icon.classList.remove("fa-eye");
    icon.classList.add("fa-eye-slash");
  }
}