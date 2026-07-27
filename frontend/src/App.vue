<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import RobotDetail from '@/components/RobotDetail.vue'
import AlertCenter from '@/components/AlertCenter.vue'
import FleetCenter from '@/components/FleetCenter.vue'
import { api } from '@/services/api'
import type { Alert, Robot, Ticket } from '@/types'

const robots = ref<Robot[]>([])
const alerts = ref<Alert[]>([])
const tickets = ref<Ticket[]>([])
const selectedRobot = ref<Robot | null>(null)
const showAlertCenter = ref(false)
const showFleetCenter = ref(false)
const search = ref('')
const loading = ref(true)
const error = ref('')

const filteredRobots = computed(() => robots.value.filter((robot) =>
  `${robot.name} ${robot.serialNumber} ${robot.facility.name}`.toLowerCase().includes(search.value.toLowerCase()),
))
const online = computed(() => robots.value.filter((r) => r.status === 'online').length)
const openAlerts = computed(() => alerts.value.filter((a) => a.status === 'open'))
const critical = computed(() => openAlerts.value.filter((a) => a.severity === 'critical').length)
const activeTickets = computed(() => tickets.value.filter((ticket) => ticket.status !== 'completed').length)
const avgPerformance = computed(() => {
  const values = robots.value.flatMap((r) => r.latestMetrics ? [100 - r.latestMetrics.systemLoad * .18] : [])
  return values.length ? Math.round(values.reduce((a, b) => a + b, 0) / values.length) : 0
})
async function load() {
  try {
    const [parc, alertData, ticketData] = await Promise.all([api.robots(), api.alerts(), api.tickets()])
    robots.value = parc.items
    alerts.value = alertData.items
    tickets.value = ticketData.items
    error.value = ''
  } catch {
    error.value = 'La plateforme ne répond pas. Vérifiez que Symfony est démarré.'
  } finally {
    loading.value = false
  }
}

async function refresh() {
  await api.simulate()
  await load()
}

async function acknowledge(alert: Alert) {
  const updated = await api.acknowledgeAlert(alert.id)
  alerts.value = alerts.value.map((item) => item.id === updated.id ? updated : item)
}

async function resolve(alert: Alert) {
  const updated = await api.resolveAlert(alert.id)
  alerts.value = alerts.value.map((item) => item.id === updated.id ? updated : item)
}

let refreshTimer: ReturnType<typeof setInterval> | undefined
onMounted(() => {
  refresh()
  refreshTimer = setInterval(refresh, 5000)
})
onBeforeUnmount(() => {
  if (refreshTimer) clearInterval(refreshTimer)
})
</script>

