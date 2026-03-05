<template>
  <BookingLayout>
    <Head>
      <title>{{ (localizedField(facility, 'name') || t('booking_title_suffix')) + ' - ' + t('booking_title_suffix') }}</title>
      <link rel="canonical" :href="canonicalUrl" />
    </Head>

    <div class="w-full max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

      <!-- プレビューモードバナー -->
      <div v-if="is_preview_mode" class="mb-8 bg-amber-50 border-2 border-amber-200 rounded-2xl p-5 flex items-center gap-4">
        <div class="p-2 bg-amber-100 rounded-xl shrink-0">
          <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
          </svg>
        </div>
        <div>
          <p class="font-bold text-amber-800 text-sm">{{ t('preview_mode_title') }}</p>
          <p class="text-xs text-amber-700 mt-0.5">{{ t('preview_mode_desc') }}</p>
        </div>
      </div>
      
      <div class="mb-10 max-w-5xl mx-auto">
        <div class="flex flex-col md:flex-row gap-4">
          <!-- メイン画像 -->
          <div class="flex-1 rounded-3xl overflow-hidden shadow-2xl ring-1 ring-slate-900/10 bg-slate-100 aspect-[2/1] md:aspect-auto md:h-[400px]">
            <img v-if="facilityImages[activeFacilityImageIndex]" 
                 :src="facilityImages[activeFacilityImageIndex]" 
                 :alt="localizedField(facility, 'name')" 
                 class="w-full h-full object-cover transition duration-500" />
            <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-300">
              <svg class="w-16 h-16 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>

          <!-- サムネイル (PC: 横, Mobile: 下) -->
          <div v-if="facilityImages.length > 1" 
               class="flex md:flex-col gap-3 md:w-32 shrink-0">
            <button v-for="(url, idx) in facilityImages" :key="idx" 
                    @click="activeFacilityImageIndex = idx"
                    class="flex-1 md:flex-none aspect-[3/2] md:h-24 rounded-2xl overflow-hidden border-4 transition-all p-0.5 bg-white shadow-sm"
                    :class="activeFacilityImageIndex === idx ? 'border-primary-500 ring-4 ring-primary-100' : 'border-transparent opacity-60 hover:opacity-100'">
              <img :src="url" class="w-full h-full object-cover rounded-xl" />
            </button>
          </div>
        </div>
        <div class="text-center mt-12">
          <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight sm:text-5xl">{{ localizedField(facility, 'name') }}</h1>
          <p class="mt-4 max-w-2xl text-lg text-slate-500 mx-auto">{{ localizedField(facility, 'address') }}</p>
        </div>
      </div>

      <div class="booking-grid gap-8">
        
        <!-- 左側：検索フォームと施設情報 -->
        <div class="space-y-6">
          <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl ring-1 ring-slate-900/5 p-6 border border-white/50">
            <h2 class="text-lg font-bold text-slate-800 mb-4">{{ t('search_title') }}</h2>

            <form @submit.prevent="searchRooms" class="space-y-4">
              <!-- 日付選択カレンダー -->
              <DateRangePicker
                v-model="dateRange"
                :check-in-label="t('check_in')"
                :check-out-label="t('check_out')"
                :nights-label="isEn ? 'night(s)' : '泊'"
              />

              <!-- 人数選択 -->
              <div class="space-y-3">
                <!-- 大人 -->
                <div>
                  <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('guests_label') }}</label>
                  <div class="flex items-center gap-3">
                    <button type="button" @click="searchForm.adults = Math.max(1, searchForm.adults - 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                            :disabled="searchForm.adults <= 1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <span class="w-8 text-center font-bold text-slate-800">{{ searchForm.adults }}</span>
                    <button type="button" @click="searchForm.adults = Math.min(10, searchForm.adults + 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition"
                            :disabled="searchForm.adults >= 10">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                  </div>
                </div>

                <!-- 子供A -->
                <div v-if="has_person_pricing">
                  <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ child_settings?.child_a_label || '子供A' }}
                    <span v-if="child_settings?.child_a_policy" class="text-xs text-slate-400 ml-1">{{ child_settings.child_a_policy }}</span>
                  </label>
                  <div class="flex items-center gap-3">
                    <button type="button" @click="searchForm.child_a = Math.max(0, searchForm.child_a - 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                            :disabled="searchForm.child_a <= 0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <span class="w-8 text-center font-bold text-slate-800">{{ searchForm.child_a }}</span>
                    <button type="button" @click="searchForm.child_a = Math.min(10, searchForm.child_a + 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                  </div>
                </div>

                <!-- 子供B -->
                <div v-if="has_person_pricing && child_settings?.child_b_label">
                  <label class="block text-sm font-medium text-slate-700 mb-1">
                    {{ child_settings.child_b_label }}
                    <span v-if="child_settings?.child_b_policy" class="text-xs text-slate-400 ml-1">{{ child_settings.child_b_policy }}</span>
                  </label>
                  <div class="flex items-center gap-3">
                    <button type="button" @click="searchForm.child_b = Math.max(0, searchForm.child_b - 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                            :disabled="searchForm.child_b <= 0">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <span class="w-8 text-center font-bold text-slate-800">{{ searchForm.child_b }}</span>
                    <button type="button" @click="searchForm.child_b = Math.min(10, searchForm.child_b + 1)"
                            class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                  </div>
                </div>
              </div>

              <!-- エラーメッセージ -->
              <div v-if="errorMessage" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm font-medium">
                {{ errorMessage }}
              </div>

              <div class="pt-2">
                <button type="submit" :disabled="isSearching || !dateRange.checkIn || !dateRange.checkOut"
                        class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition shadow-lg disabled:opacity-50">
                  <span v-if="isSearching">{{ t('searching') }}</span>
                  <span v-else>{{ t('search_button') }}</span>
                </button>
              </div>
            </form>
          </div>

          <!-- 施設ルール -->
          <div class="bg-white/60 backdrop-blur-xl rounded-3xl shadow-sm ring-1 ring-slate-900/5 p-6">
            <h3 class="text-md font-bold text-slate-800 mb-3">{{ t('facility_info') }}</h3>
            <ul class="text-sm text-slate-600 space-y-2">
              <li class="flex justify-between border-b border-slate-100 pb-2">
                <span>{{ t('check_in') }}</span>
                <span class="font-medium text-slate-800">{{ facility?.check_in_start_time }} 〜 {{ facility?.check_in_end_time }}</span>
              </li>
              <li class="flex justify-between border-b border-slate-100 pb-2">
                <span>{{ t('check_out') }}</span>
                <span class="font-medium text-slate-800">{{ facility?.check_out_time }}</span>
              </li>
            </ul>
            <div class="mt-4 text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100" v-if="facility?.cancellation_policy_text">
              <strong>{{ t('cancellation_policy') }}</strong><br>
              <span class="whitespace-pre-line">{{ facility?.cancellation_policy_text }}</span>
            </div>
            <div class="mt-2 text-xs text-slate-500 bg-slate-50 p-3 rounded-lg border border-slate-100">
              <strong>{{ t('terms_and_info') }}</strong><br>
              <div v-if="facility?.terms_text" class="mt-1">
                <span class="whitespace-pre-line leading-relaxed line-clamp-2 italic text-slate-400">{{ facility?.terms_text }}</span>
              </div>
              <div class="mt-2">
                <Link :href="route('guest.legal', facility?.uuid)" class="text-primary-600 hover:underline font-medium flex items-center gap-1">
                  {{ t('terms_link') }}
                  <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </Link>
              </div>
            </div>
          </div>
        </div>

          <!-- 右側：検索結果 / 予約フォーム -->
        <div class="min-w-0 space-y-4">
          
          <!-- 予約完了時の表示 -->
          <div v-if="isReserved" class="p-8 text-center bg-emerald-50 rounded-2xl border border-emerald-100 relative">
            <button @click="closeReservedMessage" class="absolute top-4 right-4 text-emerald-400 hover:text-emerald-600 transition" :title="t('close')">
              <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
            <div class="w-16 h-16 mx-auto bg-emerald-100 fill-emerald-600 rounded-full flex items-center justify-center mb-4">
              <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 class="text-xl font-bold text-emerald-800 mb-2">{{ t('reserved_title') }}</h3>
            <p class="text-emerald-700 text-sm">{{ t('reserved_desc') }}</p>
          </div>

          <!-- 初期状態（未検索） -->
          <div v-else-if="!hasSearched" class="h-full min-h-[400px] flex flex-col items-center justify-center text-slate-400 bg-white/40 backdrop-blur-xl rounded-4xl border-2 border-dashed border-slate-200 p-8">
            <svg class="w-16 h-16 mb-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-lg font-medium whitespace-pre-line text-center">{{ t('initial_message') }}</p>
          </div>

          <!-- 検索後：結果一覧 -->
          <div v-else-if="!selectedRoom" class="space-y-4">
            <h2 class="text-xl font-bold text-slate-800 mb-4">{{ t('available_rooms') }}</h2>
            
            <div v-if="availableRooms.length === 0" class="bg-amber-50 rounded-2xl p-6 text-center border border-amber-200">
              <p class="text-amber-800 font-medium font-bold">{{ t('no_rooms') }}</p>
              <p class="text-sm text-amber-600 mt-2">{{ t('no_rooms_hint') }}</p>
            </div>

            <!-- 部屋カード -->
            <div v-for="room in availableRooms" :key="room.uuid" 
                 class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition flex flex-col sm:flex-row gap-6">
              <!-- 施設画像表示 -->
              <div class="flex flex-col gap-2 w-full sm:w-48">
                <div class="w-full aspect-video sm:aspect-square rounded-2xl bg-slate-100 overflow-hidden relative group shadow-sm border border-slate-100">
                  <img v-if="room.image_urls && room.image_urls.length > 0" 
                       :src="room.image_urls[activeImageIndexes[room.uuid] || 0]" 
                       class="w-full h-full object-cover group-hover:scale-110 transition duration-700" />
                  <div v-else class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                    <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-[10px] uppercase tracking-widest font-bold">No Image</span>
                  </div>
                  
                  <!-- 画像枚数インジケーター（オーバーレイ） -->
                  <div v-if="room.image_urls && room.image_urls.length > 1" 
                       class="absolute top-2 right-2 px-2 py-0.5 bg-black/40 backdrop-blur-md rounded-full text-[10px] text-white font-medium">
                    {{ (activeImageIndexes[room.uuid] || 0) + 1 }} / {{ room.image_urls.length }}
                  </div>
                </div>

                <!-- サムネイルギャラリー -->
                <div v-if="room.image_urls && room.image_urls.length > 1" class="flex gap-2 px-0.5 justify-center sm:justify-start">
                  <button v-for="(url, idx) in room.image_urls" :key="idx" 
                          @click="activeImageIndexes[room.uuid] = idx"
                          class="w-10 h-10 rounded-lg overflow-hidden border-2 transition-all p-0.5 bg-white shadow-sm"
                          :class="(activeImageIndexes[room.uuid] || 0) === idx ? 'border-primary-500 ring-2 ring-primary-100' : 'border-slate-100 opacity-60 hover:opacity-100'">
                    <img :src="url" class="w-full h-full object-cover rounded-md" />
                  </button>
                </div>
              </div>

              <div class="flex-1">
                <h3 class="text-xl font-bold text-slate-900">{{ room.name }}</h3>
                <div class="flex items-center gap-4 mt-2 text-sm text-slate-500">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    {{ t('capacity') }} {{ room.capacity }}{{ t('capacity_unit') }}
                  </span>
                </div>
                <p class="mt-3 text-sm text-slate-600 line-clamp-2">{{ room.description || t('room_description_default') }}</p>
              </div>
              
              <div class="sm:w-48 sm:border-l border-slate-100 sm:pl-6 flex flex-col justify-between pt-4 sm:pt-0 border-t sm:border-t-0 mt-4 sm:mt-0">
                <div class="text-right sm:text-left text-slate-500 text-sm">
                  {{ t('total_price') }} ({{ room.nights_count }}{{ t('nights') }})
                  <div class="text-2xl font-bold text-slate-900 mt-1">¥{{ room.total_amount.toLocaleString() }}</div>
                  <div class="text-xs mt-1">({{ t('cleaning_fee') }}: ¥{{ room.cleaning_fee.toLocaleString() }})</div>
                </div>
                <button @click="selectRoom(room)" class="mt-4 w-full py-2 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition">
                  {{ t('book_this_room') }}
                </button>
              </div>
            </div>
          </div>

          <!-- 部屋選択後：予約フォーム・Stripe -->
          <div v-else class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6 md:p-8">
            <button @click="selectedRoom = null; isReserved = false" class="mb-6 flex items-center text-sm font-medium text-slate-500 hover:text-slate-900 transition">
              <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
              {{ t('back_to_rooms') }}
            </button>
            
            <div class="border-b border-slate-100 pb-6 mb-6">
              <h2 class="text-2xl font-bold text-slate-900">{{ t('confirm_booking') }}</h2>
              <div class="mt-4 bg-slate-50 rounded-xl p-4 flex flex-col md:flex-row justify-between gap-4">
                <div>
                  <div class="text-primary-800 font-bold">{{ selectedRoom.name }}</div>
                  <div class="text-sm text-slate-600 mt-1">{{ searchForm.check_in_date }} 〜 {{ searchForm.check_out_date }} ({{ selectedRoom.nights_count }}{{ t('nights') }})</div>
                  <div class="text-sm text-slate-600">
                    {{ searchForm.adults }}{{ t('guest_count_suffix') }}
                    <template v-if="searchForm.child_a > 0"> / {{ child_settings?.child_a_label || '子供A' }} {{ searchForm.child_a }}名</template>
                    <template v-if="searchForm.child_b > 0"> / {{ child_settings?.child_b_label || '子供B' }} {{ searchForm.child_b }}名</template>
                  </div>
                </div>
                <div class="md:text-right flex flex-col justify-center">
                  <div class="text-sm text-slate-500">{{ t('total_payment') }}</div>
                  <div class="text-2xl font-bold text-slate-900 leading-none mt-1">¥{{ selectedRoom.total_amount.toLocaleString() }}</div>
                </div>
              </div>

              <!-- お支払い明細 -->
              <div class="mt-6 border-t border-slate-100 pt-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">{{ t('price_details') }}</h3>
                <div class="space-y-3">
                  <!-- 日別の宿泊料金 -->
                  <div v-for="price in selectedRoom.daily_prices" :key="price.date" class="flex flex-col sm:flex-row sm:justify-between gap-1 text-sm border-b border-slate-50 pb-2">
                    <div class="flex flex-col">
                      <span class="text-slate-700 font-medium">{{ price.date }} ({{ price.label }})</span>
                      <div v-if="selectedRoom.pricing_type === 'person'" class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                        {{ t('guests_label') }}: ¥{{ price.price.toLocaleString() }} × {{ searchForm.adults }}{{ t('guests_unit') }}
                        <template v-if="price.addChildAFee > 0 && searchForm.child_a > 0">
                          <br>
                          {{ child_settings?.child_a_label || '子供A' }}: ¥{{ price.addChildAFee.toLocaleString() }} × {{ searchForm.child_a }}名
                        </template>
                        <template v-if="price.addChildBFee > 0 && searchForm.child_b > 0">
                          <br>
                          {{ child_settings?.child_b_label || '子供B' }}: ¥{{ price.addChildBFee.toLocaleString() }} × {{ searchForm.child_b }}名
                        </template>
                      </div>
                    </div>
                    <span class="font-bold text-slate-800 text-right">¥{{ price.dayTotal.toLocaleString() }}</span>
                  </div>

                  <!-- 清掃費 -->
                  <div class="flex justify-between text-sm py-1">
                    <span class="text-slate-600">{{ t('cleaning_fee') }}</span>
                    <span class="font-medium text-slate-800">¥{{ selectedRoom.cleaning_fee.toLocaleString() }}</span>
                  </div>

                  <!-- 合計金額 -->
                  <div class="flex justify-between items-center pt-4 border-t border-slate-200 mt-2">
                    <span class="text-lg font-bold text-slate-900">{{ t('total') }}</span>
                    <div class="text-right">
                      <span class="text-xs text-slate-400 block mb-0.5">{{ t('total_payment') }} ({{ t('tax_inclusive') || '税込' }})</span>
                      <span class="text-3xl font-black text-primary-600 tracking-tight">¥{{ selectedRoom.total_amount.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stripe決済フォーム -->
            <div class="py-6">
              <h3 class="text-lg font-bold text-slate-800 mb-4">{{ t('payment_title') }}</h3>
              
              <!-- プレビューモード時のメッセージ -->
              <div v-if="is_preview_mode" class="space-y-6">
                <div class="space-y-4 mb-6 pt-4 border-t border-slate-100">
                  <h4 class="font-bold text-slate-700">{{ t('guest_info_title') }}</h4>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('name_label') }}</label>
                    <input type="text" disabled :placeholder="t('name_placeholder')" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-100 placeholder-slate-400 sm:text-sm cursor-not-allowed">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('email_label') }}</label>
                    <input type="email" disabled :placeholder="t('email_placeholder')" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-100 placeholder-slate-400 sm:text-sm cursor-not-allowed">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('phone_label') }}</label>
                    <input type="tel" disabled :placeholder="t('phone_placeholder')" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-100 placeholder-slate-400 sm:text-sm cursor-not-allowed">
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                  <h4 class="font-bold text-slate-700 mb-4">{{ t('card_info_title') }}</h4>
                  <div class="bg-slate-100 p-4 rounded-xl border border-slate-200 mb-4 text-center text-sm text-slate-400">
                    {{ t('preview_card_message') }}
                  </div>
                </div>

                <button disabled class="w-full py-4 bg-slate-300 text-white font-bold rounded-xl cursor-not-allowed">
                  {{ t('preview_button') }}
                </button>
                <p class="text-xs text-center text-slate-400 mt-2">{{ t('preview_note') }}</p>
              </div>

              <!-- 通常モード時のゲスト情報入力フォーム -->
              <template v-else>
              <div v-if="isInitializingPayment" class="p-8 text-center bg-slate-50 rounded-xl border border-slate-200">
                <svg class="animate-spin h-8 w-8 text-primary-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="mt-3 text-sm text-slate-500 font-medium">{{ t('payment_initializing') }}</p>
              </div>

              <!-- ゲスト情報入力フォーム -->
              <form v-show="!isInitializingPayment && clientSecret" @submit.prevent="submitPayment" class="space-y-6">
                
                <div class="space-y-4 mb-6 pt-4 border-t border-slate-100">
                  <h4 class="font-bold text-slate-700">{{ t('guest_info_title') }}</h4>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('name_label') }}</label>
                    <input type="text" v-model="guestForm.name" required :placeholder="t('name_placeholder')"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('email_label') }}</label>
                    <input type="email" v-model="guestForm.email" required :placeholder="t('email_placeholder')"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('phone_label') }}</label>
                    <input type="tel" v-model="guestForm.phone" required :placeholder="t('phone_placeholder')"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ t('remarks_label') }}</label>
                    <textarea v-model="guestForm.remarks" rows="3" :placeholder="t('remarks_placeholder')"
                              class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                  <h4 class="font-bold text-slate-700 mb-4">{{ t('card_info_title') }}</h4>
                  <div id="payment-element" class="bg-white p-4 rounded-xl border border-slate-200 mb-4"></div>
                </div>

                <div v-if="paymentError" class="p-3 bg-red-50 text-red-600 rounded-lg text-sm font-medium">
                  {{ paymentError }}
                </div>

                <!-- 規約同意チェックボックス -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                  <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" v-model="hasAgreedToTerms" 
                           class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-slate-600 leading-relaxed">
                      {{ t('terms_agree_prefix') }}<button type="button" @click="showLegalModal = true" class="text-primary-600 font-bold underline">{{ t('terms_agree_link') }}</button>{{ t('terms_agree_suffix') }}
                    </span>
                  </label>
                  <p v-if="agreementError" class="mt-2 text-xs text-red-500 font-bold">{{ t('terms_agree_error') }}</p>
                </div>

                <button type="submit" :disabled="isProcessingPayment || !stripeElements"
                        class="w-full py-4 bg-primary-600 text-white font-bold rounded-xl hover:bg-primary-700 transition shadow-lg shadow-primary-200 disabled:opacity-50 flex justify-center items-center">
                  <svg v-if="isProcessingPayment" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <span v-if="isProcessingPayment">{{ t('processing') }}</span>
                  <span v-else>{{ t('pay_and_confirm_prefix') }}{{ selectedRoom.total_amount.toLocaleString() }}{{ t('pay_and_confirm_suffix') }}</span>
                </button>
                <p class="text-xs text-center text-slate-400 mt-2">{{ t('payment_note') }}</p>
              </form>
              </template>
              
            </div>
            
          </div>

        </div>
      </div>

    </div>

    <!-- 法的表記モーダル -->
    <div v-if="showLegalModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6">
      <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showLegalModal = false"></div>
      <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col animate-in fade-in zoom-in duration-200">
        <!-- モーダルヘッダー -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white z-10">
          <h2 class="text-xl font-bold text-slate-900">{{ t('legal_modal_title') }}</h2>
          <button @click="showLegalModal = false" class="p-2 text-slate-400 hover:text-slate-600 rounded-full hover:bg-slate-100 transition">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <!-- モーダルコンテンツ -->
        <div class="p-6 overflow-y-auto custom-scrollbar">
          <div class="space-y-8">
            <dl class="space-y-4">
              <div v-for="(item, index) in legalItems" :key="index" v-show="item.value" class="flex flex-col sm:flex-row sm:gap-4 border-b border-slate-50 pb-4 last:border-0">
                <dt class="sm:w-1/3 text-xs font-bold text-slate-400 uppercase tracking-wider mb-1 sm:mb-0">{{ item.label }}</dt>
                <dd class="sm:w-2/3 text-sm text-slate-700 whitespace-pre-line leading-relaxed">
                  {{ item.value }}
                  <p v-if="item.disclaimer" class="mt-1 text-[10px] text-slate-500 italic">{{ item.disclaimer }}</p>
                </dd>
              </div>
            </dl>
          </div>
        </div>
        <!-- モーダルフッター -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end">
          <button @click="showLegalModal = false" class="px-6 py-2 bg-slate-900 text-white font-bold rounded-xl hover:bg-slate-800 transition">
            {{ t('close') }}
          </button>
        </div>
      </div>
    </div>
  </BookingLayout>
