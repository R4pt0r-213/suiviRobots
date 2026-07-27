import type { Alert, Metrics, Robot, Ticket, TimelineEvent } from '@/types'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

async function request<T>(path: string, options?: RequestInit): Promise<T> {
  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: { 'Content-Type': 'application/json', ...options?.headers },
  })
  if (!response.ok) throw new Error(`API error ${response.status}`)
  return response.json() as Promise<T>
}

export const api = {
  simulate: () => request<{ updatedRobots: number; generatedAt: string }>('/simulation/tick', { method: 'POST' }),
  robots: () => request<{ items: Robot[]; total: number }>('/robots'),
  telemetry: (id: string, limit = 60) => request<{ items: Metrics[] }>(`/robots/${id}/telemetry?limit=${limit}`),
  timeline: (id: string) => request<{ items: TimelineEvent[] }>(`/robots/${id}/timeline`),
  alerts: () => request<{ items: Alert[]; total: number }>('/alerts?limit=100'),
  acknowledgeAlert: (id: string) => request<Alert>(`/alerts/${id}/acknowledge`, { method: 'PATCH' }),
  resolveAlert: (id: string) => request<Alert>(`/alerts/${id}/resolve`, { method: 'PATCH' }),
  tickets: () => request<{ items: Ticket[] }>('/maintenance-tickets'),
}
