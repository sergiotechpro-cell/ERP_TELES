export interface User {
  id: number;
  name: string;
  email: string;
  telefono?: string;
}

export interface LoginResponse {
  token: string;
  user: User;
}

export interface Pedido {
  id: number;
  estado: string;
  direccion_entrega: string;
  lat: number;
  lng: number;
  costo_envio: number;
  productos: Producto[];
  checklist: ChecklistItem[];
}

export interface Producto {
  producto: string;
  cantidad: number;
  precio_unitario: number;
}

export interface ChecklistItem {
  id: number;
  texto: string;
  completado: boolean;
}

export interface Origin {
  lat: number;
  lng: number;
  name?: string;
  address?: string;
}

export interface Assignment {
  id: number;
  estado: 'pendiente' | 'en_ruta' | 'entregado' | 'devuelto';
  asignado_at: string;
  salida_at: string | null;
  entregado_at?: string | null;
  origen?: Origin;
  pedido: Pedido;
}

export interface AssignmentsResponse {
  data: Assignment[];
}

export interface SaleItem {
  producto: string;
  cantidad: number;
  precio_unitario: number;
  subtotal: number;
}

export interface Sale {
  id: number;
  fecha: string;
  subtotal: number;
  envio: number;
  total: number;
  forma_pago: string;
  vendedor: {
    id: number;
    name: string;
  } | null;
  cliente: {
    id: number;
    nombre: string;
  } | null;
  items: SaleItem[];
}

export interface SalesSummary {
  total_ventas: number;
  total_bruto: number;
  total_efectivo: number;
  total_tarjeta: number;
  total_transferencia: number;
}

export interface SalesResponse {
  data: Sale[];
  summary: SalesSummary;
}

