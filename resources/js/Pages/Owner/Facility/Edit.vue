<template>
  <OwnerLayout>
    <Head title="施設設定" />
    <template #header>施設設定</template>

    <div class="max-w-4xl mx-auto py-6">
      

      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form @submit.prevent="submit" class="space-y-6">
          
          <!-- 公開状態トグル -->
          <div class="pb-6 border-b border-slate-100 flex items-center justify-between">
            <div>
              <h3 class="text-lg font-bold text-slate-800">公開状態</h3>
              <p class="text-sm text-slate-500 mt-1">公開すると、ゲストからの予約が受け付けられるようになります。</p>
            </div>
            <div class="flex flex-col items-end">
              <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" v-model="form.is_published" class="sr-only peer">
                <div class="w-14 h-7 bg-slate-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600"></div>
                <span class="ml-3 text-sm font-medium text-slate-700">{{ form.is_published ? '公開中' : '非公開' }}</span>
              </label>
              <p class="text-[10px] text-amber-600 font-bold mt-2">※トグルの変更を反映させるには、画面下部の「設定を保存」ボタンを必ず押してください。</p>
            </div>
          </div>

          <!-- 予約ページURL（Slug）設定 -->
          <div class="pb-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800">予約ページURL設定</h3>
            <p class="text-sm text-slate-500 mt-1">予約ページのURLを自由に変更できます（例: abc-hotel）。一度設定すると<strong>二度と変更できません</strong>のでご注意ください。</p>
            
            <div class="mt-4 max-w-lg">
              <!-- 現在のURL (UUID) -->
              <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 mb-3">
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">現在のURL</p>
                <code class="text-sm font-mono text-slate-600 bg-white px-2 py-1 rounded border border-slate-200 block break-all select-all">{{ route('guest.booking.show', props.facility.slug || props.facility.uuid) }}</code>
              </div>

              <div v-if="props.facility.slug" class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  <span class="text-sm font-bold text-emerald-700">カスタムURL設定済み</span>
                  <span class="text-[10px] bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full font-bold">変更不可</span>
                </div>
              </div>
              <div v-else class="space-y-2">
                <p class="text-xs font-bold text-slate-600">カスタムURLを設定する（任意）</p>
                <div class="flex items-center gap-2">
                  <span class="text-slate-400 font-mono text-sm leading-none pt-2">/b/</span>
                  <div class="flex-1">
                    <input v-model="form.slug" type="text" placeholder="your-facility-name"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition font-mono">
                  </div>
                </div>
                <p class="text-[10px] text-slate-500">※英数字とハイフンのみ使用可能です。設定後は従来の長いID（UUID）でもアクセス可能です。</p>
                <p v-if="form.errors.slug" class="mt-1 text-sm text-red-500">{{ form.errors.slug }}</p>
              </div>
            </div>
          </div>
          
          <!-- バリデーションエラーのサマリー（スマホ視認性向上） -->
          <div v-if="form.hasErrors" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-pulse">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm font-bold text-red-700">施設を公開するには必須項目を全て入力してください。</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
            <!-- 施設画像 -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-3">施設画像（最大3枚）</label>
              
              <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- 画像1 -->
                <div class="space-y-2">
                  <div class="relative group aspect-video bg-slate-100 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center">
                    <img v-if="previews.image || props.facility.image_url" 
                         :src="previews.image || props.facility.image_url" 
                         class="w-full h-full object-cover" />
                    <svg v-else class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <label class="absolute inset-0 flex items-center justify-center bg-slate-900/40 opacity-0 group-hover:opacity-100 transition rounded-2xl cursor-pointer">
                      <span class="text-white text-xs font-bold">画像を変更</span>
                      <input type="file" @change="(e) => handleImageChange(e, 'image')" class="sr-only" accept="image/*">
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">メイン画像</span>
                    <button v-if="form.image" type="button" @click="clearImage('image')" class="text-xs text-red-500 hover:text-red-700 font-bold">クリア</button>
                  </div>
                  <p v-if="form.errors.image" class="text-[10px] text-red-500">{{ form.errors.image }}</p>
                </div>

                <!-- 画像2 -->
                <div class="space-y-2">
                  <div class="relative group aspect-video bg-slate-100 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center">
                    <img v-if="previews.image_2 || props.facility.image_url_2" 
                         :src="previews.image_2 || props.facility.image_url_2" 
                         class="w-full h-full object-cover" />
                    <svg v-else class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <label class="absolute inset-0 flex items-center justify-center bg-slate-900/40 opacity-0 group-hover:opacity-100 transition rounded-2xl cursor-pointer">
                      <span class="text-white text-xs font-bold">画像を追加</span>
                      <input type="file" @change="(e) => handleImageChange(e, 'image_2')" class="sr-only" accept="image/*">
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">サブ画像1</span>
                    <button v-if="form.image_2" type="button" @click="clearImage('image_2')" class="text-xs text-red-500 hover:text-red-700 font-bold">クリア</button>
                  </div>
                  <p v-if="form.errors.image_2" class="text-[10px] text-red-500">{{ form.errors.image_2 }}</p>
                </div>

                <!-- 画像3 -->
                <div class="space-y-2">
                  <div class="relative group aspect-video bg-slate-100 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center">
                    <img v-if="previews.image_3 || props.facility.image_url_3" 
                         :src="previews.image_3 || props.facility.image_url_3" 
                         class="w-full h-full object-cover" />
                    <svg v-else class="w-10 h-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <label class="absolute inset-0 flex items-center justify-center bg-slate-900/40 opacity-0 group-hover:opacity-100 transition rounded-2xl cursor-pointer">
                      <span class="text-white text-xs font-bold">画像を追加</span>
                      <input type="file" @change="(e) => handleImageChange(e, 'image_3')" class="sr-only" accept="image/*">
                    </label>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">サブ画像2</span>
                    <button v-if="form.image_3" type="button" @click="clearImage('image_3')" class="text-xs text-red-500 hover:text-red-700 font-bold">クリア</button>
                  </div>
                  <p v-if="form.errors.image_3" class="text-[10px] text-red-500">{{ form.errors.image_3 }}</p>
                </div>
              </div>

              <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-xs text-slate-500 leading-relaxed">
                  予約ページの上部に表示される画像です。最大3枚まで登録可能です。<br>
                  推奨サイズ: 1200x600px 以上 (2:1の比率) / 最大ファイルサイズ: 5MB
                </p>
              </div>
            </div>

            <!-- 施設名 -->
            <div class="md:col-span-2">
              <label for="name" class="block text-sm font-medium text-slate-700 mb-1">施設名 <span class="text-red-500">*</span></label>
                  <input id="name" v-model="form.name" type="text" required
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition font-bold">
                  <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                  <div class="mt-2">
                    <label for="name_en" class="block text-xs font-medium text-slate-400 mb-1">🌎 Facility Name (English・任意)</label>
                    <input id="name_en" v-model="form.name_en" type="text" placeholder="e.g. Sakura Guest House"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                </div>
    
                <!-- 住所 -->
                <div class="md:col-span-2">
                  <label for="address" class="block text-sm font-medium text-slate-700 mb-1">住所 <span class="text-red-500">*</span></label>
                  <input id="address" v-model="form.address" type="text" required
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  <p v-if="form.errors.address" class="mt-1 text-sm text-red-500">{{ form.errors.address }}</p>
                  <div class="mt-2">
                    <label for="address_en" class="block text-xs font-medium text-slate-400 mb-1">🌎 Address (English・任意)</label>
                    <input id="address_en" v-model="form.address_en" type="text" placeholder="e.g. 1-2-3 Shibuya, Shibuya-ku, Tokyo"
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                </div>
    
                <!-- 電話番号 -->
                <div>
                  <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">電話番号 <span class="text-red-500">*</span></label>
                  <input id="phone" v-model="form.phone" type="text" required
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">{{ form.errors.phone }}</p>
                </div>
    
                <!-- 予約締切 -->
                <div>
                  <label for="booking_cutoff_hours" class="block text-sm font-medium text-slate-700 mb-1">予約受付の締切 <span class="text-red-500">*</span></label>
                  <select id="booking_cutoff_hours" v-model="form.booking_cutoff_hours" required
                          class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                    <option :value="0">当日まで（締切なし）</option>
                    <option :value="24">チェックイン前日</option>
                    <option :value="48">チェックイン2日前</option>
                    <option :value="72">チェックイン3日前</option>
                  </select>
                  <p v-if="form.errors.booking_cutoff_hours" class="mt-1 text-sm text-red-500">{{ form.errors.booking_cutoff_hours }}</p>
                </div>
    
                <!-- チェックイン開始・終了 -->
                <div>
                  <label for="check_in_time_start" class="block text-sm font-medium text-slate-700 mb-1">チェックイン時間（開始〜終了） <span class="text-red-500">*</span></label>
                  <div class="flex items-center gap-2">
                    <input id="check_in_time_start" v-model="form.check_in_time_start" type="time" required
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                    <span class="text-slate-500">〜</span>
                    <input id="check_in_time_end" v-model="form.check_in_time_end" type="time" required
                           class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  </div>
                  <p v-if="form.errors.check_in_time_start" class="mt-1 text-sm text-red-500">{{ form.errors.check_in_time_start }}</p>
                  <p v-if="form.errors.check_in_time_end" class="mt-1 text-sm text-red-500">{{ form.errors.check_in_time_end }}</p>
                </div>
    
                <!-- チェックアウト時間 -->
                <div>
                  <label for="check_out_time" class="block text-sm font-medium text-slate-700 mb-1">チェックアウト時間 <span class="text-red-500">*</span></label>
                  <input id="check_out_time" v-model="form.check_out_time" type="time" required
                         class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                  <p v-if="form.errors.check_out_time" class="mt-1 text-sm text-red-500">{{ form.errors.check_out_time }}</p>
                </div>
    
                <!-- 施設説明 -->
                <div class="md:col-span-2">
                  <label for="description" class="block text-sm font-medium text-slate-700 mb-1">施設の説明</label>
                  <textarea id="description" v-model="form.description" rows="4"
                            class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                  <div class="mt-2">
                    <label for="description_en" class="block text-xs font-medium text-slate-400 mb-1">🌎 Description (English・任意)</label>
                    <textarea id="description_en" v-model="form.description_en" rows="3" placeholder="e.g. A cozy guest house in central Tokyo..."
                              class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  </div>
                </div>
                
                <!-- キャンセルポリシー -->
                <div class="md:col-span-2">
                  <div class="flex items-center justify-between mb-1">
                    <label for="cancellation_policy" class="block text-sm font-medium text-slate-700">キャンセルポリシー <span class="text-red-500 font-bold">*</span></label>
                    <button type="button" @click="applyCancellationTemplate" class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-800 hover:bg-primary-50 px-2 py-1 rounded-lg transition group">
                      <svg class="w-3.5 h-3.5 transition group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      テンプレートをセット
                    </button>
                  </div>
                  <textarea id="cancellation_policy" v-model="form.cancellation_policy" rows="5"
                            placeholder="例：宿泊日の3日前からキャンセル料100%が発生します。"
                            class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  <p v-if="form.errors.cancellation_policy" class="mt-1 text-sm text-red-500">{{ form.errors.cancellation_policy }}</p>
                </div>
    
                <!-- 宿泊約款 -->
                <div class="md:col-span-2">
                  <div class="flex items-center justify-between mb-1">
                    <label for="terms_text" class="block text-sm font-medium text-slate-700">宿泊約款 <span class="text-red-500 font-bold">*</span></label>
                    <button type="button" @click="applyTermsTemplate" class="inline-flex items-center gap-1 text-xs font-bold text-primary-600 hover:text-primary-800 hover:bg-primary-50 px-2 py-1 rounded-lg transition group">
                      <svg class="w-3.5 h-3.5 transition group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      テンプレートをセット
                    </button>
                  </div>
                  <textarea id="terms_text" v-model="form.terms_text" rows="6"
                            placeholder="宿泊約款を入力してください。"
                            class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
                  <p v-if="form.errors.terms_text" class="mt-1 text-sm text-red-500">{{ form.errors.terms_text }}</p>
                </div>
    
                <!-- 特定商取引法に基づく表記 (施設用) -->
                <div class="md:col-span-2 pt-8 border-t border-slate-100">
                  <div class="mb-6">
                    <h3 class="text-lg font-bold text-slate-800">特定商取引法に基づく表記（施設用）</h3>
                    <p class="text-sm text-slate-500 mt-1">
                      宿泊契約における「販売業者」としての情報を入力してください。この内容は各施設の法的表記ページに表示されます。
                      <span class="text-primary-600 font-medium font-bold block mt-1">※施設の「公開」を有効にする場合は、*印の項目が必須です。</span>
                    </p>
                  </div>
    
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label for="scta_business_name" class="block text-sm font-medium text-slate-700 mb-1">運営事業者名 <span class="text-red-500 font-bold">*</span></label>
                      <input id="scta_business_name" v-model="form.scta_business_name" type="text" placeholder="例：株式会社〇〇、または個人名"
                             class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <p v-if="form.errors.scta_business_name" class="mt-1 text-sm text-red-500">{{ form.errors.scta_business_name }}</p>
                    </div>
    
                    <div>
                      <label for="scta_representative" class="block text-sm font-medium text-slate-700 mb-1">運営責任者名 <span class="text-red-500 font-bold">*</span></label>
                      <input id="scta_representative" v-model="form.scta_representative" type="text" placeholder="例：山田 太郎"
                             class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <p v-if="form.errors.scta_representative" class="mt-1 text-sm text-red-500">{{ form.errors.scta_representative }}</p>
                    </div>
    
                    <div class="md:col-span-2">
                      <label for="scta_address" class="block text-sm font-medium text-slate-700 mb-1">所在地 <span class="text-red-500 font-bold">*</span></label>
                      <input id="scta_address" v-model="form.scta_address" type="text" placeholder="例：東京都渋谷区..."
                             class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <p v-if="form.errors.scta_address" class="mt-1 text-sm text-red-500">{{ form.errors.scta_address }}</p>
                    </div>
    
                    <div>
                      <label for="scta_contact_email" class="block text-sm font-medium text-slate-700 mb-1">お問い合わせ先メールアドレス <span class="text-red-500 font-bold">*</span></label>
                      <input id="scta_contact_email" v-model="form.scta_contact_email" type="email" placeholder="contact@example.com"
                             class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                      <p v-if="form.errors.scta_contact_email" class="mt-1 text-sm text-red-500">{{ form.errors.scta_contact_email }}</p>
                    </div>
    
                    <div>
                      <label for="scta_contact_tel_disclaimer" class="block text-sm font-medium text-slate-700 mb-1">電話番号に関する注釈（任意）</label>
                      <input id="scta_contact_tel_disclaimer" v-model="form.scta_contact_tel_disclaimer" type="text" placeholder="例：電話番号はメールにてご請求いただければ遅滞なく開示いたします。"
                             class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
                    </div>

                <div class="md:col-span-2 space-y-4">
                    <h4 class="text-sm font-bold text-slate-600 border-b pb-1">以下の項目は未入力の場合、システム既定文が表示されます</h4>
                    
                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">販売価格の説明</label>
                      <textarea v-model="form.scta_price_description" rows="2" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="空欄時は「各宿泊プランに表示される価格による」等が表示されます"></textarea>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">支払方法・時期</label>
                      <textarea v-model="form.scta_payment_method_time" rows="2" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="空欄時は「クレジットカード決済（Stripe）」に関する説明が表示されます"></textarea>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-slate-700 mb-1">サービス提供時期</label>
                      <textarea v-model="form.scta_service_delivery_time" rows="2" class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition" placeholder="空欄時は「予約された宿泊日程による」旨が表示されます"></textarea>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-between pt-6 border-t border-slate-100">
            <Link :href="route('owner.dashboard')" class="px-6 py-3 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition">
              キャンセル
            </Link>
            <button type="submit" :disabled="form.processing"
                    class="px-8 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition disabled:opacity-50">
              <span v-if="form.processing">保存中...</span>
              <span v-else>設定を保存</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';

