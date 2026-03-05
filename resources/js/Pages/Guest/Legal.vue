<script setup>
import { Head, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
import { useI18n } from '@/composables/useI18n.js';

const { t, localizedField } = useI18n();

const props = defineProps({
    facility: Object,
});
</script>

<template>
  <BookingLayout>
    <Head :title="t('legal_modal_title') + ' - ' + localizedField(facility, 'name')" />

    <div class="py-24 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16 animate-in fade-in slide-in-from-bottom-4 duration-700">
          <span class="inline-block px-4 py-1.5 bg-teal-50 text-teal-700 font-bold text-xs tracking-[0.2em] rounded-full uppercase mb-6">Facility Information</span>
          <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 tracking-tight">{{ localizedField(facility, 'name') }}</h1>
          <p class="mt-6 text-slate-500 font-bold text-lg tracking-wide uppercase">{{ t('legal_modal_title') }}</p>
        </div>
  
        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/50 overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-700">
          <div class="p-8 sm:p-12 lg:p-16">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-12">
              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_business_name') }}</dt>
                <dd class="text-xl font-bold text-slate-900 leading-snug">{{ facility.scta_business_name || localizedField(facility, 'name') }}</dd>
              </div>

              <div v-if="facility.scta_representative" class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_representative') }}</dt>
                <dd class="text-xl font-bold text-slate-900 leading-snug">{{ facility.scta_representative }}</dd>
              </div>
              
              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_address') }}</dt>
                <dd class="text-slate-600 leading-relaxed font-medium">{{ facility.scta_address || localizedField(facility, 'address') }}</dd>
              </div>

              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_contact') }}</dt>
                <dd class="text-slate-600 leading-relaxed font-medium">
                  <div>{{ facility.scta_contact_email || facility.email || (t('inquiry')) }}</div>
                  <div v-if="facility.phone" class="mt-1">{{ facility.phone }}</div>
                  <div v-if="facility.scta_contact_tel_disclaimer" class="mt-2 text-[10px] text-slate-400 font-bold uppercase tracking-wider italic">
                    {{ facility.scta_contact_tel_disclaimer }}
                  </div>
                </dd>
              </div>
  
              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_price') }}</dt>
                <dd class="text-slate-600 leading-relaxed font-medium">
                  {{ facility.scta_price_description || t('default_price_description') }}
                </dd>
              </div>
  
              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_payment') }}</dt>
                <dd class="text-slate-600 leading-relaxed font-medium whitespace-pre-line">
                  {{ facility.scta_payment_method_time || t('default_payment_method') }}
                </dd>
              </div>

              <div class="space-y-3 group border-b border-slate-50 pb-8 md:border-none md:pb-0">
                <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_service') }}</dt>
                <dd class="text-slate-600 leading-relaxed font-medium whitespace-pre-line">
                  {{ facility.scta_service_delivery_time || t('default_service_delivery') }}
                </dd>
              </div>

              <!-- Full width sections -->
              <div class="space-y-6 md:col-span-2 pt-8 border-t border-slate-100">
                <div class="space-y-2">
                    <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_cancel') }}</dt>
                    <dd class="text-slate-600 leading-relaxed font-medium whitespace-pre-line bg-slate-50 p-6 rounded-2xl border border-slate-100">
                        {{ facility.cancellation_policy_text || t('default_service_delivery') }}
                    </dd>
                </div>

                <div class="space-y-2">
                    <dt class="text-xs font-bold text-teal-600 uppercase tracking-[0.2em]">{{ t('legal_terms') }}</dt>
                    <dd class="text-slate-600 leading-relaxed font-medium whitespace-pre-line">
                        <div v-if="facility.terms_text" class="bg-white p-6 rounded-2xl border border-slate-100 text-sm">
                            {{ facility.terms_text }}
                        </div>
                    </dd>
                </div>
              </div>
            </dl>
          </div>
        </div>
  
        <div class="mt-16 text-center">
          <Link :href="route('guest.booking.show', facility.slug || facility.uuid)" 
                class="inline-flex items-center gap-2 px-8 py-4 bg-white text-slate-600 font-bold rounded-2xl shadow-sm border border-slate-200 hover:bg-slate-50 hover:text-teal-700 transition-all active:scale-95 group">
            <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ t('back_to_booking') }}
          </Link>
        </div>
  
      </div>
    </div>
  </BookingLayout>
</template>
