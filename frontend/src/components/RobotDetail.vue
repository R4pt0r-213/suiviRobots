<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { api } from '@/services/api'
import type { Metrics, Robot, TimelineEvent } from '@/types'

const props = defineProps<{ robot: Robot }>()
const emit = defineEmits<{ close: [] }>()
const metrics = ref<Metrics[]>([])
const timeline = ref<TimelineEvent[]>([])

onMounted(async () => {
  const [telemetry, events] = await Promise.all([api.telemetry(props.robot.id, 45), api.timeline(props.robot.id)])
  metrics.value = telemetry.items
  timeline.value = events.items
})

const latest = computed(() => metrics.value.at(-1) || props.robot.latestMetrics)
</script>

<template>
  <div class="fixed inset-0 z-[60] flex justify-end bg-slate-950/30 p-4 backdrop-blur-sm" @click.self="emit('close')">
    <UCard class="h-full w-full max-w-xl overflow-auto">
      <template #header>
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-600">{{ robot.serialNumber }}</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ robot.name }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ robot.facility.name }} · {{ robot.facility.location }}</p>
          </div>
          <div class="flex items-center gap-2">
            <UBadge
              :color="robot.status === 'online' ? 'success' : robot.status === 'maintenance' ? 'warning' : 'neutral'"
              variant="soft"
            >
              {{ robot.status === 'online' ? 'En ligne' : robot.status === 'maintenance' ? 'Maintenance' : 'Hors ligne' }}
            </UBadge>
            <UButton color="neutral" variant="ghost" icon="i-lucide-x" @click="emit('close')" />
          </div>
        </div>
      </template>

      <div class="space-y-5">
        <section class="grid grid-cols-2 gap-3 sm:grid-cols-4">
          <UCard variant="subtle">
            <p class="text-sm text-slate-500">Batterie</p>
            <p class="mt-1 text-xl font-bold">{{ latest?.battery.toFixed(0) ?? '—' }}%</p>
          </UCard>
          <UCard variant="subtle">
            <p class="text-sm text-slate-500">Température</p>
            <p class="mt-1 text-xl font-bold">{{ latest?.temperature.toFixed(1) ?? '—' }}°</p>
          </UCard>
          <UCard variant="subtle">
            <p class="text-sm text-slate-500">Charge</p>
            <p class="mt-1 text-xl font-bold">{{ latest?.systemLoad.toFixed(0) ?? '—' }}%</p>
          </UCard>
          <UCard variant="subtle">
            <p class="text-sm text-slate-500">Doses</p>
            <p class="mt-1 text-xl font-bold">{{ latest?.dosesPrepared.toLocaleString('fr-FR') ?? '—' }}</p>
          </UCard>
        </section>

        <UCard variant="subtle">
          <div class="mb-3 flex items-center justify-between">
            <div>
              <h3 class="font-semibold text-slate-950">Activité récente</h3>
              <p class="text-sm text-slate-500">Charge système sur les derniers relevés.</p>
            </div>
            <UBadge color="success" variant="soft">Live</UBadge>
          </div>
          <div class="mt-2 flex justify-between text-sm text-slate-500">
            <span>Charge système</span>
            <span>{{ latest?.cycleTimeMs ?? '—' }} ms / cycle</span>
          </div>
        </UCard>

        <UCard variant="subtle">
          <h3 class="font-semibold text-slate-950">Chronologie</h3>
          <div class="mt-4 space-y-3">
            <div v-for="event in timeline.slice(0, 7)" :key="event.at + event.title" class="flex gap-3 rounded-2xl bg-white p-3">
              <UBadge
                :color="event.type === 'alert' ? 'error' : event.type === 'maintenance' ? 'warning' : 'info'"
                variant="soft"
                class="h-fit"
              >
                {{ event.type }}
              </UBadge>
              <div class="min-w-0 flex-1">
                <p class="font-medium text-slate-900">{{ event.title }}</p>
                <p class="text-sm text-slate-500">{{ event.detail }}</p>
              </div>
              <time class="text-xs text-slate-400">
                {{ new Date(event.at).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }) }}
              </time>
            </div>
          </div>
        </UCard>
      </div>
    </UCard>
  </div>
</template>