</template>

<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import BookingLayout from '@/Layouts/BookingLayout.vue';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { ref, computed, nextTick, watch } from 'vue';
import axios from 'axios';
import { loadStripe } from '@stripe/stripe-js';
import { useI18n } from '@/composables/useI18n.js';

const { t, locale, isEn, localizedField } = useI18n();

const props = defineProps({
    facility: Object,
    stripe_key: String,
    stripe_account: String,
    is_preview_mode: { type: Boolean, default: false },
    has_person_pricing: { type: Boolean, default: false },
    child_settings: { type: Object, default: null },
});

const canonicalUrl = computed(() => {
    const identifier = props.facility?.slug || props.facility?.uuid;
    return identifier ? route('guest.booking.show', identifier) : '';
});

// 法的表記のアイテムリスト（i18n対応）
const legalItems = computed(() => [
    { label: t('legal_business_name'), value: props.facility?.scta_business_name || localizedField(props.facility, 'name') },
    { label: t('legal_representative'), value: props.facility?.scta_representative },
    { label: t('legal_address'), value: props.facility?.scta_address || localizedField(props.facility, 'address') },
    { label: t('legal_contact'), value: [props.facility?.scta_contact_email || props.facility?.email, props.facility?.phone].filter(Boolean).join('\n'), disclaimer: props.facility?.scta_contact_tel_disclaimer },
    { label: t('legal_price'), value: props.facility?.scta_price_description || t('default_price_description') },
    { label: t('legal_payment'), value: props.facility?.scta_payment_method_time || t('default_payment_method') },
    { label: t('legal_service'), value: props.facility?.scta_service_delivery_time || t('default_service_delivery') },
    { label: t('legal_cancel'), value: props.facility?.cancellation_policy_text },
    { label: t('legal_terms'), value: props.facility?.terms_text }
]);

