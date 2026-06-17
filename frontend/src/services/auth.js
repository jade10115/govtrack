import api from './api'

export default {

    login(data){

        return api.post(
            '/login',
            data
        )
    },

    profile(){

        return api.get('/profile')
    },

    logout(){

        return api.post('/logout')
    }
}