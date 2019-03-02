import axios from 'axios'

export default {
    get(url, request) {
        return axios.get(url, request)
            .then((response) => Promise.resolve(response.data))
            .catch((error) => Promise.reject(error.response))
    },
    post(url, request) {
        return axios.post(url, request)
            .then((response) => Promise.resolve(response))
            .catch((error) => Promise.reject(error.response));
    },
    patch(url, request) {
        return axios.patch(url, request)
            .then((response) => Promise.resolve(response))
            .catch((error) => Promise.reject(error.response));
    },
    delete(url, request) {
        return axios.delete(url, {data: request})
            .then((response) => Promise.resolve(response))
            .catch((error) => Promise.reject(error.response));
    },
}
