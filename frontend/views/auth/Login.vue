<template>
  <div class="login-page">

    <div class="left-panel" aria-hidden="true">
      <div class="decorative-circle c1"></div>
      <div class="decorative-circle c2"></div>

      <div class="gov-strip">
        <div class="gov-seal">DSWD</div>
        <div class="gov-name">Department of Social Welfare<br>and Development</div>
      </div>

      <div class="hero-text">
        <h1 class="hero-title">
          Client Assistance<br>Request <span class="accent">System</span>
        </h1>
        <p class="hero-desc">
          A unified digital platform for managing and tracking social welfare
          assistance requests across all DSWD offices.
        </p>
        <ul class="feature-list">
          <li><span class="feature-icon"><i class="ti ti-file-check"></i></span> End-to-end request tracking</li>
          <li><span class="feature-icon"><i class="ti ti-users"></i></span> Multi-role access control</li>
          <li><span class="feature-icon"><i class="ti ti-shield-check"></i></span> Secure government data handling</li>
        </ul>
      </div>

      <footer class="left-footer">
        Republic of the Philippines &nbsp;·&nbsp; Official Government System &nbsp;·&nbsp; v2.0
      </footer>
    </div>

    <div class="right-panel">
      <div class="form-wrap">

        <div class="mobile-header">
          <div class="mobile-seal">DSWD</div>
          <div class="mobile-name">Department of Social Welfare<br>and Development</div>
        </div>

        <p class="form-eyebrow">
          <i class="ti ti-lock"></i> Secure portal
        </p>
        <h2 class="form-heading">Sign in to your account</h2>
        <p class="form-sub">Use your official DSWD credentials to access the system.</p>

        <Transition name="fade">
          <div v-if="alert.show" class="alert" :class="`alert-${alert.type}`" role="alert">
            <i :class="`ti ${alert.type === 'error' ? 'ti-alert-circle' : 'ti-circle-check'}`"></i>
            <span>{{ alert.message }}</span>
          </div>
        </Transition>

        <form @submit.prevent="login" novalidate>

          <div class="field" :class="{ 'has-error': errors.email }">
            <label class="field-label" for="email">Email address</label>
            <div class="field-wrap">
              <i class="field-icon ti ti-mail"></i>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="field-input"
                placeholder="you@dswd.gov.ph"
                autocomplete="email"
                :aria-describedby="errors.email ? 'email-error' : undefined"
              />
            </div>
            <p v-if="errors.email" id="email-error" class="field-error">
              <i class="ti ti-alert-circle"></i> {{ errors.email }}
            </p>
          </div>

          <div class="field" :class="{ 'has-error': errors.password }">
            <label class="field-label" for="password">Password</label>
            <div class="field-wrap">
              <i class="field-icon ti ti-lock"></i>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="field-input field-input--padded"
                placeholder="Enter your password"
                autocomplete="current-password"
                :aria-describedby="errors.password ? 'pw-error' : undefined"
              />
              <button
                type="button"
                class="toggle-pw"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = !showPassword"
              >
                <i :class="`ti ${showPassword ? 'ti-eye-off' : 'ti-eye'}`"></i>
              </button>
            </div>
            <p v-if="errors.password" id="pw-error" class="field-error">
              <i class="ti ti-alert-circle"></i> {{ errors.password }}
            </p>
          </div>

          <div class="forgot-row">
            <RouterLink to="/forgot-password" class="forgot-link">Forgot password?</RouterLink>
          </div>

          <button type="submit" class="submit-btn" :disabled="loading">
            <span>{{ loading ? 'Signing in…' : 'Sign in' }}</span>
            <i v-if="!loading" class="ti ti-arrow-right"></i>
            <span v-if="loading" class="spinner" aria-hidden="true"></span>
          </button>

        </form>

        <p class="helpdesk">
          Having trouble? <a href="mailto:helpdesk@dswd.gov.ph">Contact IT Helpdesk</a>
        </p>
      </div>
    </div>

  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import auth from '@/services/auth.js'

const router = useRouter()

const form = reactive({ email: '', password: '' })
const errors = reactive({ email: '', password: '' })
const alert = reactive({ show: false, type: 'error', message: '' })
const loading = ref(false)
const showPassword = ref(false)

const validate = () => {
  errors.email = ''
  errors.password = ''
  let valid = true

  if (!form.email) {
    errors.email = 'Email address is required'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Enter a valid email address'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Password is required'
    valid = false
  }

  return valid
}

const showAlert = (type, message) => {
  alert.show = true
  alert.type = type
  alert.message = message
}