const CANCELLATION_POLICY_TEMPLATE = `【キャンセルポリシー】
・宿泊日の7日前まで：無料
・宿泊日の3〜6日前：宿泊料金の30%
・宿泊日の前日：宿泊料金の50%
・宿泊日の当日：宿泊料金の80%
・無連絡キャンセル：宿泊料金の100%

※キャンセルのご連絡は、お電話またはメールにてお願いいたします。`;

const applyCancellationTemplate = () => {
    if (form.cancellation_policy && form.cancellation_policy.trim() !== '') {
        if (!confirm('現在入力されている内容をテンプレートで上書きしますか？')) {
            return;
        }
    }
    form.cancellation_policy = CANCELLATION_POLICY_TEMPLATE;
};

const TERMS_TEMPLATE = `【宿泊約款】

第1条（宿泊契約の成立）
当施設が予約を承諾した時点で宿泊契約が成立するものとします。

第2条（チェックイン・チェックアウト）
チェックイン・チェックアウトの時間は施設の定める時間に従ってください。到着が遅れる場合は事前にご連絡ください。

第3条（利用規則の遵守）
ご宿泊のお客様は、施設内に掲示する利用規則に従っていただきます。

第4条（客室の利用）
お客様が客室をご利用いただける時間は、チェックインからチェックアウトまでとします。

第5条（禁止事項）
施設内では以下の行為を禁止いたします。
・火気の使用（所定の場所を除く）
・騒音等、他のお客様への迷惑行為
・施設の設備・備品の持ち出し
・ペットの持ち込み（許可がある場合を除く）
・施設敷地内での無断撮影（商用目的）

第6条（損害賠償）
お客様の故意または過失により施設が損害を被った場合は、相当額を賠償していただきます。

第7条（個人情報の取扱い）
ご提供いただいた個人情報は、宿泊サービスの提供および法令に基づく対応のために利用し、適切に管理いたします。

第8条（免責）
天災地変その他不可抗力により宿泊サービスの提供ができない場合、施設は責任を負いません。`;

