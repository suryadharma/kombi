<?php

class UserController extends BaseController {
    
    private $db;
    
    private $roleLabels = [
        'superadmin' => 'Superadmin',
        'kombi' => 'Kombi',
        'dosen' => 'Dosen',
        'dosen_pembimbing' => 'Dosen Pembimbing',
        'dosen_penguji' => 'Dosen Penguji',
        'penguji_eksternal' => 'Penguji Eksternal',
        'mahasiswa' => 'Mahasiswa'
    ];
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    private function requireSuperadmin() {
        $role = $this->getUserRole();
        if ($role !== 'superadmin') {
            $this->redirect('/dashboard');
        }
    }

    private function isValidRole($role) {
        return array_key_exists($role, $this->roleLabels);
    }

    private function findUserById($id) {
        $query = "SELECT id, username, name, role FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public function index() {
        $this->requireAuth();
        $this->requireSuperadmin();

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        try {
            $conditions = [];
            $params = [];

            if ($search !== '') {
                $conditions[] = "(username LIKE :search OR name LIKE :search)";
                $params[':search'] = '%' . $search . '%';
            }

            $query = "SELECT id, username, name, role, created_at, updated_at 
                      FROM users";

            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ', $conditions);
            }

            $query .= " ORDER BY FIELD(role, 'superadmin','kombi','dosen','dosen_pembimbing','dosen_penguji','penguji_eksternal','mahasiswa'), name";

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $users = [];
            $this->setFlash('error', 'Gagal memuat data pengguna: ' . $e->getMessage());
        }