const showLegalModal = ref(false);
const hasAgreedToTerms = ref(false);
const agreementError = ref(false);

// 日付関連
const formatDateHelper = (date) => {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
};

const todayStr = formatDateHelper(new Date());
const tomorrow = new Date();
tomorrow.setDate(tomorrow.getDate() + 1);
const tomorrowStr = formatDateHelper(tomorrow);

// カレンダーコンポーネント用のリアクティブ日付範囲
const dateRange = ref({
    checkIn: todayStr,
    checkOut: tomorrowStr,
});

const searchForm = useForm({
    check_in_date: todayStr,
    check_out_date: tomorrowStr,
    guests: 2,
    adults: 2,
    child_a: 0,
    child_b: 0,
});

// dateRange が変わったら searchForm に反映
watch(dateRange, (newVal) => {
    if (newVal.checkIn) searchForm.check_in_date = newVal.checkIn;
    if (newVal.checkOut) searchForm.check_out_date = newVal.checkOut;
    isReserved.value = false;
}, { deep: true });

// 検索条件（人数）が変更されたら予約完了メッセージを消す
watch([() => searchForm.adults, () => searchForm.child_a, () => searchForm.child_b], () => {
    searchForm.guests = searchForm.adults + searchForm.child_a + searchForm.child_b;
    isReserved.value = false;
});

