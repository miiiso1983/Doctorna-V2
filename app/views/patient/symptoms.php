<?php
// Patient Symptoms Entry View
?>
<div class="row mb-4">
  <div class="col-12">
    <h5 class="mb-3"><i class="fas fa-notes-medical me-2"></i>كتابة الأعراض</h5>
    <p class="text-muted">اكتب الأعراض التي تشعر بها للحصول على توصيات بالتخصصات المناسبة والبحث عن الأطباء.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <div class="mb-3">
      <label class="form-label">الأعراض</label>
      <textarea id="symptoms-text" class="form-control" rows="4" placeholder="مثال: صداع شديد منذ يومين مع غثيان وخفقان..."></textarea>
    </div>
    <div class="d-flex gap-2">
      <button id="btn-recommend" class="btn btn-primary">
        <i class="fas fa-magic me-2"></i>اقتراح التخصص المناسب
      </button>
      <a href="/patient/search-doctors" class="btn btn-outline-secondary">
        <i class="fas fa-search me-2"></i>البحث اليدوي عن طبيب
      </a>
      <a href="/patient/map-search" class="btn btn-outline-info">
        <i class="fas fa-map-marker-alt me-2"></i>البحث بالخريطة
      </a>
    </div>
  </div>
</div>

<div class="card" id="recommendations-card" style="display:none;">
  <div class="card-header"><strong>التخصصات المقترحة</strong></div>
  <div class="card-body" id="recommendations-body">
    <div class="text-muted">لا توجد توصيات بعد</div>
  </div>
</div>

<script>
(function(){
  const btn = document.getElementById('btn-recommend');
  const txt = document.getElementById('symptoms-text');
  const card = document.getElementById('recommendations-card');
  const body = document.getElementById('recommendations-body');

  btn.addEventListener('click', function(){
    const q = (txt.value || '').trim();
    if (!q) {
      body.innerHTML = '<div class="alert alert-warning">يرجى كتابة الأعراض أولاً</div>';
      card.style.display = 'block';
      return;
    }
    body.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">جاري التحميل...</span></div></div>';
    card.style.display = 'block';

    const url = '/ajax/symptoms/recommend?q=' + encodeURIComponent(q);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(r => r.json())
      .then(data => {
        const recs = (data && data.data && data.data.recommendations) ? data.data.recommendations : [];
        if (!recs.length) {
          body.innerHTML = '<div class="text-muted">لا توجد توصيات حالياً</div>';
          return;
        }
        body.innerHTML = recs.map(r => {
          const href = '/patient/search-doctors?specialization=' + encodeURIComponent(r.id);
          return (
            '<div class="d-flex align-items-center justify-content-between border-bottom py-2">' +
              '<div class="d-flex align-items-center">' +
                '<i class="fas fa-stethoscope text-primary me-2"></i>' +
                '<strong>' + (r.name || 'تخصص') + '</strong>' +
              '</div>' +
              '<div class="d-flex align-items-center gap-2">' +
                (r.score ? ('<span class="badge bg-secondary">' + (r.score.toFixed ? r.score.toFixed(2) : r.score) + '</span>') : '') +
                '<a class="btn btn-sm btn-outline-primary" href="' + href + '">عرض الأطباء</a>' +
              '</div>' +
            '</div>'
          );
        }).join('');
      })
      .catch(() => {
        body.innerHTML = '<div class="alert alert-danger">تعذر جلب التوصيات الآن</div>';
      });
  });
})();
</script>

