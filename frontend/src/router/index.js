import { createRouter, createWebHistory } from 'vue-router'

// This import now works because the file is inside /src/views/
import Login from '/views/auth/Login.vue'
import Dashboard from '/views/admin/Dashboard.vue'
import Departments from '/views/admin/Departments.vue'
import Users from '/views/admin/Users.vue'
import RequestTypes from '/views/admin/RequestTypes.vue'
import Requests from '/views/admin/Requests.vue'


const routes = [
    {
        path: '/',
        name: 'login',
        component: Login
    },
    {
        path: '/admin/dashboard',
        name: 'dashboard',
        component: Dashboard
    },
     {
        path: '/admin/departments',
        name: 'departments',
        component: Departments
    },
     {
        path: '/admin/users',
        name: 'users',
        component: Users
    },
    
      {
        path: '/admin/Request-Types',
        name: 'RequestTypes',
        component: RequestTypes
    },

    {
        path: '/admin/Requests',
        name: 'Requests',
        component: Requests
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router