import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';

import { useAuthStore } from '@/stores/auth';



// Import views

import HomeView from '@/views/HomeView.vue';

import LoginView from '@/views/auth/LoginView.vue';
import ExamView from '@/views/student/ExamView.vue';
import CourseLearningView from '@/views/student/CourseLearningView.vue';
import WeekQuizView from '@/views/student/WeekQuizView.vue';


import RegisterView from '@/views/auth/RegisterView.vue';
import ForgotPasswordView from '@/views/auth/ForgotPasswordView.vue';

import ChangePasswordView from '@/views/auth/ChangePasswordView.vue';
import AnnouncementsView from '@/views/student/AnnouncementsView.vue';
import AdminAnnouncementsView from '@/views/admin/AnnouncementsView.vue';



const routes: RouteRecordRaw[] = [

  {

    path: '/',

    name: 'home',

    component: HomeView,

    meta: { title: 'Home - The Shield Maidens' }

  },

  {

    path: '/login',

    name: 'login',

    component: LoginView,

    meta: { title: 'Login - The Shield Maidens', loginEntry: true }

  },

  {
    path: '/instructor/login',
    name: 'instructor-login',
    redirect: '/login',
    meta: { title: 'Instructor Login - The Shield Maidens' }
  },

  {
    path: '/admin/login',
    name: 'admin-login',
    redirect: '/login',
    meta: { title: 'Admin Login - The Shield Maidens' }
  },

  {

    path: '/register',

    name: 'register',

    component: RegisterView,

    meta: { title: 'Create Account - The Shield Maidens', guestOnly: true }

  },

  {

    path: '/forgot-password',

    name: 'forgot-password',

    component: ForgotPasswordView,

    meta: { title: 'Forgot Password - The Shield Maidens', guestOnly: true }

  },

  {

    path: '/change-password',

    name: 'change-password',

    component: ChangePasswordView,

    meta: { title: 'Change Password - The Shield Maidens', requiresAuth: true }

  },

  {

    path: '/student/announcements',

    name: 'student-announcements',

    component: AnnouncementsView,

    meta: { title: 'Announcements - The Shield Maidens', requiresAuth: true }

  },

  {

    path: '/admin/announcements',

    name: 'admin-announcements',

    component: AdminAnnouncementsView,

    meta: { title: 'Manage Announcements - The Shield Maidens', requiresAuth: true, requiresAdmin: true }

  },

  
  {

    path: '/courses',

    name: 'courses',

    component: () => import('@/views/student/CoursesView.vue'),

    meta: { title: 'Our Curriculum - The Shield Maidens' }

  },

  // Instructor Routes

  {

    path: '/instructor',

    redirect: '/instructor/dashboard'

  },

  {

    path: '/instructor/dashboard',

    name: 'instructor-dashboard',

    component: () => import('@/views/instructor/DashboardView.vue'),

    meta: { requiresAuth: true, requiresInstructor: true, title: 'Instructor Dashboard - The Shield Maidens' }

  },

  {

    path: '/instructor/courses',

    name: 'instructor-courses',

    component: () => import('@/views/instructor/CoursesView.vue'),

    meta: { requiresAuth: true, requiresInstructor: true, title: 'My Courses - The Shield Maidens' }

  },

  {

    path: '/instructor/upload',

    name: 'instructor-upload',

    component: () => import('@/views/instructor/ContentUploadView.vue'),

    meta: { requiresAuth: true, requiresInstructor: true, title: 'Upload Content - The Shield Maidens' }

  },

  {

    path: '/instructor/settings',

    name: 'instructor-settings',

    component: () => import('@/views/instructor/SettingsView.vue'),

    meta: { requiresAuth: true, requiresInstructor: true, title: 'Settings - The Shield Maidens' }

  },

  // Admin Routes

  {

    path: '/admin',

    redirect: '/admin/dashboard'

  },

  {

    path: '/admin/dashboard',
    name: 'admin-dashboard',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/DashboardView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Admin Dashboard - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/users',
    name: 'admin-users',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/UsersView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'User Management - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/facilitators',
    name: 'admin-facilitators',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/FacilitatorsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Facilitators - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/courses',
    name: 'admin-courses',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/CoursesView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Course Management - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/categories',
    name: 'admin-categories',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/CategoriesView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Categories - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/enrollments',
    name: 'admin-enrollments',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/EnrollmentsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Enrollments - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/assignments',
    name: 'admin-assignments',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/AssignmentsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Assignments - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/exams',
    name: 'admin-exams',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/ExamsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Exams - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/reports',
    name: 'admin-reports',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/ReportsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Reports - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/analytics',
    name: 'admin-analytics',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/AnalyticsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Analytics - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/settings',
    name: 'admin-settings',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/SettingsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Settings - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/automation',
    name: 'admin-automation',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/AutomationView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Automation - The Shield Maidens' }
      }
    ]
  },

  {

    path: '/admin/notifications',
    name: 'admin-notifications',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/NotificationsView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Notifications - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/profile',
    name: 'admin-profile',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/ProfileView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Profile Settings - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/security',
    name: 'admin-security',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/SecurityView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Security - The Shield Maidens' }
      }
    ]
  },

  {
    path: '/admin/preferences',
    name: 'admin-preferences',
    component: () => import('@/views/admin/AdminWrapper.vue'),
    children: [
      {
        path: '',
        component: () => import('@/views/admin/PreferencesView.vue'),
        meta: { requiresAuth: true, requiresAdmin: true, title: 'Preferences - The Shield Maidens' }
      }
    ]
  },

  // Support routes (public)

  {

    path: '/support',

    name: 'support',

    component: () => import('@/views/support/SupportView.vue'),

    meta: { title: 'Support - The Shield Maidens' }

  },

  // Student routes

  {

    path: '/dashboard',

    name: 'student-dashboard',

    component: () => import('@/views/student/StudentDashboard.vue'),

    meta: { requiresAuth: true, title: 'Dashboard - The Shield Maidens' }

  },

  {

    path: '/course/:id/learn',

    name: 'course-learning',

    component: () => import('@/views/student/CourseLearningView.vue'),

    meta: { requiresAuth: true, title: 'Course Learning - The Shield Maidens' }

  },

  {

    path: '/course/:id/curriculum',

    name: 'course-curriculum',

    component: () => import('@/views/student/CourseCurriculumView.vue'),

    meta: { requiresAuth: true, title: 'Course Curriculum - The Shield Maidens' }

  },

  {

    path: '/course/:week/quiz',

    name: 'week-quiz',

    component: () => import('@/views/student/WeekQuizView.vue'),

    meta: { requiresAuth: true, title: 'Week Quiz - The Shield Maidens' }

  },

  {

    path: '/settings',

    name: 'student-settings',

    component: () => import('@/views/student/SettingsView.vue'),

    meta: { requiresAuth: true, title: 'Settings - The Shield Maidens' }

  },

  {

    path: '/sessions',

    name: 'student-sessions',

    component: () => import('@/views/student/ClassSessionsView.vue'),

    meta: { requiresAuth: true, title: 'Class Sessions - The Shield Maidens' }

  },

  {

    path: '/submissions',

    name: 'student-submissions',

    component: () => import('@/views/student/SubmissionsView.vue'),

    meta: { requiresAuth: true, title: 'Submissions - The Shield Maidens' }

  },

  {

    path: '/groups',

    name: 'student-groups',

    component: () => import('@/views/student/GroupsView.vue'),

    meta: { requiresAuth: true, title: 'Groups - The Shield Maidens' }

  },

  {

    path: '/community',

    name: 'student-community',

    component: () => import('@/views/student/CommunityView.vue'),

    meta: { requiresAuth: true, title: 'Community - The Shield Maidens' }
  },

  {
    path: '/certificates',
    name: 'student-certificates',
    component: () => import('@/views/student/CertificatesView.vue'),
    meta: { requiresAuth: true, title: 'Certificates - The Shield Maidens' }
  },

  {
    path: '/my-courses',
    name: 'student-my-courses',
    component: () => import('@/views/student/MyCoursesView.vue'),
    meta: { requiresAuth: true, title: 'My Courses - The Shield Maidens' }
  },

  // 404 catch-all route
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/errors/NotFoundView.vue'),
    meta: { title: 'Page Not Found - The Shield Maidens' }
  }

];



