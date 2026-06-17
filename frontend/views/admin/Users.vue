<template>
  <AdminLayout>
    <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><i class="tabler-icon tabler-icon-home me-1"></i>Home</li>
            <li class="breadcrumb-item active">Users</li>
          </ol>
        </nav>
        <h1 class="page-title">User Management</h1>
        <p class="page-desc text-muted">Manage system users, departments, and access levels</p>
      </div>
      
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary bg-white shadow-sm" @click="openLevelModal">
          <i class="tabler-icon tabler-icon-shield-lock me-2"></i> User Levels
        </button>
        <button class="btn btn-primary shadow-sm" @click="openUserModal">
          <i class="tabler-icon tabler-icon-user-plus me-2"></i> Add User
        </button>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Access Level</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loadingUsers">
                <td colspan="5" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm me-2"></div>Loading users...
                </td>
              </tr>
              <tr v-else-if="users.length === 0">
                <td colspan="5" class="text-center py-5 text-muted">
                  <i class="tabler-icon tabler-icon-users mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                  <p class="mb-0">No users found.</p>
                </td>
              </tr>
              <tr v-for="user in users" :key="user.id" v-else>
                <td class="ps-4 fw-semibold text-dark">{{ user.name }}</td>
                <td class="text-muted">{{ user.email }}</td>
                <td>
                  <span class="badge bg-light text-dark border">
                    {{ user.department?.department_name || 'No Department' }}
                  </span>
                </td>
                <td>
                  <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">
                    {{ user.user_level?.name || 'Unknown' }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" @click="editUser(user)" title="Edit">
                      <i class="tabler-icon tabler-icon-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteUser(user.id)" title="Delete">
                      <i class="tabler-icon tabler-icon-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showUserModal" class="modal-backdrop fade show"></div>
    <div v-if="showUserModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">{{ isEditingUser ? 'Edit User' : 'Add New User' }}</h5>
            <button type="button" class="btn-close" @click="showUserModal = false"></button>
          </div>
          <form @submit.prevent="saveUser">
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" v-model="userForm.name" required>
              </div>
              <div class="mb-3">
                <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" v-model="userForm.email" required>
              </div>
              
              <div class="mb-3" v-if="!isEditingUser">
                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" v-model="userForm.password" required minlength="6">
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Department</label>
                  <select class="form-select" v-model="userForm.department_id">
                    <option :value="null">None</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                      {{ dept.department_name }}
                    </option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">User Level <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="userForm.user_level_id" required>
                    <option value="" disabled>Select Level...</option>
                    <option v-for="lvl in userLevels" :key="lvl.id" :value="lvl.id">
                      {{ lvl.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="modal-footer border-0 bg-light">
              <button type="button" class="btn btn-secondary bg-white text-dark border" @click="showUserModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ isEditingUser ? 'Save Changes' : 'Create User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-if="showLevelModal" class="modal-backdrop fade show" style="z-index: 1055;"></div>
    <div v-if="showLevelModal" class="modal fade show d-block" tabindex="-1" style="z-index: 1060;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">Manage User Levels</h5>
            <button type="button" class="btn-close" @click="showLevelModal = false"></button>
          </div>
          <div class="modal-body">
            
            <form @submit.prevent="saveLevel" class="d-flex gap-2 mb-4 p-3 bg-light rounded border">
              <input type="text" class="form-control" v-model="levelForm.name" placeholder="Enter level name (e.g. Admin)" required>
              <button type="submit" class="btn btn-primary text-nowrap" :disabled="savingLevel">
                {{ editingLevelId ? 'Update' : 'Add Level' }}
              </button>
              <button type="button" class="btn btn-outline-secondary" v-if="editingLevelId" @click="cancelEditLevel">
                Cancel
              </button>
            </form>

            <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
              <table class="table table-sm table-hover align-middle">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>Level Name</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="userLevels.length === 0">
                    <td colspan="2" class="text-center text-muted py-3">No levels defined.</td>
                  </tr>
                  <tr v-for="lvl in userLevels" :key="lvl.id" v-else>
                    <td class="fw-semibold">{{ lvl.name }}</td>
                    <td class="text-end">
                      <button class="btn btn-sm text-primary border-0" @click="editLevel(lvl)"><i class="tabler-icon tabler-icon-edit"></i></button>
                      <button class="btn btn-sm text-danger border-0" @click="deleteLevel(lvl.id)"><i class="tabler-icon tabler-icon-trash"></i></button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue'
import api from '@/services/api.js'
import Swal from 'sweetalert2'

// --- Global State ---
const users = ref([])
const departments = ref([])
const userLevels = ref([])
const loadingUsers = ref(true)

// --- Modals State ---
const showUserModal = ref(false)
const showLevelModal = ref(false)

// ==========================================
// USER LOGIC
// ==========================================
const isEditingUser = ref(false)
const currentUserId = ref(null)
const saving = ref(false)

const userForm = reactive({
  name: '',
  email: '',
  password: '',
  department_id: null,
  user_level_id: ''
})

const fetchUsers = async () => {
  loadingUsers.value = true
  try {
    const { data } = await api.get('/users')
    users.value = data
  } catch (error) {
    console.error(error)
  } finally {
    loadingUsers.value = false
  }
}

const openUserModal = () => {
  isEditingUser.value = false
  currentUserId.value = null
  Object.assign(userForm, { name: '', email: '', password: '', department_id: null, user_level_id: '' })
  showUserModal.value = true
}

const editUser = (user) => {
  isEditingUser.value = true
  currentUserId.value = user.id
  Object.assign(userForm, {
    name: user.name,
    email: user.email,
    password: '', // Kept empty, update controller doesn't use it
    department_id: user.department_id,
    user_level_id: user.user_level_id
  })
  showUserModal.value = true
}

const saveUser = async () => {
  saving.value = true
  try {
    if (isEditingUser.value) {
      await api.put(`/users/${currentUserId.value}`, userForm)
      Swal.fire({ icon: 'success', title: 'Updated', text: 'User updated successfully.', timer: 1500, showConfirmButton: false })
    } else {
      await api.post('/users', userForm)
      Swal.fire({ icon: 'success', title: 'Created', text: 'User added successfully.', timer: 1500, showConfirmButton: false })
    }
    showUserModal.value = false
    fetchUsers()
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Failed to save user.', 'error')
  } finally {
    saving.value = false
  }
}

const deleteUser = (id) => {
  Swal.fire({
    title: 'Delete User?',
    text: "This action cannot be undone.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    confirmButtonText: 'Yes, delete!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/users/${id}`)
        Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false })
        fetchUsers()
      } catch (error) {
        Swal.fire('Error', 'Failed to delete user.', 'error')
      }
    }
  })
}

