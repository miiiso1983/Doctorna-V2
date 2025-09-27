<?php
// Doctor Schedule Management View
?>
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>إدارة الجدول</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= $this->url('/doctor/schedule') ?>">
          <?= $this->csrfField() ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr>
                  <th>اليوم</th>
                  <th>متاح؟</th>
                  <th>بداية الدوام</th>
                  <th>نهاية الدوام</th>
                  <th>بداية الاستراحة</th>
                  <th>نهاية الاستراحة</th>
                  <th>العدد الأقصى للمواعيد</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($working_days as $dayKey => $dayLabel): ?>
                  <?php
                    $row = null;
                    if (!empty($schedule)) {
                      foreach ($schedule as $s) {
                        if (($s['day_of_week'] ?? '') === $dayKey) { $row = $s; break; }
                      }
                    }
                  ?>
                  <tr>
                    <td><?= $this->escape($dayLabel) ?></td>
                    <td>
                      <input class="form-check-input" type="checkbox" name="schedule[<?= $dayKey ?>][is_available]" value="1" <?= (!empty($row) && (int)$row['is_available'] === 1) ? 'checked' : '' ?>>
                    </td>
                    <td><input type="time" class="form-control" name="schedule[<?= $dayKey ?>][start_time]" value="<?= $this->escape($row['start_time'] ?? '09:00') ?>"></td>
                    <td><input type="time" class="form-control" name="schedule[<?= $dayKey ?>][end_time]" value="<?= $this->escape($row['end_time'] ?? '17:00') ?>"></td>
                    <td><input type="time" class="form-control" name="schedule[<?= $dayKey ?>][break_start]" value="<?= $this->escape($row['break_start'] ?? '13:00') ?>"></td>
                    <td><input type="time" class="form-control" name="schedule[<?= $dayKey ?>][break_end]" value="<?= $this->escape($row['break_end'] ?? '14:00') ?>"></td>
                    <td><input type="number" class="form-control" min="1" max="100" name="schedule[<?= $dayKey ?>][max_appointments]" value="<?= (int)($row['max_appointments'] ?? 20) ?>"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="text-end">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>حفظ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

