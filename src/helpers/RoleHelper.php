<?php

class RoleHelper
{
    private const LECTURER_ROLES = ['dosen_pembimbing', 'dosen_penguji', 'dosen'];

    public static function lecturerRoles(): array
    {
        return self::LECTURER_ROLES;
    }

    public static function isLecturerRole(?string $role): bool
    {
        if ($role === null) {
            return false;
        }
        return in_array($role, self::LECTURER_ROLES, true);
    }

    public static function normalizeLecturerRole(?string $role): ?string
    {
        if ($role === null) {
            return null;
        }
        if ($role === 'dosen') {
            return 'dosen_pembimbing';
        }
        return $role;
    }

    public static function lecturerRoleFromAssignment(?string $assignmentRole, ?string $fallbackRole = null): ?string
    {
        if (is_string($assignmentRole)) {
            if (strpos($assignmentRole, 'penguji') === 0) {
                return 'dosen_penguji';
            }
            if (strpos($assignmentRole, 'pembimbing') === 0) {
                return 'dosen_pembimbing';
            }
        }
        return self::normalizeLecturerRole($fallbackRole);
    }
}
