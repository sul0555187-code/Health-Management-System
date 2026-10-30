document.querySelectorAll('.pm-option').forEach(function(label){
  const radio = label.querySelector('input[type="radio"]');

  // عند النقر
  label.addEventListener('click', function(){
    radio.checked = true;
    updateSelection(label);
  });

  // دعم الضغط على Enter أو Space عند التركيز
  label.addEventListener('keydown', function(e){
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      radio.checked = true;
      updateSelection(label);
    }
  });
});

function updateSelection(chosenLabel){
  document.querySelectorAll('.pm-option').forEach(function(l){
    l.classList.remove('selected');
    l.setAttribute('aria-checked', 'false');
  });
  chosenLabel.classList.add('selected');
  chosenLabel.setAttribute('aria-checked', 'true');
}
  
// التحقق قبل الإرسال
document.getElementById('paymentForm').addEventListener('submit', function(e){
  const chosen = document.querySelector('input[name="method"]:checked');
  if (!chosen) {
    e.preventDefault();
    alert('Please choose a payment method.');
  }
});