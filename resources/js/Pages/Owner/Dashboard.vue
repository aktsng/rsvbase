<template>
  <OwnerLayout>
    <Head title="ダッシュボード" />
    <template #header>オーナーダッシュボード</template>

    <div class="mb-6 space-y-4">
      <!-- 予約サイトURL -->
      <div v-if="facility.is_published" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-primary-50 rounded-lg text-primary-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.826a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
          </div>
          <div>
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest">予約サイトURL</h4>
            <p class="text-sm font-medium text-slate-600 break-all select-all">{{ facility.public_url }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <a :href="facility.public_url" target="_blank" class="px-4 py-2 bg-slate-50 text-slate-600 text-sm font-bold rounded-xl hover:bg-slate-100 transition whitespace-nowrap">
            サイトを確認
          </a>
          <button @click="copyUrl" class="px-4 py-2 bg-primary-600 text-white text-sm font-bold rounded-xl hover:bg-primary-700 transition whitespace-nowrap flex items-center gap-2">
            <span v-if="copied">コピーしました！</span>
            <span v-else>URLをコピー</span>
          </button>
        </div>
      </div>

      <div v-if="!facility.is_published" class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 flex gap-4">
        <svg class="w-6 h-6 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
          <h4 class="font-bold">施設は非公開状態です</h4>
          <p class="text-sm mt-1">ゲストからの予約を受け付けるには、施設設定から公開状態に変更してください。</p>
          <div class="mt-3">
            <Link :href="route('owner.facility.edit')" class="text-sm font-semibold text-amber-700 hover:text-amber-900 underline">
              施設設定を開く
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- ダッシュボード掲載お知らせ -->
    <div v-if="announcements && announcements.length > 0" class="mb-6 space-y-3">
      <div v-for="a in announcements" :key="a.id"
           class="rounded-2xl p-4 shadow-sm border flex items-start gap-4"
           :class="{
             'bg-blue-50 border-blue-100': a.category_color === 'blue',
             'bg-amber-50 border-amber-100': a.category_color === 'amber',
             'bg-emerald-50 border-emerald-100': a.category_color === 'emerald',
             'bg-red-50 border-red-100': a.category_color === 'red',
             'bg-slate-50 border-slate-100': !['blue','amber','emerald','red'].includes(a.category_color),
           }">
        <div class="p-2 rounded-lg shrink-0"
             :class="{
               'bg-blue-100 text-blue-600': a.category_color === 'blue',
               'bg-amber-100 text-amber-600': a.category_color === 'amber',
               'bg-emerald-100 text-emerald-600': a.category_color === 'emerald',
               'bg-red-100 text-red-600': a.category_color === 'red',
               'bg-slate-100 text-slate-500': !['blue','amber','emerald','red'].includes(a.category_color),
             }">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold px-2 py-0.5 rounded-full border"
                  :class="{
                    'bg-blue-100 text-blue-700 border-blue-200': a.category_color === 'blue',
                    'bg-amber-100 text-amber-700 border-amber-200': a.category_color === 'amber',
                    'bg-emerald-100 text-emerald-700 border-emerald-200': a.category_color === 'emerald',
                    'bg-red-100 text-red-700 border-red-200': a.category_color === 'red',
                    'bg-slate-100 text-slate-600 border-slate-200': !['blue','amber','emerald','red'].includes(a.category_color),
                  }">{{ a.category_label }}</span>
            <span class="text-xs text-slate-400">{{ a.published_at }}</span>
          </div>
          <h4 class="font-bold text-sm text-slate-800">{{ a.title }}</h4>
          <p class="text-xs text-slate-600 mt-1 line-clamp-2 whitespace-pre-line">{{ a.body }}</p>
        </div>
      </div>
      <div class="text-right">
        <Link :href="route('owner.announcements.index')" class="text-xs font-medium text-primary-600 hover:text-primary-700">
          すべてのお知らせを見る &rarr;
        </Link>
      </div>
    </div>

    <!-- 統計カード群 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">今月の売上 ({{ formatMonth(calendar.currentMonth) }})</h3>
        <p class="text-3xl font-bold text-slate-800">¥{{ stats.total_revenue.toLocaleString() }}</p>
      </div>
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <h3 class="text-slate-500 text-sm font-medium mb-1">今月の予約件数 ({{ formatMonth(calendar.currentMonth) }})</h3>
        <p class="text-3xl font-bold text-slate-800">{{ stats.total_reservations }} <span class="text-slate-400 text-lg font-normal">件</span></p>
      </div>
    </div>

    <!-- 予約状況カレンダー -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-8">
      <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">予約状況カレンダー</h2>
        <div class="flex items-center gap-4">
          <div class="flex items-center bg-slate-100 rounded-xl p-1">
            <Link :href="route('owner.dashboard', { month: calendar.prevMonth })" 
                  preserve-scroll
                  class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </Link>
            <span class="px-4 text-sm font-bold text-slate-700 min-w-[100px] text-center">
              {{ formatMonth(calendar.currentMonth) }}
            </span>
            <Link :href="route('owner.dashboard', { month: calendar.nextMonth })"
                  preserve-scroll
                  class="p-2 hover:bg-white hover:shadow-sm rounded-lg transition text-slate-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </Link>
          </div>
          <Link :href="route('owner.dashboard')" preserve-scroll class="text-xs font-bold text-primary-600 hover:text-primary-700">
            当月に戻る
          </Link>
        </div>
      </div>
      
      <div class="overflow-x-auto p-6">
        <table class="w-full border-collapse border border-slate-100">
          <thead>
            <tr>
              <th class="sticky left-0 z-10 bg-slate-50 border border-slate-100 px-4 py-2 text-left text-xs font-bold text-slate-500 w-32 min-w-[128px] shrink-0">
                部屋名
              </th>
              <th v-for="day in calendar.days" :key="day.date" 
                  :class="[
                    'border border-slate-100 px-1 py-2 text-center text-xs font-bold min-w-[36px]',
                    day.is_weekend ? 'bg-slate-50' : 'bg-white',
                    day.is_today ? 'bg-primary-50 ring-1 ring-inset ring-primary-200' : ''
                  ]">
                <div :class="[
                  day.is_sunday ? 'text-red-600' : (day.is_saturday ? 'text-blue-600' : 'text-slate-700')
                ]">{{ day.day }}</div>
                <div :class="[
                  day.is_sunday ? 'text-red-400' : (day.is_saturday ? 'text-blue-400' : 'text-slate-400'),
                  'text-[9px] font-medium leading-none mt-0.5'
                ]">{{ day.day_label }}</div>
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="room in calendar.rooms" :key="room.uuid">
              <td class="sticky left-0 z-10 bg-white border border-slate-100 px-4 py-3 text-sm font-bold text-slate-700 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] w-32 min-w-[128px] break-all">
                {{ room.name }}
              </td>
              <td v-for="day in calendar.days" :key="day.date" 
                  :class="[
                    'border border-slate-100 p-0 text-center h-12 relative',
                    day.is_weekend ? 'bg-slate-50/50' : 'bg-white'
                  ]">
                <!-- 予約あり (プログレスインジケーター風) -->
                <Link v-if="room.days[day.date] && room.days[day.date].type === 'reserved'" 
                      :href="route('owner.reservations.show', room.days[day.date].uuid)"
                      class="flex items-center justify-center w-full h-full hover:bg-emerald-50/50 transition relative group"
                      :title="`${room.days[day.date].guest_name}（${room.days[day.date].nights}泊）` ">
                  
                  <!-- 背景の接続線 -->
                  <div v-if="room.days[day.date].position !== 'single'" 
                       class="absolute top-1/2 -translate-y-1/2 h-0.5 bg-emerald-200"
                       :class="[
                         room.days[day.date].position === 'start' ? 'left-1/2 right-0' : '',
                         room.days[day.date].position === 'middle' ? 'left-0 right-0' : '',
                         room.days[day.date].position === 'end' ? 'left-0 right-1/2' : '',
                       ]">
                  </div>

                  <!-- インジケーターの円 -->
                  <div class="relative z-10 w-6 h-6 rounded-full flex items-center justify-center shadow-sm transition transform group-hover:scale-110"
                       :class="room.days[day.date].day_number === 1 ? 'bg-emerald-500 text-white' : 'bg-white border-2 border-emerald-500 text-emerald-600 font-bold'">
                    <svg v-if="room.days[day.date].day_number === 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                    <span v-else class="text-[10px]">{{ room.days[day.date].day_number }}</span>
                  </div>
                </Link>
                <!-- 部屋ブロック中 -->
                <div v-else-if="room.days[day.date] && room.days[day.date].type === 'blocked'" 
                     class="flex items-center justify-center w-full h-full bg-slate-100/50 text-slate-300"
                     title="部屋ブロック中">
                  <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9" />
                    <line x1="18.36" y1="5.64" x2="5.64" y2="18.36" />
                  </svg>
                </div>
                <!-- 空室 -->
                <button v-else 
                        @click="openManualReservationModal(room, day.date)"
                        class="flex items-center justify-center w-full h-full text-slate-200 font-bold hover:bg-primary-50 hover:text-primary-400 transition group">
                  <span class="text-xl">◯</span>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-4 text-xs text-slate-500">
        <div class="flex items-center gap-1.5">
          <div class="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <span>予約あり</span>
        </div>
        <div class="flex items-center gap-1.5">
          <div class="flex items-center">
            <div class="w-4 h-4 rounded-full border border-emerald-500 bg-white flex items-center justify-center text-[8px] text-emerald-600 font-bold">2</div>
            <div class="w-3 h-0.5 bg-emerald-200"></div>
            <div class="w-4 h-4 rounded-full border border-emerald-500 bg-white flex items-center justify-center text-[8px] text-emerald-600 font-bold">3</div>
          </div>
          <span>連泊（2日目〜）</span>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="font-bold text-slate-200 text-sm">◯</span>
          <span>空き（クリックで手動予約）</span>
        </div>
        <div class="flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="9" />
            <line x1="18.36" y1="5.64" x2="5.64" y2="18.36" />
          </svg>
          <span>ブロック中</span>
        </div>
        <div class="flex items-center gap-1.5 ml-auto">
          <span class="inline-block w-3 h-3 bg-primary-50 border border-primary-200 rounded-sm"></span>
          <span>本日</span>
        </div>
      </div>
    </div>

    <!-- 最近の予約 -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
        <h2 class="text-lg font-bold text-slate-800">最近の予約</h2>
        <Link :href="route('owner.reservations.index')" class="text-sm font-medium text-primary-600 hover:text-primary-700">
          すべて見る &rarr;
        </Link>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
          <thead class="bg-slate-50 text-slate-500 font-medium">
            <tr>
              <th class="px-6 py-3">予約番号</th>
              <th class="px-6 py-3">ゲスト名</th>
              <th class="px-6 py-3">部屋</th>
              <th class="px-6 py-3">チェックイン</th>
              <th class="px-6 py-3 text-right">料金</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="reservation in recentReservations" :key="reservation.uuid" class="hover:bg-slate-50 transition">
              <td class="px-6 py-4 font-mono text-xs text-slate-500">
                <Link :href="route('owner.reservations.show', reservation.uuid)" class="hover:underline">
                  {{ reservation.reservation_code }}
                </Link>
              </td>
              <td class="px-6 py-4 font-medium text-slate-800">{{ reservation.guest_name }}</td>
              <td class="px-6 py-4">{{ reservation.room_name }}</td>
              <td class="px-6 py-4">{{ reservation.check_in_date }}</td>
              <td class="px-6 py-4 text-right font-medium text-slate-800">¥{{ reservation.total_amount.toLocaleString() }}</td>
            </tr>
            <tr v-if="recentReservations.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-slate-500">
                最近の予約はありません。
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- 手動予約登録モーダル -->
    <div v-if="showRegisterModal" class="relative z-50">
      <div class="fixed inset-0 bg-slate-900 bg-opacity-50 backdrop-blur-sm transition-opacity" @click="closeRegisterModal"></div>
      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-100">
            <div class="bg-white px-8 pt-8 pb-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ registerStep === 'input' ? '手動予約の登録' : '予約内容の確認' }}
                </h3>
                <button @click="closeRegisterModal" class="text-slate-400 hover:text-slate-600 transition p-2 hover:bg-slate-100 rounded-full">
                  <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
              </div>

              <!-- ステップ1: 入力画面 -->
              <form v-if="registerStep === 'input'" @submit.prevent="goToConfirm" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">宿泊する部屋 *</label>
                    <select v-model="registerForm.room_id" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.room_id}">
                      <option value="" disabled>部屋を選択してください</option>
                      <option v-for="room in props.rooms" :key="room.uuid" :value="room.id">{{ room.name }} (定員:{{ room.capacity }}名)</option>
                    </select>
                    <p v-if="registerForm.errors.room_id" class="mt-1 text-xs text-red-500">{{ registerForm.errors.room_id }}</p>
                  </div>
                  <div class="sm:col-span-2">
                    <DateRangePicker
                      v-model="dateRange"
                      :check-in-label="t('check_in')"
                      :check-out-label="t('check_out')"
                      :nights-label="isEn ? 'night(s)' : '泊'"
                      :availability="selectedRoomDays"
                    />
                    <div v-if="registerForm.errors.check_in_date || registerForm.errors.check_out_date" class="mt-2 space-y-1">
                      <p v-if="registerForm.errors.check_in_date" class="text-xs text-red-500">{{ registerForm.errors.check_in_date }}</p>
                      <p v-if="registerForm.errors.check_out_date" class="text-xs text-red-500">{{ registerForm.errors.check_out_date }}</p>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">大人 *</label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_adults = Math.max(1, registerForm.number_of_adults - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_adults <= 1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_adults }}</span>
                      <button type="button" @click="registerForm.number_of_adults = Math.min(10, registerForm.number_of_adults + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                    <p v-if="registerForm.errors.number_of_guests" class="mt-1 text-xs text-red-500">{{ registerForm.errors.number_of_guests }}</p>
                  </div>
                  <!-- 子供Aカウンター -->
                  <div v-if="selectedRoomForRegister?.pricing_type === 'person' && selectedRoomForRegister?.child_a_label">
                    <label class="block text-xs font-bold text-slate-500 mb-2">
                      {{ selectedRoomForRegister.child_a_label }}
                      <span v-if="selectedRoomForRegister.child_a_policy" class="text-xs text-slate-400 font-normal ml-1">{{ selectedRoomForRegister.child_a_policy }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_child_a = Math.max(0, registerForm.number_of_child_a - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_child_a <= 0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_child_a }}</span>
                      <button type="button" @click="registerForm.number_of_child_a = Math.min(10, registerForm.number_of_child_a + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                  </div>
                  <!-- 子供Bカウンター -->
                  <div v-if="selectedRoomForRegister?.pricing_type === 'person' && selectedRoomForRegister?.child_b_label">
                    <label class="block text-xs font-bold text-slate-500 mb-2">
                      {{ selectedRoomForRegister.child_b_label }}
                      <span v-if="selectedRoomForRegister.child_b_policy" class="text-xs text-slate-400 font-normal ml-1">{{ selectedRoomForRegister.child_b_policy }}</span>
                    </label>
                    <div class="flex items-center gap-3">
                      <button type="button" @click="registerForm.number_of_child_b = Math.max(0, registerForm.number_of_child_b - 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition disabled:opacity-30"
                              :disabled="registerForm.number_of_child_b <= 0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                      </button>
                      <span class="w-8 text-center font-bold text-slate-800">{{ registerForm.number_of_child_b }}</span>
                      <button type="button" @click="registerForm.number_of_child_b = Math.min(10, registerForm.number_of_child_b + 1)"
                              class="w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                      </button>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">ゲスト名 *</label>
                    <input type="text" v-model="registerForm.guest_name" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" />
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">メール *</label>
                    <input type="email" v-model="registerForm.guest_email" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.guest_email}" />
                    <p v-if="registerForm.errors.guest_email" class="mt-1 text-xs text-red-500">{{ registerForm.errors.guest_email }}</p>
                  </div>
                  <div>
                    <label class="block text-xs font-bold text-slate-500 mb-2">電話番号 *</label>
                    <input type="tel" v-model="registerForm.guest_phone" required class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" :class="{'border-red-500': registerForm.errors.guest_phone}" />
                    <p v-if="registerForm.errors.guest_phone" class="mt-1 text-xs text-red-500">{{ registerForm.errors.guest_phone }}</p>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-500 mb-2">ゲスト備考（お客様へのメッセージ／メールに表示されます）</label>
                    <textarea v-model="registerForm.guest_remarks" rows="2" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                  <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-primary-600 mb-2">オーナーメモ (スタッフ用)</label>
                    <textarea v-model="registerForm.owner_memo" rows="2" class="block w-full px-4 py-2 border border-primary-100 rounded-xl leading-5 bg-primary-50/10 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col gap-3">
                  <div v-if="registerForm.errors.length > 0 || $page.props.flash?.error" class="bg-red-50 p-4 rounded-2xl border border-red-100 flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-xs text-red-700 leading-normal">{{ $page.props.flash.error || '入力内容を確認してください' }}</p>
                  </div>

                  <button type="submit" :disabled="calculating"
                          class="w-full py-4 bg-primary-600 text-white font-black rounded-2xl hover:bg-primary-700 transition shadow-xl shadow-primary-200 disabled:opacity-50">
                    <span v-if="calculating">料金計算中...</span>
                    <span v-else>確認画面へ</span>
                  </button>
                </div>
              </form>

              <!-- ステップ2: 確認画面 -->
              <div v-else class="space-y-6">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 space-y-4">
                    <div class="flex justify-between items-start border-b border-slate-200 pb-4">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">宿泊期間</p>
                            <p class="font-bold text-slate-800">{{ registerForm.check_in_date }} 〜 {{ registerForm.check_out_date }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">人数</p>
                            <p class="font-bold text-slate-800">
                              大人 {{ registerForm.number_of_adults }}名
                              <template v-if="registerForm.number_of_child_a > 0"> / {{ selectedRoomForRegister?.child_a_label || '子供A' }} {{ registerForm.number_of_child_a }}名</template>
                              <template v-if="registerForm.number_of_child_b > 0"> / {{ selectedRoomForRegister?.child_b_label || '子供B' }} {{ registerForm.number_of_child_b }}名</template>
                            </p>
                        </div>
                    </div>

                    <div v-if="calculatedPrice" class="space-y-2">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">料金内訳</p>
                        <div v-for="day in calculatedPrice.daily_prices" :key="day.date" class="text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-600">{{ day.date }} ({{ day.label }})</span>
                                <span class="font-mono text-slate-800">¥{{ (day.dayTotal || day.price).toLocaleString() }}</span>
                            </div>
                            <div v-if="calculatedPrice.pricing_type === 'person'" class="ml-4 text-xs text-slate-400 space-y-0.5 mt-0.5">
                                <div class="flex justify-between">
                                    <span>大人 {{ calculatedPrice.adults }}名 × ¥{{ day.price.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.adults * day.price).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_a > 0 && calculatedPrice.add_child_a_fee > 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_a_label || '子供A' }} {{ calculatedPrice.child_a }}名 × ¥{{ calculatedPrice.add_child_a_fee.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.child_a * calculatedPrice.add_child_a_fee).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_a > 0 && (calculatedPrice.add_child_a_fee || 0) === 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_a_label || '子供A' }} {{ calculatedPrice.child_a }}名</span>
                                    <span class="font-mono text-emerald-500">無料</span>
                                </div>
                                <div v-if="calculatedPrice.child_b > 0 && calculatedPrice.add_child_b_fee > 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_b_label || '子供B' }} {{ calculatedPrice.child_b }}名 × ¥{{ calculatedPrice.add_child_b_fee.toLocaleString() }}</span>
                                    <span class="font-mono">¥{{ (calculatedPrice.child_b * calculatedPrice.add_child_b_fee).toLocaleString() }}</span>
                                </div>
                                <div v-if="calculatedPrice.child_b > 0 && (calculatedPrice.add_child_b_fee || 0) === 0" class="flex justify-between">
                                    <span>{{ calculatedPrice.child_b_label || '子供B' }} {{ calculatedPrice.child_b }}名</span>
                                    <span class="font-mono text-emerald-500">無料</span>
                                </div>
                            </div>
                        </div>
                        <div v-if="calculatedPrice.cleaning_fee > 0" class="flex justify-between text-sm pt-2 border-t border-slate-100">
                            <span class="text-slate-600">清掃費</span>
                            <span class="font-mono text-slate-800">¥{{ calculatedPrice.cleaning_fee.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-slate-200">
                            <span class="font-black text-slate-900">合計金額</span>
                            <span class="text-2xl font-black text-primary-600 font-mono">¥{{ calculatedPrice.total_amount.toLocaleString() }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                  <button @click="registerStep = 'input'" 
                          class="py-4 bg-white text-slate-600 font-bold rounded-2xl border border-slate-200 hover:bg-slate-50 transition">
                    戻って修正
                  </button>
                  <button @click="submitRegister" :disabled="registerForm.processing"
                          class="py-4 bg-primary-600 text-white font-black rounded-2xl hover:bg-primary-700 transition shadow-xl shadow-primary-200 disabled:opacity-50">
                    <span v-if="registerForm.processing">登録中...</span>
                    <span v-else>確定して登録する</span>
                  </button>
                </div>

                <div v-if="$page.props.auth.is_stripe_connected" class="bg-emerald-50 p-4 rounded-2xl border border-emerald-100 text-[11px] text-emerald-700 leading-normal text-center">
                    現地決済として登録されます。予約確定メールがゲストに送信されます。
                </div>
                <div v-else class="bg-amber-50 p-4 rounded-2xl border border-amber-100 text-[11px] text-amber-700 leading-normal text-center">
                    現地決済として登録されます。プレビューモード中のため、メールは送信されません。
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import DateRangePicker from '@/Components/DateRangePicker.vue';
import { useI18n } from '@/composables/useI18n.js';

const props = defineProps({
    facility: Object,
    rooms: Array,
    stats: Object,
    recentReservations: Array,
    calendar: Object,
    announcements: { type: Array, default: () => [] },
});

const formatMonth = (monthStr) => {
    if (!monthStr) return '';
    const [year, month] = monthStr.split('-');
    return `${year}年${parseInt(month)}月`;
};

const copied = ref(false);
const copyUrl = () => {
    navigator.clipboard.writeText(props.facility.public_url).then(() => {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 3000);
    });
};

// --- 新規予約（手動）関連のロジック ---
const showRegisterModal = ref(false);
const registerForm = useForm({
    room_id: '',
    check_in_date: '',
    check_out_date: '',
    number_of_guests: 1,
    number_of_adults: 1,
    number_of_child_a: 0,
    number_of_child_b: 0,
    guest_name: '',
    guest_email: '',
    guest_phone: '',
    guest_remarks: '',
    owner_memo: '',
});

const { t, isEn } = useI18n();

const dateRange = ref({
    checkIn: registerForm.check_in_date,
    checkOut: registerForm.check_out_date,
});

// dateRange が変わったら registerForm に反映
watch(dateRange, (newVal) => {
    if (newVal.checkIn) registerForm.check_in_date = newVal.checkIn;
    if (newVal.checkOut) registerForm.check_out_date = newVal.checkOut;
}, { deep: true });

// モーダルが開いたときや初期化時に dateRange を同期
watch(() => showRegisterModal.value, (newVal) => {
    if (newVal) {
        dateRange.value = {
            checkIn: registerForm.check_in_date,
            checkOut: registerForm.check_out_date,
        };
    }
});

// 選択された部屋の情報
const selectedRoomForRegister = computed(() => {
    if (!registerForm.room_id) return null;
    return props.rooms?.find(r => r.id === registerForm.room_id) || null;
});

const selectedRoomDays = computed(() => {
    if (!registerForm.room_id) return {};
    return props.calendar.rooms?.find(r => r.id === registerForm.room_id)?.days || {};
});

// 人数内訳が変更されたら number_of_guests を自動計算
watch([() => registerForm.number_of_adults, () => registerForm.number_of_child_a, () => registerForm.number_of_child_b], () => {
    registerForm.number_of_guests = registerForm.number_of_adults + registerForm.number_of_child_a + registerForm.number_of_child_b;
});

const registerStep = ref('input');
const calculatedPrice = ref(null);
const calculating = ref(false);

const openManualReservationModal = (room, date) => {
    registerForm.reset();
    registerForm.room_id = room.id;
    registerForm.check_in_date = date;
    
    // チェックアウト日は自動的に翌日にセット
    const checkIn = new Date(date);
    const nextDay = new Date(checkIn);
    nextDay.setDate(nextDay.getDate() + 1);
    registerForm.check_out_date = nextDay.toISOString().split('T')[0];
    
    showRegisterModal.value = true;
};

// handleCheckInChange function removed as it's replaced by DateRangePicker logic

const goToConfirm = async () => {
    calculating.value = true;
    try {
        const response = await axios.post(route('owner.reservations.calculate-price'), {
            room_id: registerForm.room_id,
            check_in_date: registerForm.check_in_date,
            check_out_date: registerForm.check_out_date,
            number_of_guests: registerForm.number_of_guests,
            number_of_adults: registerForm.number_of_adults,
            number_of_child_a: registerForm.number_of_child_a,
            number_of_child_b: registerForm.number_of_child_b,
        });
        calculatedPrice.value = response.data;
        registerStep.value = 'confirm';
        registerForm.clearErrors();
    } catch (error) {
        console.error('Price calculation failed:', error);
        if (error.response?.data?.errors) {
            registerForm.setError(error.response.data.errors);
        } else if (error.response?.data?.error) {
            alert(error.response.data.error);
        }
    } finally {
        calculating.value = false;
    }
};

const submitRegister = () => {
    registerForm.post(route('owner.reservations.store'), {
        onSuccess: () => {
            showRegisterModal.value = false;
            registerForm.reset();
            registerStep.value = 'input';
            calculatedPrice.value = null;
        },
        onError: () => {
            registerStep.value = 'input';
        }
    });
};

const closeRegisterModal = () => {
    showRegisterModal.value = false;
    setTimeout(() => {
        registerForm.reset();
        registerStep.value = 'input';
        calculatedPrice.value = null;
    }, 300);
};
</script>
