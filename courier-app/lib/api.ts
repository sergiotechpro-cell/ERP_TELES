import axios from 'axios';
import { Capacitor } from '@capacitor/core';
import type { 
  User, 
  LoginResponse, 
  AssignmentsResponse,
  Assignment,
  SalesResponse,
  Sale
} from '@/types';

// Detectar URL de API según el entorno
function getApiUrl(): string {
  // Si hay una URL configurada en variables de entorno, usarla
  if (process.env.NEXT_PUBLIC_API_URL) {
    return process.env.NEXT_PUBLIC_API_URL;
  }
  
  // Si está en Android (emulador o dispositivo)
  if (Capacitor.isNativePlatform() && Capacitor.getPlatform() === 'android') {
    // En emulador Android, 10.0.2.2 apunta al host de la máquina
    // Si el servidor Laravel está corriendo localmente, usar esto:
    // return 'http://10.0.2.2:8000/api';
    
    // Por defecto, usar producción
    return 'https://erpteles-production.up.railway.app/api';
  }
  
  // Si está en iOS (simulador o dispositivo)
  if (Capacitor.isNativePlatform() && Capacitor.getPlatform() === 'ios') {
    // En simulador iOS, localhost funciona
    // Pero para producción, usar la URL de producción
    return 'https://erpteles-production.up.railway.app/api';
  }
  
  // Para desarrollo web (navegador)
  return 'http://localhost:8000/api';
}

const API_URL = getApiUrl();

// Log de la URL para debugging (siempre en Capacitor)
if (typeof window !== 'undefined') {
  try {
    const { Capacitor } = require('@capacitor/core');
    if (Capacitor.isNativePlatform()) {
      console.log('🔗 API URL:', API_URL);
      console.log('📱 Platform:', Capacitor.getPlatform());
    }
  } catch (e) {
    // Ignorar si Capacitor no está disponible
  }
}

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
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

// Interceptor para manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    // Log del error para debugging
    if (typeof window !== 'undefined' && process.env.NODE_ENV === 'development') {
      console.error('API Error:', {
        url: error.config?.url,
        method: error.config?.method,
        status: error.response?.status,
        data: error.response?.data,
        message: error.message,
        baseURL: API_URL,
      });
    }
    return Promise.reject(error);
  }
);

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

