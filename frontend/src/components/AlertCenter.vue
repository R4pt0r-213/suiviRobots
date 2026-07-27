<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Alert } from '@/types'

const props = defineProps<{ alerts: Alert[] }>()
const emit = defineEmits<{
  close: []
  acknowledge: [alert: Alert]
  resolve: [alert: Alert]
}>()

const search = ref('')
const severity = ref('all')
const status = ref('all')

const filteredAlerts = computed(() => props.alerts.filter((alert) => {
  const matchesSearch = `${alert.title} ${alert.robotName} ${alert.message}`.toLowerCase().includes(search.value.toLowerCase())
  const matchesSeverity = severity.value === 'all' || alert.severity === severity.value
  const matchesStatus = status.value === 'all' || alert.status === status.value
  return matchesSearch && matchesSeverity && matchesStatus
}))

const count = (value: Alert['severity']) => props.alerts.filter((alert) => alert.severity === value && alert.status !== 'resolved').length
const statusLabel = (value: Alert['status']) => ({ open: 'Ouverte', acknowledged: 'Prise en charge', resolved: 'Résolue' })[value]
const severityLabel = (value: Alert['severity']) => ({ critical: 'Critique', warning: 'Avertissement', info: 'Information' })[value]
const statusColor = (value: Alert['status']) => ({ open: 'error', acknowledged: 'warning', resolved: 'success' })[value] as 'error' | 'warning' | 'success'
const severityColor = (value: Alert['severity']) => ({ critical: 'error', warning: 'warning', info: 'info' })[value] as 'error' | 'warning' | 'info'
</script>

<template>
  <div class="fixed inset-0 z-50 overflow-auto bg-slate-50 p-6">
    <div class="mx-auto flex max-w-7xl flex-col gap-5">
      <header class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Centre d’alertes</p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950">Toutes les alertes</h2>
          <p class="mt-1 text-sm text-slate-500">Filtrez, prenez en charge et résolvez les événements du parc.</p>
        </div>
        <UButton color="neutral" variant="ghost" icon="i-lucide-x" @click="emit('close')" />
      </header>

      <section class="grid gap-4 md:grid-cols-4">
        <UCard>
          <p class="text-sm text-slate-500">Alertes affichées</p>
          <p class="mt-2 text-3xl font-bold text-slate-950">{{ filteredAlerts.length }}</p>
        </UCard>
        <UCard>
          <p class="text-sm text-slate-500">Critiques actives</p>
          <p class="mt-2 text-3xl font-bold text-red-600">{{ count('critical') }}</p>
        </UCard>
        <UCard>
          <p class="text-sm text-slate-500">Avertissements</p>
          <p class="mt-2 text-3xl font-bold text-amber-600">{{ count('warning') }}</p>
        </UCard>
        <UCard>
          <p class="text-sm text-slate-500">Résolues</p>
          <p class="mt-2 text-3xl font-bold text-emerald-600">{{ alerts.filter(a => a.status === 'resolved').length }}</p>
        </UCard>
      </section>

      <UCard>
        <div class="grid gap-3 md:grid-cols-[1fr_220px_220px]">
          <UInput v-model="search" icon="i-lucide-search" placeholder="Rechercher une alerte ou un robot…" />
          <USelect
            v-model="severity"
            :items="[
              { label: 'Toutes les gravités', value: 'all' },
              { label: 'Critique', value: 'critical' },
              { label: 'Avertissement', value: 'warning' },
              { label: 'Information', value: 'info' },
            ]"
          />
          <USelect
            v-model="status"
            :items="[
              { label: 'Tous les statuts', value: 'all' },
              { label: 'Ouvertes', value: 'open' },
              { label: 'Prises en charge', value: 'acknowledged' },
              { label: 'Résolues', value: 'resolved' },
            ]"
          />
        </div>
      </UCard>

      <UCard>
        <div class="overflow-hidden rounded-2xl border border-slate-200">
          <div class="grid grid-cols-[1.8fr_1fr_auto_auto_1fr] gap-4 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <span>Alerte</span>
            <span>Robot</span>
            <span>Gravité</span>
            <span>Statut</span>
            <span>Action</span>
          </div>

          <article
            v-for="alert in filteredAlerts"
            :key="alert.id"
            class="grid grid-cols-[1.8fr_1fr_auto_auto_1fr] items-center gap-4 border-t border-slate-100 px-4 py-4"
          >
            <div>
              <p class="font-medium text-slate-950">{{ alert.title }}</p>
              <p class="mt-1 line-clamp-1 text-sm text-slate-500">{{ alert.message }}</p>
              <p class="mt-1 text-xs text-slate-400">
                {{ new Date(alert.triggeredAt).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
              </p>
            </div>
            <p class="font-medium text-slate-800">{{ alert.robotName }}</p>
            <UBadge :color="severityColor(alert.severity)" variant="soft">{{ severityLabel(alert.severity) }}</UBadge>
            <UBadge :color="statusColor(alert.status)" variant="subtle">{{ statusLabel(alert.status) }}</UBadge>
            <div>
              <UButton v-if="alert.status === 'open'" size="xs" variant="soft" @click="emit('acknowledge', alert)">Prendre en charge</UButton>
              <UButton v-else-if="alert.status === 'acknowledged'" size="xs" color="success" variant="soft" @click="emit('resolve', alert)">Marquer résolue</UButton>
              <span v-else class="text-sm text-slate-400">Terminée</span>
            </div>
          </article>

          <div v-if="!filteredAlerts.length" class="border-t border-slate-100 p-10 text-center">
            <p class="font-medium text-slate-900">Aucune alerte ne correspond</p>
            <p class="mt-1 text-sm text-slate-500">Modifiez les filtres ou la recherche.</p>
          </div>
        </div>
      </UCard>
    </div>
  </div>
</template>
