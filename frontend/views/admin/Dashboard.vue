<template>
  <AdminLayout>

    <!-- Page Header -->
    <div class="page-header">
      <div class="breadcrumb-row">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item">
              <i class="ti ti-home"></i>
              <span>Home</span>
            </li>
            <li class="breadcrumb-sep"><i class="ti ti-chevron-right"></i></li>
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </nav>
        <div class="header-meta">
          <span class="date-badge">
            <i class="ti ti-calendar"></i>
            {{ currentDate }}
          </span>
        </div>
      </div>
      <div class="header-main">
        <div>
          <h1 class="page-title">Admin Dashboard</h1>
          <p class="page-desc">Overview of all client assistance requests and system activity</p>
        </div>
        <button class="refresh-btn" @click="refreshData" :disabled="refreshing" aria-label="Refresh data">
          <i class="ti ti-refresh" :class="{ spinning: refreshing }"></i>
          <span>Refresh</span>
        </button>
      </div>
    </div>

    <!-- Status Bar -->
    <div class="status-bar" v-if="lastSynced">
      <span class="status-dot"></span>
      <span class="status-text">System operational</span>
      <span class="status-divider">·</span>
      <span class="status-sync">Last synced {{ lastSynced }}</span>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <div
        class="stat-card"
        v-for="stat in statCards"
        :key="stat.key"
        :class="`stat-card--${stat.color}`"
      >
        <div class="stat-top">
          <div class="stat-icon-wrap" :class="`icon-wrap--${stat.color}`">
            <i :class="`ti ${stat.icon}`"></i>
          </div>
          <div class="stat-trend" :class="stat.trendClass">
            <i :class="`ti ${stat.trendIcon}`"></i>
            <span>{{ stat.trendText }}</span>
          </div>
        </div>
        <div class="stat-value">{{ stats[stat.key]?.toLocaleString() ?? '—' }}</div>
        <div class="stat-label">{{ stat.label }}</div>
        <div class="stat-bar">
          <div class="stat-bar-fill" :class="`fill--${stat.color}`" :style="{ width: stat.pct }"></div>
        </div>
      </div>
    </div>

    <!-- Content Row -->
    <div class="content-row">

      <!-- Recent Requests Table -->
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <i class="ti ti-file-text"></i>
            <span>Recent Requests</span>
          </div>
          <RouterLink to="/requests" class="view-all-btn">
            View all <i class="ti ti-arrow-right"></i>
          </RouterLink>
        </div>

        <div class="table-wrap">
          <table class="req-table">
            <thead>
              <tr>
                <th>Request ID</th>
                <th>Client Name</th>
                <th class="hide-sm">Type</th>
                <th>Status</th>
                <th class="hide-xs">Date Filed</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="req in recentRequests" :key="req.id" class="req-row">
                <td class="req-id">{{ req.reference_no }}</td>
                <td class="req-name">{{ req.client_name }}</td>
                <td class="hide-sm req-type">{{ req.assistance_type }}</td>
                <td>
                  <span class="status-badge" :class="`status-badge--${req.status.toLowerCase()}`">
                    {{ req.status }}
                  </span>
                </td>
                <td class="hide-xs req-date">{{ formatDate(req.created_at) }}</td>
              </tr>
              <tr v-if="!recentRequests.length">
                <td colspan="5" class="empty-state">
                  <i class="ti ti-inbox"></i>
                  <span>No requests yet</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Side Panel -->
      <div class="side-col">

        <!-- Quick Actions -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <i class="ti ti-bolt"></i>
              <span>Quick Actions</span>
            </div>
          </div>
          <div class="quick-actions">
            <RouterLink to="/requests/new" class="qa-item qa-item--blue">
              <div class="qa-icon"><i class="ti ti-plus"></i></div>
              <span>New Request</span>
              <i class="ti ti-chevron-right qa-arrow"></i>
            </RouterLink>
            <RouterLink to="/clients/new" class="qa-item qa-item--teal">
              <div class="qa-icon"><i class="ti ti-user-plus"></i></div>
              <span>Add Client</span>
              <i class="ti ti-chevron-right qa-arrow"></i>
            </RouterLink>
            <RouterLink to="/assign" class="qa-item qa-item--purple">
              <div class="qa-icon"><i class="ti ti-user-check"></i></div>
              <span>Assign Staff</span>
              <i class="ti ti-chevron-right qa-arrow"></i>
            </RouterLink>
            <button class="qa-item qa-item--amber" @click="exportReport">
              <div class="qa-icon"><i class="ti ti-download"></i></div>
              <span>Export Report</span>
              <i class="ti ti-chevron-right qa-arrow"></i>
            </button>
          </div>
        </div>

        <!-- Activity Feed -->
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title">
              <i class="ti ti-activity"></i>
              <span>Recent Activity</span>
            </div>
          </div>
          <div class="activity-feed">
            <div
              class="activity-item"
              v-for="log in activityLogs"
              :key="log.id"
            >
              <div class="activity-dot" :class="`dot--${log.color}`"></div>
              <div class="activity-body">
                <div class="activity-msg">{{ log.message }}</div>
                <div class="activity-time">
                  <i class="ti ti-clock"></i>
                  {{ log.time_ago }}
                </div>
              </div>
            </div>
            <div v-if="!activityLogs.length" class="activity-empty">
              <i class="ti ti-mood-empty"></i>
              <span>No recent activity</span>
            </div>
          </div>
        </div>

      </div>
    </div>

  </AdminLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api.js'
