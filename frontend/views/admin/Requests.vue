<template>
  <AdminLayout>
    <div class="page-header mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><i class="tabler-icon tabler-icon-home me-1"></i>Home</li>
            <li class="breadcrumb-item active">Client Requests</li>
          </ol>
        </nav>
        <h1 class="page-title">Client Requests</h1>
        <p class="page-desc text-muted">Manage, assign, and track all incoming client assistance requests</p>
      </div>
      
      <button class="btn btn-primary shadow-sm" @click="openAddModal">
        <i class="tabler-icon tabler-icon-file-plus me-2"></i> New Request
      </button>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="ps-4">Control No.</th>
                <th>Client Details</th>
                <th>Type / Department</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th class="text-end pe-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="6" class="text-center py-4 text-muted">
                  <div class="spinner-border spinner-border-sm me-2"></div>Loading requests...
                </td>
              </tr>
              <tr v-else-if="requests.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  <i class="tabler-icon tabler-icon-folder-open mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                  <p class="mb-0">No requests have been filed yet.</p>
                </td>
              </tr>
              <tr v-for="req in requests" :key="req.id" v-else>
                <td class="ps-4 fw-bold text-primary">{{ req.control_no }}</td>
                <td>
                  <div class="fw-semibold text-dark">{{ req.client_name }}</div>
                  <div class="text-muted small">{{ req.contact_number }}</div>
                </td>
                <td>
                  <div class="fw-medium">{{ req.request_type?.request_name || 'N/A' }}</div>
                  <div class="text-muted small">{{ req.department?.department_name || 'N/A' }}</div>
                </td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                      <i class="tabler-icon tabler-icon-user" style="font-size: 14px;"></i>
                    </div>
                    <span class="fw-medium">{{ req.assigned_user?.name || 'Unassigned' }}</span>
                  </div>
                </td>
                <td>
                  <span class="badge border" :class="getStatusClass(req.status)">
                    {{ req.status }}
                  </span>
                </td>
                <td class="text-end pe-4">
                  <button class="btn btn-sm btn-outline-secondary" @click="viewRequest(req)" title="View Details">
                    <i class="tabler-icon tabler-icon-eye"></i> View
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="modal-backdrop fade show"></div>
    <div v-if="showModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">File New Client Request</h5>
            <button type="button" class="btn-close" @click="showModal = false"></button>
          </div>
          <form @submit.prevent="submitRequest">
            <div class="modal-body p-4">
              
              <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Client Information</h6>
              <div class="row mb-3">
                <div class="col-md-12 mb-3">
                  <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.client_name" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.contact_number" required>
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold">Age <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" v-model="form.age" required min="1">
                </div>
                <div class="col-md-3 mb-3">
                  <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.gender" required>
                    <option value="" disabled>Select...</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
                <div class="col-md-12">
                  <label class="form-label fw-semibold">Complete Address <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" v-model="form.address" required>
                </div>
              </div>

              <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">Routing & Assignment</h6>
              <div class="row mb-3">
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">Request Type <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.request_type_id" required>
                    <option value="" disabled>Select Type...</option>
                    <option v-for="type in requestTypes" :key="type.id" :value="type.id">{{ type.request_name }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.department_id" required>
                    <option value="" disabled>Select Dept...</option>
                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.department_name }}</option>
                  </select>
                </div>
                <div class="col-md-4 mb-3">
                  <label class="form-label fw-semibold">Assign To User <span class="text-danger">*</span></label>
                  <select class="form-select" v-model="form.assigned_to" required>
                    <option value="" disabled>Select Assignee...</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                  </select>
                </div>
              </div>

              <h6 class="fw-bold text-primary mb-3 border-bottom pb-2 mt-4">Interview Details</h6>
              <div class="mb-3">
                <label class="form-label fw-semibold">Interview Notes & Assessment <span class="text-danger">*</span></label>
                <textarea class="form-control" v-model="form.interview_notes" rows="4" required placeholder="Detail the client's situation and specific needs here..."></textarea>
              </div>

            </div>
            <div class="modal-footer border-0 bg-light">
              <button type="button" class="btn btn-secondary bg-white text-dark border" @click="showModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary px-4" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                Submit & Assign Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div v-if="showViewModal" class="modal-backdrop fade show"></div>
    <div v-if="showViewModal" class="modal fade show d-block" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-light border-0">
            <h5 class="modal-title fw-bold">Request Details</h5>
            <button type="button" class="btn-close" @click="showViewModal = false"></button>
          </div>
          <div class="modal-body" v-if="selectedReq">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="text-primary mb-0">{{ selectedReq.control_no }}</h4>
              <span class="badge border p-2 px-3" :class="getStatusClass(selectedReq.status)">{{ selectedReq.status }}</span>
            </div>
            
            <table class="table table-sm table-borderless">
              <tbody>
                <tr><td class="text-muted" style="width: 120px;">Client:</td><td class="fw-semibold">{{ selectedReq.client_name }} ({{ selectedReq.age }} / {{ selectedReq.gender }})</td></tr>
                <tr><td class="text-muted">Contact:</td><td>{{ selectedReq.contact_number }}</td></tr>
                <tr><td class="text-muted">Address:</td><td>{{ selectedReq.address }}</td></tr>
                <tr><td colspan="2"><hr class="my-2"></td></tr>
                <tr><td class="text-muted">Type:</td><td>{{ selectedReq.request_type?.request_name }}</td></tr>
                <tr><td class="text-muted">Department:</td><td>{{ selectedReq.department?.department_name }}</td></tr>
                <tr><td class="text-muted">Assigned To:</td><td class="fw-bold text-primary">{{ selectedReq.assigned_user?.name }}</td></tr>
              </tbody>
            </table>
            
            <div class="mt-3 bg-light p-3 rounded border">
              <h6 class="fw-bold text-muted mb-2">Interview Notes</h6>
              <p class="mb-0 small" style="white-space: pre-wrap;">{{ selectedReq.interview_notes }}</p>
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

