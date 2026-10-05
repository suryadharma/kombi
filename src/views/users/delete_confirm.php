<?php
$user = $user ?? [];
$roleLabels = $roleLabels ?? [];
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus Pengguna
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-danger">
                    <strong>Peringatan:</strong> Tindakan ini tidak dapat dibatalkan!
                </div>

                <p>Anda akan menghapus pengguna berikut:</p>

                <table class="table table-bordered table-sm">
                    <tr>
                        <th style="width: 30%">Username</th>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>
                            <span class="badge bg-secondary">
                                <?= htmlspecialchars($roleLabels[$user['role']] ?? ucfirst(str_replace('_', ' ', $user['role']))) ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle"></i>
                    Semua data terkait pengguna ini (role, assignments, dll) akan dihapus secara permanen.
                </p>

                <form method="POST" action="/users/<?= urlencode((string)$user['id']) ?>/delete">
                    <?= Csrf::field() ?>

                    <div class="mb-3">
                        <label for="confirm" class="form-label">
                            Ketik <strong>DELETE</strong> untuk mengkonfirmasi penghapusan:
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="confirm" 
                               name="confirm" 
                               required
                               placeholder="Ketik DELETE"
                               pattern="DELETE"
                               oninput="checkConfirm()">
                        <div class="form-text">Ini diperlukan untuk mencegah penghapusan tidak sengaja.</div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="/users" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" 
                                id="deleteBtn"
                                class="btn btn-danger" 
                                disabled
                                onclick="return confirm('Apakah Anda YAKIN ingin menghapus pengguna ini? Tindakan ini TIDAK DAPAT dibatalkan!')">
                            <i class="fas fa-trash"></i> Ya, Hapus Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function checkConfirm() {
    const input = document.getElementById('confirm');
    const btn = document.getElementById('deleteBtn');
    btn.disabled = input.value !== 'DELETE';
}
</script>