import AdminLayout from '@/layouts/AdminLayout.vue'

const stats = ref({
  total_requests: 0,
  pending: 0,
  approved: 0,
  rejected: 0,
  processing: 0,
})
const recentRequests = ref([])
const activityLogs = ref([])
const lastSynced = ref(null)
const refreshing = ref(false)

const currentDate = computed(() =>
  new Date().toLocaleDateString('en-PH', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
)

const statCards = [
  { key: 'total_requests', label: 'Total Requests',  color: 'blue',   icon: 'ti-file-description', trendIcon: 'ti-trending-up',   trendText: '+12% this month', trendClass: 'trend-up',   pct: '72%' },
  { key: 'pending',        label: 'Pending Review',  color: 'amber',  icon: 'ti-clock-hour-4',     trendIcon: 'ti-trending-down', trendText: 'Needs attention', trendClass: 'trend-down', pct: '34%' },
  { key: 'approved',       label: 'Approved',        color: 'green',  icon: 'ti-circle-check',     trendIcon: 'ti-trending-up',   trendText: '+8% this week',   trendClass: 'trend-up',   pct: '85%' },
  { key: 'rejected',       label: 'Rejected',        color: 'red',    icon: 'ti-circle-x',         trendIcon: 'ti-minus',         trendText: 'No change',       trendClass: '',           pct: '12%' },
  { key: 'processing',     label: 'In Processing',   color: 'purple', icon: 'ti-loader',            trendIcon: 'ti-trending-up',   trendText: 'Active cases',    trendClass: 'trend-up',   pct: '48%' },
]

const formatDate = (dateStr) =>
  new Date(dateStr).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })

const loadData = async () => {
  const { data } = await api.get('/dashboard')

  stats.value = {
    total_requests: data?.stats?.total_requests || 0,
    pending: data?.stats?.pending || 0,
    approved: data?.stats?.approved || 0,
    rejected: data?.stats?.rejected || 0,
    processing: data?.stats?.processing || 0,
  }

  recentRequests.value = data?.recent_requests || []
  activityLogs.value = data?.activity_logs || []

  lastSynced.value = new Date().toLocaleString('en-PH')
}

const refreshData = async () => {
  refreshing.value = true
  try {
    await loadData()
  } catch (e) {
    console.error('Refresh failed', e)
  } finally {
    refreshing.value = false
  }
}

const exportReport = async () => {
  try {
    const res = await api.get('/reports/export', { responseType: 'blob' })
    const url = window.URL.createObjectURL(res.data)
    const a = document.createElement('a')
    a.href = url
    a.download = `DSWD-Report-${Date.now()}.xlsx`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    console.error('Export failed', e)
  }
}

onMounted(async () => {
  try {
    await loadData()
  } catch (error) {
    console.error('Dashboard load error:', error)
  }
})
</script>