const requests = ref([])
const requestTypes = ref([])
const departments = ref([])
const users = ref([])

const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const showViewModal = ref(false)
const selectedReq = ref(null)

const form = reactive({
  client_name: '', address: '', contact_number: '', age: '', gender: '',
  request_type_id: '', department_id: '', assigned_to: '', interview_notes: ''
})

const fetchDependencies = async () => {
  try {
    const [types, depts, usrs] = await Promise.all([
      api.get('/request-types'),
      api.get('/departments'),
      api.get('/users')
    ])
    requestTypes.value = types.data
    departments.value = depts.data
    users.value = usrs.data
  } catch (error) {
    console.error('Failed to load form dependencies', error)
  }
}

const fetchRequests = async () => {
  loading.value = true
  try {
    const response = await api.get('/requests')
    requests.value = response.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  Object.assign(form, { client_name: '', address: '', contact_number: '', age: '', gender: '', request_type_id: '', department_id: '', assigned_to: '', interview_notes: '' })
  showModal.value = true
}

const submitRequest = async () => {
  saving.value = true
  try {
    await api.post('/requests', form)
    Swal.fire({ icon: 'success', title: 'Request Sent', text: 'Assigned successfully.', timer: 1500, showConfirmButton: false })
    showModal.value = false
    fetchRequests()
  } catch (error) {
    Swal.fire('Error', error.response?.data?.message || 'Failed to submit request.', 'error')
  } finally {
    saving.value = false
  }
}

const viewRequest = (req) => {
  selectedReq.value = req
  showViewModal.value = true
}

const getStatusClass = (status) => {
  const map = {
    'Pending': 'bg-warning bg-opacity-10 text-warning border-warning-subtle',
    'Accepted': 'bg-info bg-opacity-10 text-info border-info-subtle',
    'Completed': 'bg-success bg-opacity-10 text-success border-success-subtle',
    'Rejected': 'bg-danger bg-opacity-10 text-danger border-danger-subtle',
  }
  return map[status] || 'bg-secondary text-white'
}

onMounted(() => {
  fetchDependencies()
  fetchRequests()
})
</script>

<style scoped>
.modal-backdrop { background-color: rgba(0, 0, 0, 0.5); z-index: 1040; }
.modal { z-index: 1050; }
.table th { font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
.badge { padding: 0.4em 0.7em; font-weight: 600; }
</style>