const closeReservedMessage = () => {
    isReserved.value = false;
};

// 検索ステート
const isSearching = ref(false);
const hasSearched = ref(false);
const availableRooms = ref([]);
const activeImageIndexes = ref({});
const activeFacilityImageIndex = ref(0);

const facilityImages = computed(() => {
    return [
        props.facility?.image_url,
        props.facility?.image_url_2,
        props.facility?.image_url_3
    ].filter(Boolean);
});

const errorMessage = ref('');
const selectedRoom = ref(null);

const searchRooms = async () => {
    errorMessage.value = '';
    isSearching.value = true;
    selectedRoom.value = null;
    isReserved.value = false;
    activeImageIndexes.value = {};

    try {
        const response = await axios.post(`/api/facilities/${props.facility?.uuid}/availability`, {
            check_in_date: searchForm.check_in_date,
            check_out_date: searchForm.check_out_date,
            adults: searchForm.adults,
            child_a: searchForm.child_a,
            child_b: searchForm.child_b,
        });

        availableRooms.value = response.data.rooms;
        hasSearched.value = true;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || t('search_error');
        hasSearched.value = false;
    } finally {
        isSearching.value = false;
    }
};

// 決済・予約関連ステート
const stripeInstance = ref(null);
const stripeElements = ref(null);
const clientSecret = ref('');
const isInitializingPayment = ref(false);
const isProcessingPayment = ref(false);
const paymentError = ref('');
const isReserved = ref(false);

