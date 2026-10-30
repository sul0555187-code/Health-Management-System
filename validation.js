document.addEventListener("DOMContentLoaded", () => {
  // 🔹 تعيين الفورم حسب الصفحة الحالية
  const form = document.querySelector("form[action*='admin_add_doctor.php'], form[action*='admin_edit_doctor.php'], form[action*='admin_add_user.php'], form[action*='admin_edit_user.php']");
  if (!form) return;

  const dobInput = form.querySelector("input[name='dob']");
  const phoneInput = form.querySelector("input[name='phone']");
  const countryCode = form.querySelector("select[name='country_code']");
  const hint = form.querySelector(".phone-hint");
  const messageContainer = document.createElement("div");
  messageContainer.classList.add("alert-container");
  form.prepend(messageContainer);

  const phoneRules = {
    "+966": { digits: 9, name: "Saudi Arabia" },
    "+20": { digits: 10, name: "Egypt" },
    "+971": { digits: 9, name: "UAE" },
    "+962": { digits: 9, name: "Jordan" },
    "+212": { digits: 9, name: "Morocco" },
  };

  const updateHint = () => {
    if (!hint) return;
    const code = countryCode.value;
    const rule = phoneRules[code];
    hint.textContent = `📞 ${rule.name} numbers must be ${rule.digits} digits.`;
  };

  updateHint();

  countryCode.addEventListener("change", () => {
    phoneInput.value = "";
    updateHint();
  });

  phoneInput.addEventListener("input", () => {
    const value = phoneInput.value.replace(/\D/g, "");
    phoneInput.value = value;

    const code = countryCode.value;
    const rule = phoneRules[code];
    if (value.length > rule.digits) phoneInput.value = value.slice(0, rule.digits);

    if (hint) {
      hint.textContent =
        value.length === rule.digits
          ? `✅ Valid ${rule.name} number`
          : `📞 Must be ${rule.digits} digits for ${rule.name}`;
    }
  });

  form.addEventListener("submit", (e) => {
    messageContainer.innerHTML = "";
    const messages = [];

    const dobValue = new Date(dobInput.value);
    const today = new Date();
    const age = today.getFullYear() - dobValue.getFullYear();
    const monthDiff = today.getMonth() - dobValue.getMonth();
    const dayDiff = today.getDate() - dobValue.getDate();

    if (age < 16 || (age === 16 && (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)))) {
      messages.push("⚠️ You must be at least 16 years old.");
    }

    const code = countryCode.value;
    const phoneValue = phoneInput.value.trim();
    const rule = phoneRules[code];
    if (phoneValue.length !== rule.digits) {
      messages.push(`⚠️ ${rule.name} phone numbers must be ${rule.digits} digits.`);
    }

    if (messages.length > 0) {
      e.preventDefault();
      messageContainer.innerHTML = messages.map((msg) => `<p class="alert error">${msg}</p>`).join("");
      messageContainer.scrollIntoView({ behavior: "smooth" });
    }
  });
});

// ✅ إظهار رسالة نجاح أو خطأ بشكل أنيق
function showFloatingMessage(text, type = "success") {
  const oldMsg = document.querySelector(".floating-message");
  if (oldMsg) oldMsg.remove();

  const msg = document.createElement("div");
  msg.className = `floating-message ${type}`;
  msg.textContent = text;
  document.body.appendChild(msg);

  setTimeout(() => msg.classList.add("show"), 100);
  setTimeout(() => {
    msg.classList.remove("show");
    setTimeout(() => msg.remove(), 500);
  }, 4000);
}

// ✅ عرض رسالة عند نجاح التسجيل أو تحديث الطبيب
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get("registered") === "success") {
  showFloatingMessage("🎉 Registration successful! Welcome aboard!", "success");
}
if (urlParams.get("updated") === "success") {
  showFloatingMessage("✅ Doctor updated successfully!", "success");
}