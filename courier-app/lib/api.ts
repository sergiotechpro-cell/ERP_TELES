import axios from 'axios';
import type { 
  User, 
  LoginResponse, 
  AssignmentsResponse,
  Assignment,
  SalesResponse,
  Sale
} from '@/types';

const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor para agregar token a las peticiones
api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('courier_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
});

export const authAPI = {
  login: async (email: string, password: string): Promise<LoginResponse> => {
    const { data } = await api.post<LoginResponse>('/courier/login', {
      email,
      password,
    });
    
    // Guardar token en localStorage
    if (typeof window !== 'undefined') {
      localStorage.setItem('courier_token', data.token);
      localStorage.setItem('courier_user', JSON.stringify(data.user));
    }
    
    return data;
  },

  logout: async (): Promise<void> => {
    await api.post('/courier/logout');
    
    if (typeof window !== 'undefined') {
      localStorage.removeItem('courier_token');
      localStorage.removeItem('courier_user');
    }
  },

  getProfile: async (): Promise<User> => {
    const { data } = await api.get<{ user: User }>('/courier/profile');
    return data.user;
  },
};

export const courierAPI = {
  getAssignments: async (): Promise<AssignmentsResponse> => {
    const { data } = await api.get<AssignmentsResponse>('/courier/assignments');
    return data;
  },

  getAssignment: async (id: number): Promise<Assignment> => {
    const { data } = await api.get<{ data: Assignment }>(`/courier/assignments/${id}`);
    return data.data;
  },

  startAssignment: async (id: number): Promise<void> => {
    await api.post(`/courier/assignments/${id}/start`);
  },

  completeAssignment: async (id: number): Promise<void> => {
    await api.post(`/courier/assignments/${id}/complete`);
  },

  getSales: async (startDate?: string, endDate?: string): Promise<SalesResponse> => {
    const params: any = {};
    if (startDate) params.start_date = startDate;
    if (endDate) params.end_date = endDate;
    const { data } = await api.get<SalesResponse>('/courier/sales', { params });
    return data;
  },
};

export default api;