const router = createRouter({

  history: createWebHistory(import.meta.env.BASE_URL),

  routes,

  scrollBehavior(to, from, savedPosition) {

    // Always scroll to top when navigating to a new route

    return { top: 0 };

  }

});



// Update page title on route change

router.beforeEach((to, from, next) => {

  document.title = to.meta.title as string || 'The Shield Maidens';

  next();

});



// Authentication guard

router.beforeEach((to, from, next) => {

  const authStore = useAuthStore();

  const isAuthenticated = !!authStore.token;

  const isAdmin = authStore.isAdmin;

  const isInstructor = authStore.isInstructor;

  // Already logged in: do not show login forms again
  if (to.meta.loginEntry && isAuthenticated) {
    if (isAdmin) {
      next({ name: 'admin-dashboard' });
      return;
    }
    if (isInstructor) {
      next({ name: 'instructor-dashboard' });
      return;
    }
    next({ name: 'student-dashboard' });
    return;
  }

  if (to.meta.requiresAuth && !isAuthenticated) {
    authStore.setReturnUrl(to.fullPath);

    next({ name: 'login', query: { redirect: to.fullPath } });

    return;
  }

  if (to.meta.requiresAdmin && !isAdmin) {

    next({ name: 'student-dashboard' }); // Redirect non-admin users to student dashboard

  } else if (to.meta.requiresInstructor && !isInstructor) {

    next({ name: 'student-dashboard' }); // Redirect non-instructor users to student dashboard

  } else if (to.meta.guestOnly && isAuthenticated) {

    if (isAdmin) {
      next({ name: 'admin-dashboard' });
    } else if (isInstructor) {
      next({ name: 'instructor-dashboard' });
    } else {
      next({ name: 'student-dashboard' });
    }

  } else {

    next();

  }

});



export default router;

