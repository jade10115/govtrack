<template>
  <div class="layout">

    <aside class="sidebar" :class="{ collapsed: collapsed }">

      <div class="logo-section">
        <img src="/favicon.svg" class="logo" />
        <span v-if="!collapsed">GovTrack</span>
      </div>

      <ul class="menu">
        <li>
          <RouterLink to="/admin/dashboard" class="menu-link">
            <i class="fas fa-chart-line"></i>
            <span v-if="!collapsed">Dashboard</span>
          </RouterLink>
        </li>

        <li>
          <RouterLink to="/admin/users" class="menu-link">
            <i class="fas fa-users"></i>
            <span v-if="!collapsed">Users</span>
          </RouterLink>
        </li>

        <li>
          <RouterLink to="/admin/departments" class="menu-link">
            <i class="fas fa-building"></i>
            <span v-if="!collapsed">Departments</span>
          </RouterLink>
        </li>

        <li>
          <RouterLink to="/admin/request-types" class="menu-link">
            <i class="fas fa-list"></i>
            <span v-if="!collapsed">Request Types</span>
          </RouterLink>
        </li>

        <li>
          <RouterLink to="/admin/requests" class="menu-link d-flex justify-content-between align-items-center w-100">
            <div>
              <i class="fas fa-file-alt"></i>
              <span v-if="!collapsed">Requests</span>
            </div>
            
            <span v-if="!collapsed && pendingCount > 0" class="badge bg-danger rounded-pill px-2 py-1">
              {{ pendingCount }}
            </span>
          </RouterLink>
        </li>
      </ul>

    </aside>

    <div class="main-content">

      <header class="navbar">
        <button class="toggle-btn" @click="toggleSidebar">
          <i class="fas fa-bars"></i>
        </button>

        <div class="navbar-right">
          <span class="user-name">
            {{ user.name }}
          </span>

          <button class="logout-btn" @click="logout">
            <i class="fas fa-sign-out-alt"></i>
          </button>
        </div>
      </header>

      <main class="content">
        <slot />
      </main>

    </div>

  </div>
</template>

<script setup>
// FIXED: Imported onMounted from vue
import { ref, onMounted } from 'vue' 
import { RouterLink, useRouter } from 'vue-router'

// FIXED: Imported your API service so fetchPendingCount works
import api from '../services/api.js' 

const router = useRouter()
const collapsed = ref(false)
const pendingCount = ref(0)

const toggleSidebar = () => {
  collapsed.value = !collapsed.value
}

const fetchPendingCount = async () => {
  try {
    const response = await api.get('/assigned-requests') 
    
    // Count how many of my assigned requests are still "Pending"
    const unread = response.data.filter(req => req.status === 'Pending')
    pendingCount.value = unread.length
  } catch (error) {
    console.error("Failed to load pending count", error)
  }
}

// This will now work perfectly since it is imported!
onMounted(() => {
  fetchPendingCount()
})

const user = ref(
  JSON.parse(localStorage.getItem('user') || '{}')
)

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  router.push('/')
}
</script>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
  background: #f5f7fb;
}

.sidebar {
  width: 260px;
  background: #0d47a1;
  color: white;
  transition: .3s;
}

.sidebar.collapsed {
  width: 80px;
}

.logo-section {
  height: 70px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px;
  border-bottom: 1px solid rgba(255,255,255,.1);
}

.logo {
  width: 35px;
}

.menu {
  list-style: none;
  padding: 15px 0;
}

.menu li {
  margin-bottom: 4px;
}

.menu-link {
  display: flex;
  align-items: center;
  gap: 15px;
  color: white;
  text-decoration: none;
  padding: 14px 20px;
}

.menu-link:hover {
  background: rgba(255,255,255,.12);
}

.router-link-active {
  background: rgba(255,255,255,.18);
  border-left: 4px solid #fff;
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.navbar {
  height: 70px;
  background: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.toggle-btn {
  border: none;
  background: none;
  font-size: 20px;
}

.navbar-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.user-name {
  font-weight: 600;
}

.logout-btn {
  border: none;
  background: #dc3545;
  color: white;
  padding: 8px 12px;
  border-radius: 6px;
}

.content {
  padding: 20px;
}

/* Mobile */
@media(max-width:768px){
  .sidebar {
    position: fixed;
    z-index: 999;
    height: 100%;
  }

  .sidebar.collapsed {
    margin-left: -260px;
    width: 260px;
  }

  .content {
    padding: 15px;
  }
}
</style>