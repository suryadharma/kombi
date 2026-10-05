<?php

class AssignmentRoleHelper
{
    private const ROLE_ORDER = [
        'pembimbing_1',
        'pembimbing_2',
        'penguji_1',
        'penguji_2',
        'penguji_3',
        'sekretaris'
    ];

    public static function fetchRoles(PDO $db, int $studentId, int $lecturerId): array
    {
        $order = implode("','", self::ROLE_ORDER);
        $query = "
            SELECT role
            FROM assignments
            WHERE student_id = :student_id AND lecturer_id = :lecturer_id
            ORDER BY FIELD(role, '{$order}') ASC, role ASC
        ";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->bindValue(':lecturer_id', $lecturerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function parseConcatenated(?string $rawRoles): array
    {
        if (!is_string($rawRoles) || trim($rawRoles) === '') {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(',', $rawRoles)));
        $ordered = [];
        foreach (self::ROLE_ORDER as $roleCode) {
            if (in_array($roleCode, $parts, true)) {
                $ordered[] = $roleCode;
            }
        }
        foreach ($parts as $roleCode) {
            if (!in_array($roleCode, $ordered, true)) {
                $ordered[] = $roleCode;
            }
        }
        return $ordered;
    }

    public static function pickRoleForStage(array $roles, string $stage): ?string
    {
        if (empty($roles)) {
            return null;
        }
        $stage = strtolower($stage);
        if ($stage === 'ujian') {
            foreach ($roles as $role) {
                if (strpos($role, 'penguji') === 0) {
                    return $role;
                }
            }
        }
        if ($stage === 'pra-ujian') {
            foreach ($roles as $role) {
                if (strpos($role, 'pembimbing') === 0) {
                    return $role;
                }
            }
        }
        foreach ($roles as $role) {
            if (strpos($role, 'pembimbing') === 0) {
                return $role;
            }
        }
        foreach ($roles as $role) {
            if (strpos($role, 'penguji') === 0) {
                return $role;
            }
        }
        return $roles[0] ?? null;
    }

    public static function inferLecturerRole(array $roles, string $stage, ?string $fallbackRole = null): ?string
    {
        $assignmentRole = self::pickRoleForStage($roles, $stage);
        return RoleHelper::lecturerRoleFromAssignment($assignmentRole, $fallbackRole);
    }

    public static function hasRolePrefix(array $roles, string $prefix): bool
    {
        foreach ($roles as $role) {
            if (strpos($role, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}
