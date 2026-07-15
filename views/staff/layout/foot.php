        </main>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3" id="staffToastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/staff/staff-app.js"></script>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
    <script src="<?= staff_e($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