// ==========================================
// USER LEVELS LOGIC
// ==========================================
const editingLevelId = ref(null)
const savingLevel = ref(false)
const levelForm = reactive({ name: '' })

const fetchDependencies = async () => {
  try {
    const [deptRes, levelRes] = await Promise.all([
      api.get('/departments'),
      api.get('/user-levels') // <-- Make sure you have a UserLevelController with index/store/put/delete handling this!
    ])
    departments.value = deptRes.data
    userLevels.value = levelRes.data
  } catch (error) {
    console.error('Failed to load departments/levels', error)
  }
}

const openLevelModal = () => {
  cancelEditLevel()
  showLevelModal.value = true
}

const editLevel = (lvl) => {
  editingLevelId.value = lvl.id
  levelForm.name = lvl.name
}

const cancelEditLevel = () => {
  editingLevelId.value = null
  levelForm.name = ''
}

const saveLevel = async () => {
  savingLevel.value = true
  try {
    if (editingLevelId.value) {
      await api.put(`/user-levels/${editingLevelId.value}`, levelForm)
    } else {
      await api.post('/user-levels', levelForm)
    }
    cancelEditLevel()
    await fetchDependencies() // Refresh list
  } catch (error) {
    Swal.fire('Error', 'Failed to save user level.', 'error')
  } finally {
    savingLevel.value = false
  }
}

const deleteLevel = async (id) => {
  if (!confirm('Are you sure you want to delete this level?')) return
  try {
    await api.delete(`/user-levels/${id}`)
    await fetchDependencies()
  } catch (error) {
    Swal.fire('Error', 'Cannot delete. Level might be in use.', 'error')
  }
}

// ==========================================
// INITIALIZATION
// ==========================================
onMounted(() => {
  fetchUsers()
  fetchDependencies()
})
</script>

<style scoped>
.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}
.table th {
  font-weight: 600;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}
.badge {
  font-weight: 500;
  padding: 0.4em 0.6em;
}
</style>