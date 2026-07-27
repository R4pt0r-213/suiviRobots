export interface Metrics {
  id?: number
  battery: number
  temperature: number
  speed: number
  systemLoad: number
  dosesPrepared: number
  cycleTimeMs: number
  recordedAt: string
}

export interface Robot {
  id: string
  serialNumber: string
  name: string
  model: string
  facility: { name: string; city: string; location: string }
  status: 'online' | 'offline' | 'maintenance'
  firmwareVersion: string
  installedAt: string
  lastSeenAt: string | null
  latestMetrics: Metrics | null
  openAlerts?: number
}

export interface Alert {
  id: string
  robotId: string
  robotName: string
  type: string
  severity: 'critical' | 'warning' | 'info'
  title: string
  message: string
  status: 'open' | 'acknowledged' | 'resolved'
  triggeredAt: string
}

export interface Ticket {
  id: string
  robotId: string
  robotName: string
  title: string
  description: string
  priority: string
  status: string
  scheduledAt: string | null
  createdAt: string
}

export interface TimelineEvent {
  type: 'alert' | 'maintenance' | 'status'
  title: string
  detail: string | null
  severity: string
  at: string
}
