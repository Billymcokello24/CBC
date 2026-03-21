import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface AuthData {
    user: any;
    roles: string[];
    permissions: string[];
}

export function usePermissions() {
    const page = usePage();

    const auth = computed<AuthData>(() => ({
        user: (page.props as any).auth?.user ?? null,
        roles: (page.props as any).auth?.roles ?? [],
        permissions: (page.props as any).auth?.permissions ?? [],
    }));

    /**
     * Check if the user has a specific permission.
     */
    const can = (permission: string): boolean => {
        return auth.value.permissions.includes(permission);
    };

    /**
     * Check if the user has ANY of the given permissions.
     */
    const canAny = (...permissions: string[]): boolean => {
        return permissions.some(p => auth.value.permissions.includes(p));
    };

    /**
     * Check if the user has ALL of the given permissions.
     */
    const canAll = (...permissions: string[]): boolean => {
        return permissions.every(p => auth.value.permissions.includes(p));
    };

    /**
     * Check if the user has a specific role.
     */
    const hasRole = (role: string): boolean => {
        return auth.value.roles.includes(role);
    };

    /**
     * Check if the user has ANY of the given roles.
     */
    const hasAnyRole = (...roles: string[]): boolean => {
        return roles.some(r => auth.value.roles.includes(r));
    };

    /**
     * Check if the user is an admin-level user (super_admin, school_admin, principal, deputy_principal).
     */
    const isAdmin = computed(() => hasAnyRole('super_admin', 'school_admin', 'principal', 'deputy_principal'));

    /**
     * Check if the user is a teacher-type user (teacher, class_teacher, hod).
     */
    const isTeacher = computed(() => hasAnyRole('teacher', 'class_teacher', 'hod'));

    /**
     * Check if the user is a parent/guardian.
     */
    const isParent = computed(() => hasRole('parent'));

    /**
     * Check if the user is a student.
     */
    const isStudent = computed(() => hasRole('student'));

    return {
        auth,
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        isAdmin,
        isTeacher,
        isParent,
        isStudent,
    };
}
