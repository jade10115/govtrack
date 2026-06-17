<template>
  <AdminLayout>
    <div class="page-header mb-4 d-flex justify-content-between align-items-center">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <i class="ti ti-home me-1"></i>Home
            </li>
            <li class="breadcrumb-item active">Departments</li>
          </ol>
        </nav>
        <h1 class="page-title">Departments Management</h1>
        <p class="page-desc text-muted">Manage system departments and their descriptions</p>
      </div>
      
      <button class="btn btn-primary shadow-sm" @click="openAddModal">
        <i class="ti ti-plus me-2"></i> Add Department
      </button>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">ID</th>
                <th>Department Name</th>
                <th>Description</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="4" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                  Loading departments...
                </td>
              </tr>

              <tr v-else-if="departments.length === 0">
                <td colspan="4" class="text-center py-5 text-muted">
                  <i class="ti ti-building mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                  <p class="mb-0">No departments found.</p>
                </td>
              </tr>

              <tr v-for="dept in departments" :key="dept.id" v-else>
                <td class="ps-4 text-muted">#{{ dept.id }}</td>
                <td class="fw-semibold text-dark">{{ dept.department_name }}</td>
                <td class="text-muted text-truncate" style="max-width: 300px;">
                  {{ dept.description || 'No description provided' }}
                </td>
                <td class="text-end pe-4">
                  <div class="btn-group">
                    <button 
                      class="btn btn-sm btn-outline-primary" 
                      @click="openEditModal(dept)"
                      title="Edit"
                    >
                      <i class="ti ti-edit"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-outline-danger" 
                      @click="confirmDelete(dept.id)"
                      title="Delete"
                    >
                      <i class="ti ti-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="modal-backdrop fade show"></div>
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1" role="dialog">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">
              {{ isEditing ? 'Edit Department' : 'Add New Department' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body">
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.department_name" 
                  placeholder="e.g. Human Resources" 
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea 
                  class="form-control" 
                  v-model="form.description" 
                  rows="3" 
                  placeholder="Brief description of the department's role..."
                ></textarea>
              </div>

            </div>
            
            <div class="modal-footer border-0 bg-light">
              <button type="button" class="btn btn-secondary text-dark bg-white border" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ isEditing ? 'Save Changes' : 'Create Department' }}
              </button>
            </div>
          </form>

        </div>
      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import AdminLayout from '@/layouts/AdminLayout.vue' // Adjust path if needed
import api from '@/services/api.js'                 // Adjust path if needed
import Swal from 'sweetalert2'

// State
const departments = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)

// Form Data
const form = reactive({
  department_name: '',
  description: ''
})

// Fetch Data
const fetchDepartments = async () => {
  loading.value = true
  try {
    const response = await api.get('/departments')
    departments.value = response.data
  } catch (error) {
    Swal.fire('Error!', 'Failed to load departments.', 'error')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Modal Handlers
const openAddModal = () => {
  isEditing.value = false
  currentId.value = null
  form.department_name = ''
  form.description = ''
  showModal.value = true
}

const openEditModal = (dept) => {
  isEditing.value = true
  currentId.value = dept.id
  form.department_name = dept.department_name
  form.description = dept.description || ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

// Submit Form (Create or Update)
const submitForm = async () => {
  saving.value = true
  try {
    if (isEditing.value) {
      // Update existing
      await api.put(`/departments/${currentId.value}`, form)
      Swal.fire({ icon: 'success', title: 'Updated!', text: 'Department updated successfully.', timer: 1500, showConfirmButton: false })
    } else {
      // Create new
      await api.post('/departments', form)
      Swal.fire({ icon: 'success', title: 'Created!', text: 'Department added successfully.', timer: 1500, showConfirmButton: false })
    }
    closeModal()
    fetchDepartments() // Refresh table
  } catch (error) {
    Swal.fire('Error!', error.response?.data?.message || 'Something went wrong.', 'error')
    console.error(error)
  } finally {
    saving.value = false
  }
}

// Delete Handler
const confirmDelete = (id) => {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc3545',
    cancelButtonColor: '#6c757d',
    confirmButtonText: 'Yes, delete it!'
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await api.delete(`/departments/${id}`)
        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Department has been deleted.', timer: 1500, showConfirmButton: false })
        fetchDepartments() // Refresh table
      } catch (error) {
        Swal.fire('Error!', 'Cannot delete department. It might be linked to existing users.', 'error')
        console.error(error)
      }
    }
  })
}

// Lifecycle
onMounted(() => {
  fetchDepartments()
})
</script>

<style scoped>
/* Modal overlay styling so we don't need Bootstrap's JS library */
.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

/* Subtle table styling improvements */
.table th {
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
}

.table td {
  vertical-align: middle;
}

/* Button transitions */
.btn {
  transition: all 0.2s ease;
}
.btn-group .btn {
  padding: 0.25rem 0.5rem;
}
</style>