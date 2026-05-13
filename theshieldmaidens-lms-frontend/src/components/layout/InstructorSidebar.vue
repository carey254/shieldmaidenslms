<template>
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">
        <img :src="PUBLIC_BRAND_LOGO" alt="" class="brand-logo" />
      </div>
      <div class="brand-text">
        <div class="brand-title">Shield Maidens</div>
        <div class="brand-subtitle">Instructor</div>
      </div>
    </div>

    <nav class="nav">
      <RouterLink class="nav-item" to="/instructor/dashboard" :class="{ active: isActive('/instructor/dashboard') }">
        <span class="icon">⌂</span>
        <span class="label">Dashboard</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/courses" :class="{ active: isActive('/instructor/courses') }">
        <span class="icon">▦</span>
        <span class="label">Courses</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/sessions" :class="{ active: isActive('/instructor/sessions') }">
        <span class="icon">▣</span>
        <span class="label">Sessions</span>
      </RouterLink>

      <RouterLink class="nav-item" to="/instructor/settings" :class="{ active: isActive('/instructor/settings') }">
        <span class="icon">⚙</span>
        <span class="label">Settings</span>
      </RouterLink>
    </nav>

    <div class="spacer" />

    <div class="profile">
      <div class="avatar">{{ (user?.name || 'I').charAt(0).toUpperCase() }}</div>
      <div class="who">
        <div class="name">{{ user?.name || 'Instructor' }}</div>
        <div class="email">{{ user?.email || '' }}</div>
      </div>
      <button class="logout" type="button" @click="logout">↩</button>
    </div>
  </aside>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { PUBLIC_BRAND_LOGO } from '@/config/branding'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const user = computed(() => authStore.user as { name?: string; email?: string } | null)

const isActive = (path: string) => route.path === path

const logout = async () => {
  await authStore.logout()
  router.push('/instructor/login')
}
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 96px;
  padding: 18px 14px;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  z-index: 1002;
}

.brand {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 10px;
  align-items: center;
  margin-bottom: 18px;
}

.brand-mark {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #e2e8f0;
  display: grid;
  place-items: center;
  overflow: hidden;
}

.brand-logo {
  width: 34px;
  height: 34px;
  object-fit: contain;
}

.brand-text {
  display: none;
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 8px;
}

.nav-item {
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #475569;
  text-decoration: none;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  transition: background 180ms ease, transform 180ms ease, border-color 180ms ease;
}

.nav-item:hover {
  transform: translateY(-1px);
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.nav-item.active {
  background: #eef2ff;
  border-color: #c7d2fe;
  color: #3730a3;
}

.icon {
  font-size: 18px;
  line-height: 1;
}

.label {
  display: none;
}

.spacer {
  flex: 1;
}

.profile {
  display: grid;
  gap: 10px;
  place-items: center;
}

.avatar {
  width: 40px;
  height: 40px;
  border-radius: 999px;
  background: #e2e8f0;
  color: #0f172a;
  display: grid;
  place-items: center;
  font-weight: 700;
}

.who {
  display: none;
}

.logout {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  cursor: pointer;
}

.logout:hover {
  background: #e2e8f0;
}

@media (min-width: 1100px) {
  .sidebar {
    width: 260px;
    padding: 20px 16px;
  }

  .brand-text {
    display: block;
  }

  .brand-title {
    color: #0f172a;
    font-weight: 700;
    font-size: 14px;
  }

  .brand-subtitle {
    color: #64748b;
    font-size: 12px;
    margin-top: 2px;
  }

  .nav-item {
    grid-template-columns: 36px 1fr;
    justify-content: start;
    padding: 0 12px;
    place-items: center start;
  }

  .label {
    display: inline;
    font-size: 13px;
    font-weight: 600;
  }

  .profile {
    grid-template-columns: 40px 1fr 40px;
    place-items: center start;
  }

  .who {
    display: block;
    min-width: 0;
  }

  .name {
    color: #0f172a;
    font-weight: 700;
    font-size: 13px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .email {
    color: #64748b;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
}
</style>

