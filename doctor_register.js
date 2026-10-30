// 🩺 دالة لإظهار أو إخفاء حقول الطبيب حسب الدور المختار من القائمة (role)
function toggleDoctorFields() {
  // الحصول على قيمة الدور (role) من القائمة المنسدلة
  const role = document.getElementById('role').value;

  // تحديد القسم الذي يحتوي على حقول الطبيب (التخصص + الرسوم)
  const doctorFields = document.getElementById('doctorFields');

  // تحديد حقول التخصص والرسوم داخل النموذج
  const specialization = document.querySelector('input[name="specialization"]');
  const fee = document.querySelector('input[name="fee"]');

  // ✅ إذا كان الدور هو "doctor" → أظهر الحقول واجعلها مطلوبة
  if (role === 'doctor') {
    doctorFields.style.display = 'block'; // إظهار القسم
    specialization.setAttribute('required', 'required'); // جعل التخصص إلزامي
    fee.setAttribute('required', 'required'); // جعل الرسوم إلزامية
  } 
  // ❌ إذا لم يكن "doctor" → أخفِ الحقول واجعلها غير مطلوبة
  else {
    doctorFields.style.display = 'none'; // إخفاء القسم
    specialization.removeAttribute('required'); // إزالة الإلزام
    fee.removeAttribute('required'); // إزالة الإلزام
  }
}