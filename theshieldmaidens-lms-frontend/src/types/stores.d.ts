// Type definitions for Pinia stores

import type { User } from '@/types';

declare module '@/stores/auth' {
  export interface AuthState {
    user: User | null;
    token: string | null;
    returnUrl: string | null;
  }

  export interface AuthGetters {
    isAuthenticated: boolean;
    isAdmin: boolean;
    isInstructor: boolean;
    isStudent: boolean;
    isParent: boolean;
    canManageCourses: boolean;
    canViewReports: boolean;
    canManageUsers: boolean;
    canAccessAdminPanel: boolean;
    canAccessInstructorPanel: boolean;
  }

  export interface AuthActions {
    login(email: string, password: string): Promise<User>;
    register(userData: { name: string; email: string; password: string; password_confirmation: string }): Promise<void>;
    completeOAuthLogin(accessToken: string): Promise<void>;
    logout(): Promise<void>;
    fetchUser(): Promise<User | null>;
    refreshToken(): Promise<string | null>;
    setReturnUrl(url: string): void;
  }

  export function useAuthStore(): AuthState & AuthGetters & AuthActions;
}

// Type definitions for User
declare module '@/types' {
  export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_admin: boolean;
    role: string;
  }
}