        $this->render('users/index', [
            'users' => $users,
            'search' => $search,
            'roleLabels' => $this->roleLabels,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    public function manageRoles() {
        $this->requireAuth();
        $this->requireSuperadmin();

        try {
            $usersQuery = "SELECT id, username, name, role, created_at, updated_at
                           FROM users
                           ORDER BY FIELD(role, 'superadmin','kombi','dosen','dosen_pembimbing','dosen_penguji','penguji_eksternal','mahasiswa'), name";
            $usersStmt = $this->db->prepare($usersQuery);
            $usersStmt->execute();
            $users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

            $roleMap = [];
            $roleStmt = $this->db->prepare("SELECT user_id, role FROM user_roles ORDER BY role");
            $roleStmt->execute();
            while ($row = $roleStmt->fetch(PDO::FETCH_ASSOC)) {
                $userId = (int)($row['user_id'] ?? 0);
                $roleValue = $row['role'] ?? '';
                if (!isset($roleMap[$userId])) {
                    $roleMap[$userId] = [];
                }
                if (!in_array($roleValue, $roleMap[$userId], true)) {
                    $roleMap[$userId][] = $roleValue;
                }
            }
        } catch (Exception $e) {
            $users = [];
            $roleMap = [];
            $this->setFlash('error', 'Gagal memuat daftar role: ' . $e->getMessage());
        }

        foreach ($users as &$user) {
            $id = (int)($user['id'] ?? 0);
            $roles = $roleMap[$id] ?? [];
            if (!in_array($user['role'], $roles, true)) {
                $roles[] = $user['role'];
            }
            sort($roles);
            $user['roles'] = $roles;

            $assignable = array_values(array_diff(array_keys($this->roleLabels), $roles));
            $user['assignable_roles'] = $assignable;
        }
        unset($user);

        $this->render('users/manage_roles', [
            'title' => 'Pengaturan Peran Pengguna',
            'users' => $users,
            'roleLabels' => $this->roleLabels,
            'success' => $this->getFlash('success'),
            'error' => $this->getFlash('error'),
        ]);
    }

    public function create() {
        $this->requireAuth();
        $this->requireSuperadmin();

        $this->render('users/form', [
            'title' => 'Tambah Pengguna',
            'formAction' => '/users/store',
            'roles' => $this->roleLabels,
            'formData' => [
                'username' => '',
                'name' => '',
                'role' => 'kombi',
            ],
            'errors' => []
        ]);
    }

    public function store() {
        $this->requireAuth();
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/create');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        }
        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if (!$this->isValidRole($role)) {
            $errors[] = 'Role tidak valid.';
        }
        if ($password !== '' && $password !== $passwordConfirmation) {
            $errors[] = 'Konfirmasi password tidak sesuai.';
        }

        if (!empty($errors)) {
            $this->render('users/form', [
                'title' => 'Tambah Pengguna',
                'formAction' => '/users/store',
                'roles' => $this->roleLabels,
                'formData' => [
                    'username' => $username,
                    'name' => $name,
                    'role' => $role,
                ],
                'errors' => $errors
            ]);
            return;
        }

        try {
            $checkQuery = "SELECT id FROM users WHERE username = :username LIMIT 1";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->render('users/form', [
                    'title' => 'Tambah Pengguna',
                    'formAction' => '/users/store',
                    'roles' => $this->roleLabels,
                    'formData' => [
                        'username' => $username,
                        'name' => $name,
                        'role' => $role,
                    ],
                    'errors' => ['Username sudah digunakan.']
                ]);
                return;
            }

            $finalPassword = $password !== '' ? $password : $username;
            $hashedPassword = password_hash($finalPassword, PASSWORD_DEFAULT);

            $insertQuery = "INSERT INTO users (username, password, name, role) VALUES (:username, :password, :name, :role)";
            $insertStmt = $this->db->prepare($insertQuery);
            $insertStmt->bindParam(':username', $username);
            $insertStmt->bindParam(':password', $hashedPassword);
            $insertStmt->bindParam(':name', $name);
            $insertStmt->bindParam(':role', $role);
            $insertStmt->execute();

            $newUserId = (int)$this->db->lastInsertId();
            UserRoleService::ensureUserRole($this->db, $newUserId, $role);

            $this->setFlash('success', 'Pengguna berhasil ditambahkan.');
            $this->redirect('/users');
        } catch (Exception $e) {
            $this->render('users/form', [
                'title' => 'Tambah Pengguna',
                'formAction' => '/users/store',
                'roles' => $this->roleLabels,
                'formData' => [
                    'username' => $username,
                    'name' => $name,
                    'role' => $role,
                ],
                'errors' => ['Gagal menambahkan pengguna: ' . $e->getMessage()]
            ]);
        }
    }

    public function addRole() {
        $this->requireAuth();
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/roles');
            return;
        }

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $role = $_POST['role'] ?? '';

        if ($userId <= 0 || !$this->isValidRole($role)) {
            $this->setFlash('error', 'Data peran tidak valid.');
            $this->redirect('/users/roles');
            return;
        }

        $user = $this->findUserById($userId);
        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/users/roles');
            return;
        }

        try {
            $currentRoles = UserRoleService::getUserRoles($this->db, $userId);
            if (!in_array($role, $currentRoles, true)) {
                UserRoleService::ensureUserRole($this->db, $userId, $role);
            }

            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId) {
                $sessionRoles = $_SESSION['available_roles'] ?? [];
                if (!in_array($role, $sessionRoles, true)) {
                    $sessionRoles[] = $role;
                    sort($sessionRoles);
                    $_SESSION['available_roles'] = $sessionRoles;
                }
            }

