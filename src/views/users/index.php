<?php
$users = $users ?? [];
$roleLabels = $roleLabels ?? [];
$search = $search ?? '';
$success = $success ?? null;
$error = $error ?? null;
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <h2 class="mb-0">Kelola Pengguna</h2>
    <a href="/users/create" class="btn btn-primary mt-2 mt-md-0">
        <i class="fas fa-user-plus"></i> Tambah Pengguna
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="search" class="form-label">Cari Pengguna</label>
                <input type="text" class="form-control" id="search" name="search"
                       placeholder="Username atau nama..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-3 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
            <div class="col-md-3 col-lg-2">
                <a href="/users" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 18%">Username</th>
                        <th style="width: 24%">Nama</th>
                        <th style="width: 18%">Role</th>
                        <th style="width: 20%">Dibuat</th>
                        <th style="width: 20%">Diperbarui</th>
                        <th style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada data pengguna.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= htmlspecialchars($roleLabels[$user['role']] ?? ucfirst(str_replace('_', ' ', $user['role']))) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= isset($user['created_at']) ? htmlspecialchars(date('d M Y H:i', strtotime($user['created_at']))) : '-' ?>
                                </td>
                                <td>
                                    <?= isset($user['updated_at']) ? htmlspecialchars(date('d M Y H:i', strtotime($user['updated_at']))) : '-' ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/users/<?= urlencode((string)$user['id']) ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php
                                        $currentUserId = $_SESSION['user_id'] ?? 0;
                                        $canDelete = ((int)$user['id'] !== $currentUserId);
                                        ?>
                                        <?php if ($canDelete): ?>
                                        <a href="/users/<?= urlencode((string)$user['id']) ?>/delete"
                                           class="btn btn-sm btn-outline-danger"
                                           title="Hapus"
                                           onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