const guestForm = useForm({
    name: '',
    email: '',
    phone: '',
    remarks: '',
});

const selectRoom = async (room) => {
    selectedRoom.value = room;
    paymentError.value = '';
    isReserved.value = false;
    hasAgreedToTerms.value = false;

    // プレビューモード時はStripe初期化をスキップ
    if (props.is_preview_mode) {
        return;
    }

    isInitializingPayment.value = true;
    
    try {
        // PaymentIntentの生成
        const response = await axios.post(`/api/facilities/${props.facility?.uuid}/payment-intent`, {
            room_uuid: room.uuid,
            check_in_date: searchForm.check_in_date,
            check_out_date: searchForm.check_out_date,
            adults: searchForm.adults,
            child_a: searchForm.child_a,
            child_b: searchForm.child_b,
        });

        clientSecret.value = response.data.clientSecret;

        // Stripeの初期化
        stripeInstance.value = await loadStripe(props.stripe_key);

        // DOMの更新完了を待つ
        await nextTick();

        // Elementsの初期化（locale: 'auto' でブラウザ言語に自動対応）
        const appearance = { theme: 'stripe' };
        stripeElements.value = stripeInstance.value.elements({
            clientSecret: clientSecret.value,
            appearance,
            locale: 'auto',
        });
        
        const paymentElement = stripeElements.value.create('payment');
        paymentElement.mount('#payment-element');

    } catch (error) {
        paymentError.value = error.response?.data?.error || t('payment_init_error');
    } finally {
        isInitializingPayment.value = false;
    }
};

