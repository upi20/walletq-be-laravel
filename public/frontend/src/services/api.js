import axios from 'axios';

// Create axios instance with default configs
const api = axios.create({
  baseURL: '/api/v1', // Path relatif ke API Laravel
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor untuk menambahkan token JWT ke setiap request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Interceptor untuk handling response & error
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Handle 401 Unauthorized - redirect to login
    if (error.response && error.response.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/frontend/login';
    }
    return Promise.reject(error);
  }
);

// Auth services
export const authService = {
  login: (email, password) => api.post('/auth/login', { email, password }),
  register: (name, email, password, password_confirmation) => 
    api.post('/auth/register', { name, email, password, password_confirmation }),
  logout: () => api.post('/auth/logout'),
  getProfile: () => api.get('/auth/me'),
  refreshToken: () => api.post('/auth/refresh'),
};

// Transaction services
export const transactionService = {
  getAll: (params) => api.get('/user/transaction', { params }),
  getById: (id) => api.get(`/user/transaction/${id}`),
  create: (data) => api.post('/user/transaction', data),
  update: (id, data) => api.put(`/user/transaction/${id}`, data),
  delete: (id) => api.delete(`/user/transaction/${id}`),
  import: (formData) => {
    const config = { headers: { 'Content-Type': 'multipart/form-data' } };
    return api.post('/user/transaction/import', formData, config);
  },
};

// Account services
export const accountService = {
  getAll: () => api.get('/user/account'),
  getById: (id) => api.get(`/user/account/${id}`),
  create: (data) => api.post('/user/account', data),
  update: (id, data) => api.put(`/user/account/${id}`, data),
  delete: (id) => api.delete(`/user/account/${id}`),
};

// Account category services
export const accountCategoryService = {
  getAll: () => api.get('/user/account-category'),
  getById: (id) => api.get(`/user/account-category/${id}`),
  create: (data) => api.post('/user/account-category', data),
  update: (id, data) => api.put(`/user/account-category/${id}`, data),
  delete: (id) => api.delete(`/user/account-category/${id}`),
};

// Transaction category services
export const transactionCategoryService = {
  getAll: () => api.get('/user/transaction-category'),
  getById: (id) => api.get(`/user/transaction-category/${id}`),
  create: (data) => api.post('/user/transaction-category', data),
  update: (id, data) => api.put(`/user/transaction-category/${id}`, data),
  delete: (id) => api.delete(`/user/transaction-category/${id}`),
};

export default api;
