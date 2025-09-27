<?php
/**
 * Services Page View
 */
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">التخصصات والخدمات</h2>
            <p class="text-muted">اختر التخصص المناسب لحالتك الصحية</p>
        </div>

        <?php if (empty($specializations)): ?>
            <div class="alert alert-info">لا توجد تخصصات متاحة حالياً.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($specializations as $specialization): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100 specialization-card">
                            <div class="card-body text-center">
                                <div class="mb-3" style="color: <?= $specialization['color'] ?>">
                                    <i class="<?= $specialization['icon'] ?> fa-3x"></i>
                                </div>
                                <h5 class="card-title fw-bold"><?= $this->escape($specialization['name']) ?></h5>
                                <p class="text-muted small mb-3"><?= $this->escape($specialization['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-light text-dark">
                                        <?= $specialization['doctor_count'] ?> طبيب
                                    </span>
                                    <a href="<?= $this->url('/patient/doctors?specialization=' . $specialization['id']) ?>" class="btn btn-sm btn-outline-primary">
                                        عرض الأطباء
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

