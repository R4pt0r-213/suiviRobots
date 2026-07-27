<script setup lang="ts">
import { computed, ref } from 'vue'
import type { Robot } from '@/types'

const props = defineProps<{ robots: Robot[] }>()
const emit = defineEmits<{ close: []; select: [robot: Robot] }>()

const search = ref('')
const status = ref('all')
const facility = ref('all')

const facilities = computed(() => [...new Set(props.robots.map((robot) => robot.facility.name))].sort())
const filteredRobots = computed(() => props.robots.filter((robot) => {
  const text = `${robot.name} ${robot.serialNumber} ${robot.model} ${robot.facility.name} ${robot.facility.city}`.toLowerCase()
  return text.includes(search.value.toLowerCase())
    && (status.value === 'all' || robot.status === status.value)
    && (facility.value === 'all' || robot.facility.name === facility.value)
}))
const countStatus = (value: Robot['status']) => props.robots.filter((robot) => robot.status === value).length
const freshness = (robot: Robot) => {
  if (!robot.lastSeenAt) return 'Jamais connecté'
  const minutes = Math.max(0, Math.round((Date.now() - new Date(robot.lastSeenAt).getTime()) / 60000))
  return minutes < 1 ? 'À l’instant' : `Il y a ${minutes} min`
}
</script>

<template>
  <div class="fixed inset-0 z-50 overflow-auto bg-slate-50 p-6">
    <div class="mx-auto flex max-w-7xl flex-col gap-5">
      <header class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-start sm:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">Gestion du parc</p>
          <h2 class="mt-1 text-2xl font-bold text-slate-950">Tous les robots</h2>
          <p class="mt-1 text-sm text-slate-500">Vue complète des équipements connectés.</p>
        </div>
        <UButton color="neutral" variant="ghost" icon="i-lucide-x" @click="emit('close')" />
      </header>

      <section class="grid gap-4 md:grid-cols-4">
        <UCard><p class="text-sm text-slate-500">Total</p><p class="mt-2 text-3xl font-bold">{{ robots.length }}</p></UCard>
        <UCard><p class="text-sm text-slate-500">En ligne</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ countStatus('online') }}</p></UCard>
        <UCard><p class="text-sm text-slate-500">Maintenance</p><p class="mt-2 text-3xl font-bold text-amber-600">{{ countStatus('maintenance') }}</p></UCard>
        <UCard><p class="text-sm text-slate-500">Hors ligne</p><p class="mt-2 text-3xl font-bold text-slate-500">{{ countStatus('offline') }}</p></UCard>
      </section>

      <UCard>
        <div class="grid gap-3 md:grid-cols-[1fr_220px_240px_auto]">
          <UInput v-model="search" icon="i-lucide-search" placeholder="Rechercher par nom, série, modèle ou site…" />
          <USelect
            v-model="status"
            :items="[
              { label: 'Tous les états', value: 'all' },
              { label: 'En ligne', value: 'online' },
              { label: 'Hors ligne', value: 'offline' },
              { label: 'Maintenance', value: 'maintenance' },
            ]"
          />
          <USelect
            v-model="facility"
            :items="[{ label: 'Tous les sites', value: 'all' }, ...facilities.map(name => ({ label: name, value: name }))]"
          />
          <UBadge color="neutral" variant="soft" size="lg">{{ filteredRobots.length }} résultat{{ filteredRobots.length > 1 ? 's' : '' }}</UBadge>
        </div>
      </UCard>

      <UCard>
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <UCard
            v-for="robot in filteredRobots"
            :key="robot.id"
            variant="subtle"
            class="cursor-pointer transition hover:-translate-y-0.5 hover:shadow-md"
            @click="emit('select', robot)"
          >
            <div class="space-y-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="font-semibold text-slate-950">{{ robot.name }}</p>
                  <p class="text-sm text-slate-500">{{ robot.serialNumber }} · {{ robot.model }}</p>
                </div>
                <UBadge
                  :color="robot.status === 'online' ? 'success' : robot.status === 'maintenance' ? 'warning' : 'neutral'"
                  variant="soft"
                >
                  {{ robot.status === 'online' ? 'En ligne' : robot.status === 'maintenance' ? 'Maintenance' : 'Hors ligne' }}
                </UBadge>
              </div>

              <div>
                <p class="font-medium text-slate-800">{{ robot.facility.name }}</p>
                <p class="text-sm text-slate-500">{{ robot.facility.city }} · {{ robot.facility.location }}</p>
              </div>

              <div class="grid grid-cols-3 gap-3 text-sm">
                <div class="rounded-xl bg-white p-3">
                  <p class="text-slate-500">Batterie</p>
                  <p class="font-semibold">{{ robot.latestMetrics?.battery.toFixed(0) ?? '—' }}%</p>
                </div>
                <div class="rounded-xl bg-white p-3">
                  <p class="text-slate-500">Temp.</p>
                  <p class="font-semibold">{{ robot.latestMetrics?.temperature.toFixed(1) ?? '—' }}°C</p>
                </div>
                <div class="rounded-xl bg-white p-3">
                  <p class="text-slate-500">Charge</p>
                  <p class="font-semibold">{{ robot.latestMetrics?.systemLoad.toFixed(0) ?? '—' }}%</p>
                </div>
              </div>

              <div class="flex items-center justify-between text-sm">
                <span class="text-slate-500">{{ freshness(robot) }}</span>
                <span class="font-medium text-emerald-600">Voir la fiche →</span>
              </div>
            </div>
          </UCard>
        </div>

        <div v-if="!filteredRobots.length" class="p-10 text-center">
          <p class="font-medium text-slate-900">Aucun robot trouvé</p>
          <p class="mt-1 text-sm text-slate-500">Modifiez vos critères de recherche.</p>
        </div>
      </UCard>
    </div>
  </div>
</template>
