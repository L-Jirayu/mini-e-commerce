// จัดการ submit form .add-to-cart ด้วย fetch เพื่อไม่ต้องรีเฟรชหน้า
document.addEventListener('submit', async (e) => {
  const form = e.target;
  if (!form.classList.contains('add-to-cart')) return;
  e.preventDefault();
  const formData = new FormData(form);

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      headers: { 'X-Requested-With': 'fetch' },
      body: formData
    });
    const json = await res.json();
    if (json && json.ok) {
      const badge = document.getElementById('cart-badge');
      if (badge) badge.textContent = json.count;
      // ปุ่มเด้ง feedback เล็กน้อย
      form.querySelector('button')?.classList.add('pulse');
      setTimeout(()=>form.querySelector('button')?.classList.remove('pulse'), 300);
    } else {
      window.location.href = '/cart.php';
    }
  } catch (err) {
    // ถ้า fetch ล้มเหลว ให้ fallback ไปหน้า cart
    window.location.href = '/cart.php';
  }
});

// เพิ่มเอฟเฟกต์นิดหน่อย
const style = document.createElement('style');
style.textContent = `.btn.pulse{transform:scale(0.98)}`
document.head.appendChild(style);
