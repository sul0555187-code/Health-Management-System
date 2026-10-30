// 🖼️ دالة لعرض معاينة الصورة التي يرفعها المستخدم قبل الحفظ
function previewImage(event) {
  // إنشاء كائن FileReader لقراءة محتوى الملف المرفوع
  const reader = new FileReader();

  // عند الانتهاء من قراءة الملف → يتم عرض الصورة داخل <img id="previewImg">
  reader.onload = function() {
    document.getElementById('previewImg').src = reader.result;
  };

  // قراءة الملف المرفوع وتحويله إلى Data URL لعرضه في الصفحة
  reader.readAsDataURL(event.target.files[0]);
}
