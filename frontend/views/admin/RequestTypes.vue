<template>
  <AdminLayout>
    <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <i class="tabler-icon tabler-icon-home me-1"></i>Home
            </li>
            <li class="breadcrumb-item active">Request Types</li>
          </ol>
        </nav>
        <h1 class="page-title">Request Types</h1>
        <p class="page-desc text-muted">Manage the categories of assistance requests your system handles</p>
      </div>
      
      <button class="btn btn-primary shadow-sm" @click="openAddModal">
        <i class="tabler-icon tabler-icon-plus me-2"></i> Add Request Type
      </button>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">ID</th>
                <th>Request Name</th>
                <th>Description</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="4" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                  Loading request types...
                </td>
              </tr>

              <tr v-else-if="requestTypes.length === 0">
                <td colspan="4" class="text-center py-5 text-muted">
                  <i class="tabler-icon tabler-icon-list mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                  <p class="mb-0">No request types found.</p>
                </td>
              </tr>

              <tr v-for="type in requestTypes" :key="type.id" v-else>
                <td class="ps-4 text-muted">#{{ type.id }}</td>
                <td class="fw-semibold text-dark">
                  <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">
                    {{ type.request_name }}
                  </span>
                </td>
                <td class="text-muted text-truncate" style="max-width: 350px;">
                  {{ type.description || 'No description provided' }}
                </td>
                <td class="text-end pe-4">
                  <div class="btn-group">
                    <button 
                      class="btn btn-sm btn-outline-primary border-0" 
                      @click="openEditModal(type)"
                      title="Edit"
                    >
                      <i class="tabler-icon tabler-icon-edit"></i>
                    </button>
                    <button 
                      class="btn btn-sm btn-outline-danger border-0" 
                      @click="confirmDelete(type.id)"
                      title="Delete"
                    >
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

    <div v-if="showModal" class="modal-backdrop fade show"></div>
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">
              <i class="tabler-icon me-2" :class="isEditing ? 'tabler-icon-edit' : 'tabler-icon-plus'"></i>
              {{ isEditing ? 'Edit Request Type' : 'New Request Type' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>

          <form @submit.prevent="submitForm">
            <div class="modal-body">
              
              <div class="mb-3">
                <label class="form-label fw-semibold">Request Name <span class="text-danger">*</span></label>
                <input 
                  type="text" 
                  class="form-control" 
                  v-model="form.request_name" 
                  placeholder="e.g. Medical Assistance, Financial Aid" 
                  required
                >
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea 
                  class="form-control" 
                  v-model="form.description" 
                  rows="3" 
                  placeholder="Provide details about what this request entails..."
                ></textarea>
              </div>

            </div>
            
            <div class="modal-footer border-0 bg-light">
              <button type="button" class="btn btn-secondary text-dark bg-white border" @click="closeModal">Cancel</button>
              <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ isEditing ? 'Save Changes' : 'Create Request Type' }}
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
import AdminLayout from '@/layouts/AdminLayout.vue' // Update path if your alias differs
import api from '@/services/api.js'               // Update path if your alias differs
import Swal from 'sweetalert2'

// State
const requestTypes = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const isEditing = ref(false)
const currentId = ref(null)

// Form Data matches Laravel Request Type fillables
const form = reactive({
  request_name: '',
  description: ''
})

// Fetch Data
const fetchRequestTypes = async () => {
  loading.value = true
  try {
    const response = await api.get('/request-types')
    requestTypes.value = response.data
  } catch (error) {
    Swal.fire('Error!', 'Failed to load request types.', 'error')
    console.error(error)
  } finally {
    loading.value = false
  }
}

// Modal Handlers
const openAddModal = () => {
  isEditing.value = false
  currentId.value = null
  form.request_name = ''
  form.description = ''
  showModal.value = true
}

const openEditModal = (type) => {
  isEditing.value = true
  currentId.value = type.id
  form.request_name = type.request_name
  form.description = type.description || ''
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
      await api.put(`/request-types/${currentId.value}`, form)
      Swal.fire({ icon: 'success', title: 'Updated!', text: 'Request type updated successfully.', timer: 1500, showConfirmButton: false })
    } else {
      await api.post('/request-types', form)
      Swal.fire({ icon: 'success', title: 'Created!', text: 'Request type added successfully.', timer: 1500, showConfirmButton: false })
    }
    closeModal()
    fetchRequestTypes() // Refresh table
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
        await api.delete(`/request-types/${id}`)
        Swal.fire({ icon: 'success', title: 'Deleted!', text: 'Request type has been deleted.', timer: 1500, showConfirmButton: false })
        fetchRequestTypes() // Refresh table
      } catch (error) {
        Swal.fire('Error!', 'Cannot delete this request type.', 'error')
        console.error(error)
      }
    }
  })
}

// Lifecycle
onMounted(() => {
  fetchRequestTypes()
})
</script>

<style scoped>
/* Modal overlay styling to avoid requiring Bootstrap JS */
.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}
.modal {
  z-index: 1050;
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
</style>