<template>
  <UApp>
    <main class="min-h-screen bg-slate-50 text-slate-900">
      <div class="mx-auto flex w-full max-w-7xl flex-col gap-6 px-6 py-6">
        <header class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">e-santé robotik</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Supervision du parc médical</h1>
            <p class="mt-1 text-sm text-slate-500">Données simulées, sauvegardées et rafraîchies toutes les 5 secondes.</p>
          </div>

          <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <UInput
              v-model="search"
              icon="i-lucide-search"
              placeholder="Rechercher un robot ou un site"
              class="w-full sm:w-80"
            />
            <UBadge color="success" variant="soft" size="lg">Live</UBadge>
          </div>
        </header>

        <UAlert
          v-if="error"
          color="error"
          variant="soft"
          title="Erreur de connexion"
          :description="error"
        />

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
          <UCard>
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">Robots opérationnels</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ online }} <span class="text-base text-slate-400">/ {{ robots.length }}</span></p>
              </div>
              <UBadge color="success" variant="subtle">Parc</UBadge>
            </div>
          </UCard>

          <UCard>
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">Alertes actives</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ openAlerts.length }}</p>
              </div>
              <UBadge :color="critical ? 'error' : 'neutral'" variant="subtle">{{ critical }} critique{{ critical > 1 ? 's' : '' }}</UBadge>
            </div>
          </UCard>

          <UCard>
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm text-slate-500">Performance moyenne</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ avgPerformance }}%</p>
              </div>
            </div>
          </UCard>

          <UCard>
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm text-slate-500">Interventions planifiées</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ activeTickets }}</p>
              </div>
              <UBadge color="info" variant="subtle">Maintenance</UBadge>
            </div>
          </UCard>
        </section>

        <section class="grid gap-4 xl:grid-cols-[1fr_360px]">
          <UCard>
            <template #header>
              <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-slate-950">État du parc</h2>
                  <p class="text-sm text-slate-500">Liste simplifiée des robots supervisés.</p>
                </div>
                <UButton color="primary" variant="soft" trailing-icon="i-lucide-arrow-right" @click="showFleetCenter = true">
                  Afficher tout le parc
                </UButton>
              </div>
            </template>

            <div v-if="loading" class="py-10 text-center text-sm text-slate-500">Chargement de la flotte…</div>
            <div v-else class="divide-y divide-slate-100">
              <button
                v-for="robot in filteredRobots.slice(0, 8)"
                :key="robot.id"
                class="grid w-full gap-3 py-4 text-left transition hover:bg-slate-50 sm:grid-cols-[1.4fr_1fr_auto_auto]"
                @click="selectedRobot = robot"
              >
                <div class="px-2">
                  <p class="font-medium text-slate-950">{{ robot.name }}</p>
                  <p class="text-sm text-slate-500">{{ robot.serialNumber }} · {{ robot.model }}</p>
                </div>
                <div class="px-2">
                  <p class="font-medium text-slate-800">{{ robot.facility.name }}</p>
                  <p class="text-sm text-slate-500">{{ robot.facility.city }}</p>
                </div>
                <div class="px-2">
                  <UBadge
                    :color="robot.status === 'online' ? 'success' : robot.status === 'maintenance' ? 'warning' : 'neutral'"
                    variant="soft"
                  >
                    {{ robot.status === 'online' ? 'En ligne' : robot.status === 'maintenance' ? 'Maintenance' : 'Hors ligne' }}
                  </UBadge>
                </div>
                <div class="grid grid-cols-3 gap-3 px-2 text-sm text-slate-600">
                  <span>{{ robot.latestMetrics?.battery.toFixed(0) ?? '—' }}%</span>
                  <span>{{ robot.latestMetrics?.temperature.toFixed(1) ?? '—' }}°C</span>
                  <span>{{ robot.latestMetrics?.systemLoad.toFixed(0) ?? '—' }}%</span>
                </div>
              </button>
            </div>
          </UCard>

          <UCard>
            <template #header>
              <div class="flex items-start justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-slate-950">Alertes récentes</h2>
                  <p class="text-sm text-slate-500">À traiter en priorité.</p>
                </div>
                <UBadge :color="openAlerts.length ? 'error' : 'success'" variant="subtle">{{ openAlerts.length }}</UBadge>
              </div>
            </template>

            <div v-if="!openAlerts.length" class="rounded-2xl bg-slate-50 p-6 text-center">
              <p class="font-medium text-slate-900">Aucune alerte active</p>
              <p class="mt-1 text-sm text-slate-500">Tout est calme sur le parc.</p>
            </div>

            <div v-else class="space-y-3">
              <UCard v-for="alert in openAlerts.slice(0, 4)" :key="alert.id" variant="subtle">
                <div class="space-y-3">
                  <div class="flex items-start justify-between gap-3">
                    <div>
                      <p class="font-medium text-slate-950">{{ alert.title }}</p>
                      <p class="text-sm text-slate-500">{{ alert.robotName }}</p>
                    </div>
                    <UBadge
                      :color="alert.severity === 'critical' ? 'error' : alert.severity === 'warning' ? 'warning' : 'info'"
                      variant="soft"
                    >
                      {{ alert.severity }}
                    </UBadge>
                  </div>
                  <p class="text-sm text-slate-600">{{ alert.message }}</p>
                  <UButton size="xs" color="primary" variant="soft" @click="acknowledge(alert)">Prendre en charge</UButton>
                </div>
              </UCard>
            </div>

            <template #footer>
              <UButton block variant="ghost" trailing-icon="i-lucide-arrow-right" @click="showAlertCenter = true">
                Consulter toutes les alertes
              </UButton>
            </template>
          </UCard>
        </section>
      </div>
    </main>

    <RobotDetail v-if="selectedRobot" :robot="selectedRobot" @close="selectedRobot = null" />
    <AlertCenter
      v-if="showAlertCenter"
      :alerts="alerts"
      @close="showAlertCenter = false"
      @acknowledge="acknowledge"
      @resolve="resolve"
    />
    <FleetCenter
      v-if="showFleetCenter"
      :robots="robots"
      @close="showFleetCenter = false"
      @select="selectedRobot = $event"
    />
  </UApp>
</template>
