<?php
/**
 * FAQ View
 */
?>
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">الأسئلة الشائعة</h2>
            <p class="text-muted">أجبنا هنا على أكثر الأسئلة تكراراً</p>
        </div>
        <div class="accordion" id="faqAccordion">
            <?php if (!empty($faqs)): $i = 0; foreach ($faqs as $faq): $i++; ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $i ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $i ?>">
                            <?= $this->escape($faq['question']) ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?= $this->escape($faq['answer']) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</section>

