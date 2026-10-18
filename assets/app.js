document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('searchInput');
  const pills = document.querySelectorAll('.pill');
  const cards = [...document.querySelectorAll('.field-card')];
  const empty = document.getElementById('emptyState');
  let sport = 'الكل';
  function filterCards(){
    const term = (search.value || '').trim().toLowerCase(); let count = 0;
    cards.forEach(card => { const matchesSport = sport === 'الكل' || card.dataset.sport === sport; const matchesSearch = !term || card.dataset.search.toLowerCase().includes(term); const show = matchesSport && matchesSearch; card.style.display = show ? '' : 'none'; if(show) count++; });
    empty.hidden = count !== 0;
  }
  search.addEventListener('input', filterCards);
  pills.forEach(p => p.addEventListener('click', () => { pills.forEach(x => x.classList.remove('active')); p.classList.add('active'); sport = p.dataset.sport; filterCards(); }));
  document.querySelectorAll('.heart').forEach(btn => btn.addEventListener('click', () => { btn.textContent = btn.textContent === '♡' ? '♥' : '♡'; btn.style.color = btn.textContent === '♥' ? '#d87878' : ''; }));
  const modal = document.getElementById('bookingModal'), form = document.getElementById('bookingForm'), message = document.getElementById('formMessage');
  const dateInput = document.getElementById('bookingDate'); dateInput.min = new Date().toISOString().split('T')[0];
  document.querySelectorAll('.book-btn').forEach(btn => btn.addEventListener('click', () => { document.getElementById('modalFieldName').textContent = btn.dataset.name; document.getElementById('fieldName').value = btn.dataset.name; document.getElementById('fieldId').value = btn.dataset.id; modal.hidden = false; document.body.style.overflow = 'hidden'; }));
  function close(){modal.hidden = true; document.body.style.overflow = ''; form.reset(); message.textContent = ''; message.className = 'form-message';}
  document.getElementById('closeModal').addEventListener('click', close); modal.addEventListener('click', e => { if(e.target === modal) close(); });
  form.addEventListener('submit', async e => { e.preventDefault(); message.textContent = 'جارٍ حفظ الحجز...'; message.className = 'form-message'; const data = new FormData(form); try { const res = await fetch('api.php', {method:'POST', body:data}); const out = await res.json(); if(!out.success) throw new Error(out.message); message.textContent = out.message; message.className = 'form-message success'; setTimeout(close, 1800); } catch(err) { message.textContent = err.message || 'تعذر حفظ الحجز، حاول مرة أخرى.'; message.className = 'form-message error'; } });
});