            $label = $this->roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role));
            $this->setFlash('success', 'Peran ' . $label . ' berhasil ditambahkan untuk ' . ($user['name'] ?? 'pengguna') . '.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal menambahkan peran: ' . $e->getMessage());
        }

        $this->redirect('/users/roles');
    }

    public function removeRole() {
        $this->requireAuth();
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users/roles');
            return;
        }

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $role = $_POST['role'] ?? '';

        if ($userId <= 0 || !$this->isValidRole($role)) {
            $this->setFlash('error', 'Data peran tidak valid.');
            $this->redirect('/users/roles');
            return;
        }

        $user = $this->findUserById($userId);
        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/users/roles');
            return;
        }

        if ($role === $user['role']) {
            $this->setFlash('error', 'Tidak dapat menghapus peran utama pengguna.');
            $this->redirect('/users/roles');
            return;
        }

        try {
            $currentRoles = UserRoleService::getUserRoles($this->db, $userId);
            if (!in_array($role, $currentRoles, true)) {
                $this->setFlash('error', 'Peran tidak terdaftar pada pengguna ini.');
                $this->redirect('/users/roles');
                return;
            }

            if (count($currentRoles) <= 1) {
                $this->setFlash('error', 'Pengguna harus memiliki minimal satu peran.');
                $this->redirect('/users/roles');
                return;
            }

            UserRoleService::removeUserRole($this->db, $userId, $role);

            if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $userId) {
                $sessionRoles = $_SESSION['available_roles'] ?? [];
                $sessionRoles = array_values(array_filter($sessionRoles, function ($value) use ($role) {
                    return $value !== $role;
                }));

                if (empty($sessionRoles)) {
                    $sessionRoles[] = $_SESSION['role_default'] ?? ($user['role'] ?? 'kombi');
                }

                $_SESSION['available_roles'] = $sessionRoles;

                if (isset($_SESSION['active_role']) && $_SESSION['active_role'] === $role) {
                    $fallbackRole = $_SESSION['role_default'] ?? ($sessionRoles[0] ?? $user['role']);
                    $_SESSION['active_role'] = $fallbackRole;
                    $_SESSION['role'] = $fallbackRole;
                }
            }

            $label = $this->roleLabels[$role] ?? ucwords(str_replace('_', ' ', $role));
            $this->setFlash('success', 'Peran ' . $label . ' berhasil dihapus dari ' . ($user['name'] ?? 'pengguna') . '.');
        } catch (Exception $e) {
            $this->setFlash('error', 'Gagal menghapus peran: ' . $e->getMessage());
        }

        $this->redirect('/users/roles');
    }

    public function edit($params) {
        $this->requireAuth();
        $this->requireSuperadmin();

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if ($id <= 0) {
            $this->redirect('/users');
            return;
        }

        $user = $this->findUserById($id);
        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/users');
            return;
        }

        $this->render('users/form', [
            'title' => 'Edit Pengguna',
            'formAction' => '/users/' . $id . '/update',
            'roles' => $this->roleLabels,
            'formData' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'role' => $user['role'],
            ],
            'errors' => []
        ]);
    }

    public function update($params) {
        $this->requireAuth();
        $this->requireSuperadmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if ($id <= 0) {
            $this->redirect('/users');
            return;
        }

        $user = $this->findUserById($id);
        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/users');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $role = $_POST['role'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $errors = [];

        if ($username === '') {
            $errors[] = 'Username wajib diisi.';
        }
        if ($name === '') {
            $errors[] = 'Nama wajib diisi.';
        }
        if (!$this->isValidRole($role)) {
            $errors[] = 'Role tidak valid.';
        }
        if ($password !== '' && $password !== $passwordConfirmation) {
            $errors[] = 'Konfirmasi password tidak sesuai.';
        }
        if ($user['id'] === (int)($_SESSION['user_id'] ?? 0) && $role !== 'superadmin') {
            $errors[] = 'Anda tidak dapat mengubah role akun Anda sendiri.';
        }

        if (!empty($errors)) {
            $this->render('users/form', [
                'title' => 'Edit Pengguna',
                'formAction' => '/users/' . $id . '/update',
                'roles' => $this->roleLabels,
                'formData' => [
                    'id' => $id,
                    'username' => $username,
                    'name' => $name,
                    'role' => $role,
                ],
                'errors' => $errors
            ]);
            return;
        }

        try {
            $checkQuery = "SELECT id FROM users WHERE username = :username AND id != :id LIMIT 1";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $checkStmt->execute();
            if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
                $this->render('users/form', [
                    'title' => 'Edit Pengguna',
                    'formAction' => '/users/' . $id . '/update',
                    'roles' => $this->roleLabels,
                    'formData' => [
                        'id' => $id,
                        'username' => $username,
                        'name' => $name,
                        'role' => $role,
                    ],
                    'errors' => ['Username sudah digunakan oleh pengguna lain.']
                ]);
                return;
            }

            $this->db->beginTransaction();

            $updateQuery = "UPDATE users SET username = :username, name = :name, role = :role WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':username', $username);
            $updateStmt->bindParam(':name', $name);
            $updateStmt->bindParam(':role', $role);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $updateStmt->execute();

            if ($password !== '') {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $passwordQuery = "UPDATE users SET password = :password WHERE id = :id";
                $passwordStmt = $this->db->prepare($passwordQuery);
                $passwordStmt->bindParam(':password', $hashedPassword);
                $passwordStmt->bindParam(':id', $id, PDO::PARAM_INT);
                $passwordStmt->execute();
            }

            UserRoleService::ensureUserRole($this->db, $id, $role);
            if ($user['role'] !== $role) {
                UserRoleService::removeUserRole($this->db, $id, $user['role']);
            }

            $this->db->commit();

            $this->setFlash('success', 'Perubahan pengguna berhasil disimpan.');
            $this->redirect('/users');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->render('users/form', [
                'title' => 'Edit Pengguna',
                'formAction' => '/users/' . $id . '/update',
                'roles' => $this->roleLabels,
                'formData' => [
                    'id' => $id,
                    'username' => $username,
                    'name' => $name,
                    'role' => $role,
                ],
                'errors' => ['Gagal memperbarui pengguna: ' . $e->getMessage()]
            ]);
        }
    }
    
    public function resetPasswordForm() {
        $this->requireAuth();
        
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('users/reset_password');
    }
    
    public function resetPasswordProcess() {
        $this->requireAuth();
        
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            $this->redirect('/dashboard');
            return;
        }
        
        $userType = $_POST['user_type'] ?? '';
        $userId = $_POST['user_id'] ?? '';
        
        if (empty($userType) || empty($userId)) {
            $this->render('users/reset_password', ['error' => 'Jenis pengguna dan ID pengguna harus dipilih']);
            return;
        }
        
        try {
            $query = "SELECT id, username, name, role FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                $this->render('users/reset_password', ['error' => 'Pengguna tidak ditemukan']);
                return;
            }
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($userType === 'mahasiswa' && $user['role'] !== 'mahasiswa') {
                $this->render('users/reset_password', ['error' => 'Pengguna yang dipilih bukan mahasiswa']);
                return;
            }

            if ($userType === 'dosen' && !RoleHelper::isLecturerRole($user['role'])) {
                $this->render('users/reset_password', ['error' => 'Pengguna yang dipilih bukan dosen']);
                return;
            }
            
            $newPassword = (string)$user['username'];
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            
            if ($updateStmt->execute()) {
                $this->render('users/reset_password', [
                    'success' => 'Password berhasil direset',
                    'user' => $user,
                    'new_password' => $newPassword
                ]);
            } else {
                $this->render('users/reset_password', ['error' => 'Gagal mereset password']);
            }
        } catch (Exception $e) {
            $this->render('users/reset_password', ['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
    
    public function getUsersByType() {
        $this->requireAuth();
        
        $role = $this->getUserRole();
        if ($role !== 'kombi' && $role !== 'superadmin') {
            echo json_encode(['error' => 'Akses ditolak']);
            return;
        }
        
        $userType = $_GET['type'] ?? '';
        
        if (empty($userType)) {
            echo json_encode(['error' => 'Jenis pengguna harus ditentukan']);
            return;
        }
        
        try {
            if ($userType === 'mahasiswa') {
                $query = "SELECT u.id, s.nim as username, s.name 
                         FROM students s 
                         JOIN users u ON s.user_id = u.id 
                         WHERE u.role = 'mahasiswa'
                         ORDER BY s.name";
                $stmt = $this->db->prepare($query);
            } else if ($userType === 'dosen') {
                $query = "SELECT id, username, name 
                         FROM users 
                         WHERE role IN ('dosen', 'dosen_pembimbing', 'dosen_penguji')
                         ORDER BY name";
                $stmt = $this->db->prepare($query);
            } else {
                echo json_encode(['error' => 'Jenis pengguna tidak valid']);
                return;
            }
            
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['users' => $users]);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function delete($params) {
        $this->requireAuth();
        $this->requireSuperadmin();

        $id = isset($params['id']) ? (int)$params['id'] : 0;
        if ($id <= 0) {
            $this->setFlash('error', 'ID pengguna tidak valid.');
            $this->redirect('/users');
            return;
        }

        // Prevent deleting own account
        if ($id === (int)($_SESSION['user_id'] ?? 0)) {
            $this->setFlash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->redirect('/users');
            return;
        }

        $user = $this->findUserById($id);
        if (!$user) {
            $this->setFlash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/users');
            return;
        }

        // Check if deletion is confirmed
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('users/delete_confirm', [
                'user' => $user,
                'roleLabels' => $this->roleLabels,
            ]);
            return;
        }

        // Verify CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!Csrf::validate($csrfToken)) {
            $this->setFlash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            $this->redirect('/users');
            return;
        }

        // Verify confirmation
        $confirm = $_POST['confirm'] ?? '';
        if ($confirm !== 'DELETE') {
            $this->setFlash('error', 'Konfirmasi tidak valid. Penghapusan dibatalkan.');
            $this->redirect('/users');
            return;
        }

        try {
            $this->db->beginTransaction();

            // Delete related data first (due to foreign key constraints)
            // Delete from user_roles
            $deleteRolesQuery = "DELETE FROM user_roles WHERE user_id = :user_id";
            $deleteRolesStmt = $this->db->prepare($deleteRolesQuery);
            $deleteRolesStmt->bindParam(':user_id', $id, PDO::PARAM_INT);
            $deleteRolesStmt->execute();

            // Delete from assignments (if lecturer)
            $deleteAssignmentsQuery = "DELETE FROM assignments WHERE lecturer_id = :user_id";
            $deleteAssignmentsStmt = $this->db->prepare($deleteAssignmentsQuery);
            $deleteAssignmentsStmt->bindParam(':user_id', $id, PDO::PARAM_INT);
            $deleteAssignmentsStmt->execute();

            // Delete from lecturers (if exists)
            $deleteLecturerQuery = "DELETE FROM lecturers WHERE user_id = :user_id";
            $deleteLecturerStmt = $this->db->prepare($deleteLecturerQuery);
            $deleteLecturerStmt->bindParam(':user_id', $id, PDO::PARAM_INT);
            $deleteLecturerStmt->execute();

            // Delete from students (if exists)
            $deleteStudentQuery = "DELETE FROM students WHERE user_id = :user_id";
            $deleteStudentStmt = $this->db->prepare($deleteStudentQuery);
            $deleteStudentStmt->bindParam(':user_id', $id, PDO::PARAM_INT);
            $deleteStudentStmt->execute();

            // Delete the user
            $deleteUserQuery = "DELETE FROM users WHERE id = :id";
            $deleteUserStmt = $this->db->prepare($deleteUserQuery);
            $deleteUserStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $deleteUserStmt->execute();

            $this->db->commit();

            $this->setFlash('success', 'Pengguna ' . htmlspecialchars($user['name']) . ' berhasil dihapus.');
            $this->redirect('/users');
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->setFlash('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
            $this->redirect('/users');
        }
    }
}
