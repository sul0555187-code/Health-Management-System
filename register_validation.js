document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form[action='register.php']");
  const dobInput = document.querySelector("input[name='dob']");
  const phoneInput = document.getElementById("phone");
  const countryCode = document.getElementById("countryCode");
  const hint = document.querySelector(".phone-hint");
  const messageContainer = document.createElement("div");
  messageContainer.classList.add("alert-container");
  form.prepend(messageContainer);

  // 🔢 عدد الأرقام المسموح بها لكل دولة
  const phoneRules = {
    "+966": { digits: 9, name: "Saudi Arabia" },
    "+20": { digits: 10, name: "Egypt" },
    "+971": { digits: 9, name: "UAE" },
    "+962": { digits: 9, name: "Jordan" },
    "+212": { digits: 9, name: "Morocco" },
  };

  // 🕹️ تحديث التلميح حسب الدولة
  const updateHint = () => {
    const code = countryCode.value;
    const rule = phoneRules[code];
    hint.textContent = `📞 ${rule.name} numbers must be ${rule.digits} digits.`;
  };

  updateHint(); // أول مرة

  // 🔄 لما المستخدم يبدّل الدولة – امسح الرقم وحدّث التلميح
  countryCode.addEventListener("change", () => {
    phoneInput.value = "";
    updateHint();
  });

  // ✅ تحقق فوري من رقم الهاتف أثناء الكتابة
  phoneInput.addEventListener("input", () => {
    const value = phoneInput.value.replace(/\D/g, ""); // إزالة أي رموز غير أرقام
    phoneInput.value = value;

    const code = countryCode.value;
    const rule = phoneRules[code];
    const length = value.length;

    if (length > rule.digits) phoneInput.value = value.slice(0, rule.digits);

    hint.textContent =
      length === rule.digits
        ? `✅ Valid ${rule.name} number`
        : `📞 Must be ${rule.digits} digits for ${rule.name}`;
  });

  // ✅ تحقق من صحة البيانات عند الإرسال
  form.addEventListener("submit", (e) => {
    messageContainer.innerHTML = "";
    const messages = [];

    // 🎂 تحقق من العمر (16 سنة على الأقل)
    const dobValue = new Date(dobInput.value);
    const today = new Date();
    const age = today.getFullYear() - dobValue.getFullYear();
    const monthDiff = today.getMonth() - dobValue.getMonth();
    const dayDiff = today.getDate() - dobValue.getDate();

    if (
      age < 16 ||
      (age === 16 && (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)))
    ) {
      messages.push("⚠️ You must be at least 16 years old to register.");
    }

    // 📞 تحقق من الرقم حسب الدولة
    const code = countryCode.value;
    const phoneValue = phoneInput.value.trim();
    const rule = phoneRules[code];
    if (phoneValue.length !== rule.digits) {
      messages.push(`⚠️ ${rule.name} phone numbers must be ${rule.digits} digits.`);
    }

    // عرض التنبيهات إن وجدت
    if (messages.length > 0) {
      e.preventDefault();
      messageContainer.innerHTML = messages
        .map((msg) => `<p class="alert error">${msg}</p>`)
        .join("");
      messageContainer.scrollIntoView({ behavior: "smooth" });
    }
  });
});


// ✅ إظهار رسالة نجاح أو خطأ بشكل أنيق
function showFloatingMessage(text, type = "success") {
  // لو فيه رسالة قديمة نحذفها
  const oldMsg = document.querySelector(".floating-message");
  if (oldMsg) oldMsg.remove();

  // إنشاء العنصر
  const msg = document.createElement("div");
  msg.className = `floating-message ${type}`;
  msg.textContent = text;
  document.body.appendChild(msg);

  // عرضها بتأثير بسيط
  setTimeout(() => msg.classList.add("show"), 100);

  // تختفي بعد 4 ثواني
  setTimeout(() => {
    msg.classList.remove("show");
    setTimeout(() => msg.remove(), 500);
  }, 4000);
}

// ✅ مثال: عند نجاح التسجيل
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get("registered") === "success") {
  showFloatingMessage("🎉 Registration successful! Welcome aboard!", "success");
}