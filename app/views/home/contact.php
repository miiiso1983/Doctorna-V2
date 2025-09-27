<?php
/**
 * Contact Page View
 */
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">اتصل بنا</h2>
            <p class="text-muted">يسعدنا تواصلك معنا في أي وقت</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="POST" action="<?= $this->url('/contact') ?>" class="card border-0 shadow-sm p-4">
                    <?= CSRF::field() ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control" required value="<?= $this->escape($this->old('name')) ?>">
                            <?php if ($this->hasError('name')): ?><div class="text-danger small"><?= $this->escape($this->error('name')[0]) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control" required value="<?= $this->escape($this->old('email')) ?>">
                            <?php if ($this->hasError('email')): ?><div class="text-danger small"><?= $this->escape($this->error('email')[0]) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الموضوع</label>
                        <input type="text" name="subject" class="form-control" required value="<?= $this->escape($this->old('subject')) ?>">
                        <?php if ($this->hasError('subject')): ?><div class="text-danger small"><?= $this->escape($this->error('subject')[0]) ?></div><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسالة</label>
                        <textarea name="message" rows="5" class="form-control" required><?= $this->escape($this->old('message')) ?></textarea>
                        <?php if ($this->hasError('message')): ?><div class="text-danger small"><?= $this->escape($this->error('message')[0]) ?></div><?php endif; ?>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary px-5">إرسال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