const login = async () => {
  alert.show = false
  if (!validate()) return

  loading.value = true
  try {
    const { data } = await auth.login(form)

    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    showAlert('success', 'Login successful. Redirecting…')
    setTimeout(() => router.push('/admin/dashboard'), 1000)

  } catch (err) {
    const msg = err.response?.data?.message ?? 'Invalid email or password. Please try again.'
    showAlert('error', msg)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 100vh;
  font-family: 'Inter', system-ui, sans-serif;
}

.left-panel {
  background: #0d47a1;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 2.5rem;
  position: relative;
  overflow: hidden;
}
.decorative-circle { position: absolute; border-radius: 50%; pointer-events: none; }
.c1 { width: 260px; height: 260px; right: -40px; bottom: -40px; border: 40px solid rgba(255,255,255,0.05); }
.c2 { width: 140px; height: 140px; right: 60px; bottom: 60px; border: 20px solid rgba(255,255,255,0.04); }

.gov-strip { display: flex; align-items: center; gap: 12px; }
.gov-seal {
  width: 48px; height: 48px; border-radius: 50%;
  background: #fff; color: #0d47a1;
  font-size: 9px; font-weight: 800; text-align: center; line-height: 1.2;
  display: flex; align-items: center; justify-content: center;
  border: 3px solid #fdd835; flex-shrink: 0;
}
.gov-name { font-size: 11px; font-weight: 600; color: #90caf9; line-height: 1.5; }

.hero-text { margin: auto 0; padding: 2.5rem 0; }
.hero-title { font-size: 2rem; font-weight: 700; line-height: 1.2; letter-spacing: -0.5px; margin-bottom: 1rem; }
.hero-title .accent { color: #fdd835; }
.hero-desc { font-size: 14px; color: #90caf9; line-height: 1.7; max-width: 340px; margin-bottom: 1.5rem; }

.feature-list { list-style: none; padding: 0; display: flex; flex-direction: column; gap: 12px; }
.feature-list li { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #bbdefb; }
.feature-icon {
  width: 28px; height: 28px; border-radius: 6px;
  background: rgba(255,255,255,0.1);
  display: flex; align-items: center; justify-content: center;
  font-size: 15px; flex-shrink: 0;
}

.left-footer {
  font-size: 11px; color: #5c8ac7;
  border-top: 1px solid rgba(255,255,255,0.1);
  padding-top: 1rem; margin-top: 1rem;
}

.right-panel {
  background: #fff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 2.5rem;
}
.form-wrap { max-width: 360px; width: 100%; margin: 0 auto; }

.mobile-header { display: none; align-items: center; gap: 10px; margin-bottom: 2rem; }
.mobile-seal {
  width: 40px; height: 40px; border-radius: 50%;
  background: #0d47a1; color: #fff;
  font-size: 9px; font-weight: 800; text-align: center; line-height: 1.2;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fdd835;
}
.mobile-name { font-size: 12px; font-weight: 600; color: #1565c0; line-height: 1.5; }

.form-eyebrow { font-size: 11px; font-weight: 600; color: #1565c0; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px; }
.form-heading { font-size: 22px; font-weight: 700; color: #1a1a2e; margin-bottom: 4px; }
.form-sub { font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 1.75rem; }

.alert {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 10px 14px; border-radius: 8px;
  font-size: 13px; line-height: 1.5; margin-bottom: 1.25rem;
}
.alert i { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
.alert-error { background: #ffebee; border: 1px solid #ef9a9a; color: #c62828; }
.alert-success { background: #e8f5e9; border: 1px solid #a5d6a7; color: #1b5e20; }

.field { margin-bottom: 1.25rem; }
.field-label { display: block; font-size: 12px; font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }
.field-wrap { position: relative; }
.field-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #94a3b8; pointer-events: none; }
.field-input {
  width: 100%; height: 42px; padding: 0 12px 0 38px;
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: 14px; color: #1a1a2e; background: #fff;
  font-family: inherit; outline: none; transition: border-color 0.15s;
}
.field-input--padded { padding-right: 40px; }
.field-input:focus { border-color: #1565c0; box-shadow: 0 0 0 3px rgba(21,101,192,0.12); }
.has-error .field-input { border-color: #e53935; }
.has-error .field-input:focus { box-shadow: 0 0 0 3px rgba(229,57,53,0.12); }
.field-error { font-size: 11px; color: #c62828; margin-top: 5px; display: flex; align-items: center; gap: 4px; }

.toggle-pw {
  position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
  background: none; border: none; cursor: pointer;
  font-size: 16px; color: #94a3b8; padding: 2px; line-height: 1;
}
.toggle-pw:hover { color: #475569; }

.forgot-row { display: flex; justify-content: flex-end; margin-top: -0.75rem; margin-bottom: 1.25rem; }
.forgot-link { font-size: 12px; color: #1565c0; font-weight: 500; text-decoration: none; }
.forgot-link:hover { text-decoration: underline; }

.submit-btn {
  width: 100%; height: 44px; background: #0d47a1; color: #fff;
  border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
  cursor: pointer; font-family: inherit;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: background 0.15s;
}
.submit-btn:hover { background: #1565c0; }
.submit-btn:disabled { background: #90a4ae; cursor: not-allowed; }
.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.helpdesk {
  text-align: center; font-size: 12px; color: #64748b;
  margin-top: 1.5rem; padding-top: 1.25rem;
  border-top: 1px solid #e2e8f0;
}
.helpdesk a { color: #1565c0; font-weight: 500; text-decoration: none; }

.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

@media (max-width: 768px) {
  .login-page { grid-template-columns: 1fr; }
  .left-panel { display: none; }
  .right-panel { padding: 2rem 1.25rem; justify-content: flex-start; padding-top: 3rem; }
  .mobile-header { display: flex; }
}
</style>