<style scoped>
/* ── Reset & base ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── Page Header ── */
.page-header {
  margin-bottom: 1.25rem;
}
.breadcrumb-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 4px;
  list-style: none;
  font-size: 12px;
  color: #64748b;
}
.breadcrumb-item {
  display: flex;
  align-items: center;
  gap: 4px;
}
.breadcrumb-item i { font-size: 13px; }
.breadcrumb-item.active { color: #0d47a1; font-weight: 600; }
.breadcrumb-sep { color: #cbd5e1; font-size: 11px; }

.date-badge {
  display: flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 10px;
  border-radius: 20px;
  font-weight: 500;
}
.date-badge i { font-size: 12px; color: #0d47a1; }

.header-main {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: #0a1628;
  letter-spacing: -0.4px;
  line-height: 1.2;
}
.page-desc {
  font-size: 13px;
  color: #64748b;
  margin-top: 3px;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border: 1.5px solid #e2e8f0;
  border-radius: 8px;
  background: #fff;
  color: #475569;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
  font-family: inherit;
  white-space: nowrap;
}
.refresh-btn:hover { border-color: #0d47a1; color: #0d47a1; background: #f0f5ff; }
.refresh-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.refresh-btn i { font-size: 15px; }
.spinning { animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── Status Bar ── */
.status-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 8px;
  margin-bottom: 1.25rem;
  font-size: 12px;
  color: #166534;
  font-weight: 500;
}
.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22c55e;
  flex-shrink: 0;
  box-shadow: 0 0 0 2px rgba(34,197,94,0.25);
  animation: pulse 2s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { box-shadow: 0 0 0 2px rgba(34,197,94,0.25); }
  50% { box-shadow: 0 0 0 5px rgba(34,197,94,0.1); }
}
.status-divider { color: #86efac; }
.status-sync { color: #4ade80; font-weight: 400; }

/* ── Stats Grid ── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 12px;
  margin-bottom: 1.25rem;
}

.stat-card {
  background: #fff;
  border: 1px solid #e9edf2;
  border-radius: 12px;
  padding: 1.1rem 1.1rem 0.9rem;
  position: relative;
  overflow: hidden;
  transition: border-color 0.15s, transform 0.15s;
  cursor: default;
}
.stat-card:hover { border-color: #c7d4e8; transform: translateY(-1px); }
.stat-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  border-radius: 12px 12px 0 0;
}
.stat-card--blue::before  { background: #1565c0; }
.stat-card--amber::before { background: #d97706; }
.stat-card--green::before { background: #15803d; }
.stat-card--red::before   { background: #dc2626; }
.stat-card--purple::before{ background: #7c3aed; }

.stat-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}
.stat-icon-wrap {
  width: 38px;
  height: 38px;
  border-radius: 9px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.icon-wrap--blue   { background: #dbeafe; color: #1565c0; }
.icon-wrap--amber  { background: #fef3c7; color: #d97706; }
.icon-wrap--green  { background: #dcfce7; color: #15803d; }
.icon-wrap--red    { background: #fee2e2; color: #dc2626; }
.icon-wrap--purple { background: #ede9fe; color: #7c3aed; }

.stat-trend {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 10px;
  font-weight: 600;
  padding: 3px 7px;
  border-radius: 20px;
}
.stat-trend i { font-size: 11px; }
.trend-up   { background: #f0fdf4; color: #15803d; }
.trend-down { background: #fff7ed; color: #c2410c; }
.stat-trend:not(.trend-up):not(.trend-down) { background: #f1f5f9; color: #64748b; }

.stat-value {
  font-size: 26px;
  font-weight: 700;
  color: #0a1628;
  line-height: 1;
  font-variant-numeric: tabular-nums;
  letter-spacing: -0.5px;
  margin-bottom: 4px;
}
.stat-label {
  font-size: 12px;
  color: #64748b;
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.stat-bar {
  height: 3px;
  background: #f1f5f9;
  border-radius: 99px;
  overflow: hidden;
}
.stat-bar-fill {
  height: 100%;
  border-radius: 99px;
  transition: width 0.8s ease;
}
.fill--blue   { background: #1565c0; }
.fill--amber  { background: #d97706; }
.fill--green  { background: #15803d; }
.fill--red    { background: #dc2626; }
.fill--purple { background: #7c3aed; }

/* ── Content Row ── */
.content-row {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 12px;
  align-items: start;
}

/* ── Panel ── */
.panel {
  background: #fff;
  border: 1px solid #e9edf2;
  border-radius: 12px;
  overflow: hidden;
}
.panel + .panel { margin-top: 12px; }

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.9rem 1.1rem;
  border-bottom: 1px solid #f1f5f9;
}
.panel-title {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
}
.panel-title i { font-size: 16px; color: #1565c0; }

.view-all-btn {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #1565c0;
  font-weight: 500;
  text-decoration: none;
  padding: 4px 10px;
  border-radius: 6px;
  transition: background 0.15s;
}
.view-all-btn:hover { background: #eff6ff; }
.view-all-btn i { font-size: 12px; }

/* ── Table ── */
.table-wrap { overflow-x: auto; }
.req-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.req-table th {
  padding: 9px 1rem;
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #f8fafc;
  border-bottom: 1px solid #f1f5f9;
  white-space: nowrap;
}
.req-table td {
  padding: 11px 1rem;
  border-bottom: 1px solid #f8fafc;
  color: #334155;
  vertical-align: middle;
}
.req-row:last-child td { border-bottom: none; }
.req-row:hover td { background: #fafbff; }

.req-id    { font-weight: 600; color: #1565c0; white-space: nowrap; font-size: 12px; }
.req-name  { font-weight: 500; color: #1e293b; }
.req-type  { color: #64748b; }
.req-date  { color: #94a3b8; white-space: nowrap; font-size: 12px; }

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 3px 9px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
}
.status-badge--pending    { background: #fef9c3; color: #854d0e; }
.status-badge--approved   { background: #dcfce7; color: #14532d; }
.status-badge--rejected   { background: #fee2e2; color: #7f1d1d; }
.status-badge--processing { background: #dbeafe; color: #1e3a8a; }

.empty-state {
  text-align: center;
  padding: 2.5rem 1rem;
  color: #94a3b8;
  font-size: 13px;
}
.empty-state i { font-size: 24px; display: block; margin-bottom: 6px; opacity: 0.5; }

/* ── Quick Actions ── */
.quick-actions { padding: 0.5rem; display: flex; flex-direction: column; gap: 4px; }

.qa-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #334155;
  text-decoration: none;
  border: none;
  background: transparent;
  cursor: pointer;
  font-family: inherit;
  width: 100%;
  text-align: left;
  transition: background 0.12s;
}
.qa-item:hover { background: #f8fafc; }
.qa-item span { flex: 1; }

.qa-icon {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  flex-shrink: 0;
}
.qa-item--blue   .qa-icon { background: #dbeafe; color: #1565c0; }
.qa-item--teal   .qa-icon { background: #ccfbf1; color: #0f766e; }
.qa-item--purple .qa-icon { background: #ede9fe; color: #7c3aed; }
.qa-item--amber  .qa-icon { background: #fef3c7; color: #d97706; }

.qa-arrow { font-size: 13px; color: #cbd5e1; }

/* ── Activity Feed ── */
.activity-feed { padding: 0.5rem 0; }

.activity-item {
  display: flex;
  gap: 10px;
  padding: 10px 1.1rem;
  align-items: flex-start;
  transition: background 0.12s;
}
.activity-item:hover { background: #fafbff; }

.activity-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 5px;
}
.dot--blue   { background: #3b82f6; }
.dot--green  { background: #22c55e; }
.dot--amber  { background: #f59e0b; }
.dot--red    { background: #ef4444; }
.dot--purple { background: #8b5cf6; }

.activity-body { flex: 1; min-width: 0; }
.activity-msg {
  font-size: 12px;
  color: #334155;
  line-height: 1.5;
  font-weight: 500;
}
.activity-time {
  display: flex;
  align-items: center;
  gap: 3px;
  font-size: 11px;
  color: #94a3b8;
  margin-top: 3px;
}
.activity-time i { font-size: 11px; }

.activity-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  padding: 1.5rem;
  color: #94a3b8;
  font-size: 12px;
}
.activity-empty i { font-size: 22px; opacity: 0.5; }

/* ── Responsive ── */
@media (max-width: 1024px) {
  .content-row { grid-template-columns: 1fr 260px; }
}

@media (max-width: 768px) {
  .content-row { grid-template-columns: 1fr; }
  .stats-grid  { grid-template-columns: repeat(2, 1fr); }
  .header-main { flex-direction: column; align-items: flex-start; }
  .refresh-btn { width: 100%; justify-content: center; }
  .hide-sm { display: none; }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .stat-value { font-size: 22px; }
  .breadcrumb-row { flex-direction: column; align-items: flex-start; gap: 6px; }
  .hide-xs { display: none; }
  .page-title { font-size: 18px; }
}

@media (max-width: 360px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>