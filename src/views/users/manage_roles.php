<?php
$users = $users ?? [];
$roleLabels = $roleLabels ?? [];
$success = $success ?? null;
$error = $error ?? null;

$roleName = function (string $role) use ($roleLabels): string {
    return $roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role));
};
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h2 class="mb-0">Pengaturan Peran Pengguna</h2>
        <p class="text-muted mb-0">Kelola akun multi-role dan navigasi cepat ke daftar pengguna di satu tempat.</p>
    </div>
    <div class="btn-group">
        <a href="/users" class="btn btn-outline-secondary">
            <i class="fas fa-table"></i> Daftar Pengguna
        </a>
        <a href="/users/create" class="btn btn-primary">
            <i class="fas fa-user-plus"></i> Tambah Pengguna
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="alert alert-info mb-0">
                Belum ada pengguna yang terdaftar.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="min-width: 220px;">Nama</th>
                        <th>Username</th>
                        <th>Peran Utama</th>
                        <th>Peran Tambahan</th>
                        <th style="min-width: 220px;">Tambah Peran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                            $userId = (int)($user['id'] ?? 0);
                            $primaryRole = (string)($user['role'] ?? '');
                            $roles = $user['roles'] ?? [];
                            $assignableRoles = $user['assignable_roles'] ?? [];
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($user['name'] ?? '-') ?></strong><br>
                                <small class="text-muted">#<?= htmlspecialchars((string)$userId) ?></small>
                            </td>
                            <td><?= htmlspecialchars($user['username'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?= htmlspecialchars($roleName($primaryRole)) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $additionalRoles = array_values(array_filter($roles, function ($role) use ($primaryRole) {
                                        return $role !== $primaryRole;
                                    }));
                                ?>
                                <?php if (empty($additionalRoles)): ?>
                                    <span class="text-muted">Belum ada peran tambahan</span>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($additionalRoles as $role): ?>
                                            <form action="/users/roles/remove" method="POST" class="d-inline">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars((string)$userId) ?>">
                                                <input type="hidden" name="role" value="<?= htmlspecialchars($role) ?>">
                                                <span class="badge bg-secondary d-inline-flex align-items-center gap-2">
                                                    <?= htmlspecialchars($roleName($role)) ?>
                                                    <button type="submit" class="btn btn-sm btn-link text-white p-0"
                                                            onclick="return confirm('Hapus peran ini dari pengguna?');">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </span>
                                            </form>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($assignableRoles)): ?>
                                    <span class="text-muted">Semua peran sudah ditambahkan</span>
                                <?php else: ?>
                                    <form action="/users/roles/add" method="POST" class="row g-2 align-items-center">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string)$userId) ?>">
                                        <div class="col">
                                            <select name="role" class="form-select form-select-sm">
                                                <?php foreach ($assignableRoles as $assignableRole): ?>
                                                    <option value="<?= htmlspecialchars($assignableRole) ?>">
                                                        <?= htmlspecialchars($roleName($assignableRole)) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-plus"></i> Tambah
                                            </button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