const submitPayment = async () => {
    if (!stripeInstance.value || !stripeElements.value) return;

    // 規約同意チェック
    if (!hasAgreedToTerms.value) {
        agreementError.value = true;
        return;
    }
    agreementError.value = false;

    isProcessingPayment.value = true;
    paymentError.value = '';

    try {
        // 1. Stripe側で決済の確定
        const { error, paymentIntent } = await stripeInstance.value.confirmPayment({
            elements: stripeElements.value,
            confirmParams: {
                payment_method_data: {
                    billing_details: {
                        name: guestForm.name,
                        email: guestForm.email,
                        phone: guestForm.phone,
                    }
                }
            },
            redirect: 'if_required',
        });

        if (error) {
            paymentError.value = error.message;
            isProcessingPayment.value = false;
            return;
        }

        if (paymentIntent && paymentIntent.status === 'succeeded') {
            // 2. 自社側で予約データを保存
            await saveReservation(paymentIntent.id);
        } else {
            paymentError.value = t('payment_status_error') + (paymentIntent?.status || '不明');
            isProcessingPayment.value = false;
        }

    } catch (e) {
        paymentError.value = t('payment_unexpected_error') + e.message;
        isProcessingPayment.value = false;
    }
};

const saveReservation = async (paymentIntentId) => {
    try {
        const response = await axios.post(`/api/facilities/${props.facility?.uuid}/reservations`, {
            payment_intent_id: paymentIntentId,
            room_uuid: selectedRoom.value.uuid,
            check_in_date: searchForm.check_in_date,
            check_out_date: searchForm.check_out_date,
            adults: searchForm.adults,
            child_a: searchForm.child_a,
            child_b: searchForm.child_b,
            guest_name: guestForm.name,
            guest_email: guestForm.email,
            guest_phone: guestForm.phone,
            guest_remarks: guestForm.remarks,
            total_amount: selectedRoom.value.total_amount,
        });

        if (response.data.success) {
            isReserved.value = true;
            clientSecret.value = '';
            hasAgreedToTerms.value = false;
        }
    } catch (error) {
        paymentError.value = t('reservation_save_error');
        console.error(error);
    } finally {
        isProcessingPayment.value = false;
    }
};
</script>

<style scoped>
.booking-grid {
  display: grid;
  grid-template-columns: 1fr;
}

@media (min-width: 1024px) {
  .booking-grid {
    grid-template-columns: 400px 1fr;
  }
}
</style>