const applyTermsTemplate = () => {
    if (form.terms_text && form.terms_text.trim() !== '') {
        if (!confirm('現在入力されている内容をテンプレートで上書きしますか？')) {
            return;
        }
    }
    form.terms_text = TERMS_TEMPLATE;
};

const props = defineProps({
    facility: Object,
});

const previews = ref({
    image: null,
    image_2: null,
    image_3: null,
});

const form = useForm({
    _method: 'PUT',
    name: props.facility.name || '',
    name_en: props.facility.name_en || '',
    address: props.facility.address || '',
    address_en: props.facility.address_en || '',
    phone: props.facility.phone || '',
    description: props.facility.description || '',
    description_en: props.facility.description_en || '',
    booking_cutoff_hours: props.facility.booking_cutoff_hours || 0,
    check_in_time_start: props.facility.check_in_time_start || '15:00',
    check_in_time_end: props.facility.check_in_time_end || '22:00',
    check_out_time: props.facility.check_out_time || '10:00',
    cancellation_policy: props.facility.cancellation_policy || '',
    terms_text: props.facility.terms_text || '',
    is_published: props.facility.is_published == 1,
    image: null,
    image_2: null,
    image_3: null,
    slug: props.facility.slug || '',
    
    // 特定商取引法関連
    scta_business_name: props.facility.scta_business_name || '',
    scta_representative: props.facility.scta_representative || '',
    scta_address: props.facility.scta_address || '',
    scta_contact_email: props.facility.scta_contact_email || '',
    scta_contact_tel_disclaimer: props.facility.scta_contact_tel_disclaimer || '',
    scta_price_description: props.facility.scta_price_description || '',
    scta_payment_method_time: props.facility.scta_payment_method_time || '',
    scta_service_delivery_time: props.facility.scta_service_delivery_time || '',
});

const handleImageChange = (e, slot) => {
    const file = e.target.files[0];
    if (!file) return;

    form[slot] = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        previews.value[slot] = e.target.result;
    };
    reader.readAsDataURL(file);
};

const clearImage = (slot) => {
    form[slot] = null;
    previews.value[slot] = null;
};

const submit = () => {
    form.post(route('owner.facility.update'), {
        forceFormData: true,
        onSuccess: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        onError: () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
    });
};
</script>
