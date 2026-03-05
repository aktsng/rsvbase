<template>
  <OwnerLayout>
    <Head title="部屋登録" />
    <template #header>
      <div class="flex items-center gap-4">
        <Link :href="route('owner.rooms.index')" class="p-2 hover:bg-slate-100 rounded-full transition text-slate-400">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
        </Link>
        新規部屋登録
      </div>
    </template>

    <div class="max-w-4xl mx-auto py-6">
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- 公開設定 -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-2">公開設定</label>
              <div class="flex gap-4">
                <label class="flex-1 relative cursor-pointer">
                  <input type="radio" v-model="form.is_active" :value="true" class="sr-only peer">
                  <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 transition flex items-center justify-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="font-bold text-slate-800">公開</span>
                  </div>
                </label>
                <label class="flex-1 relative cursor-pointer">
                  <input type="radio" v-model="form.is_active" :value="false" class="sr-only peer">
                  <div class="p-3 rounded-xl border-2 border-slate-200 peer-checked:border-slate-500 peer-checked:bg-slate-100 transition flex items-center justify-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span class="font-bold text-slate-800">非公開</span>
                  </div>
                </label>
              </div>
              <p class="text-xs text-slate-500 mt-2">非公開に設定すると、予約画面に表示されなくなります。</p>
            </div>

            <!-- 部屋名 -->
            <div class="md:col-span-2">
              <label for="name" class="block text-sm font-medium text-slate-700 mb-1">部屋名 <span class="text-red-500">*</span></label>
              <input id="name" v-model="form.name" type="text" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition font-bold"
                     placeholder="例：スタンダードツインルーム">
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- 定員 -->
            <div>
              <label for="capacity" class="block text-sm font-medium text-slate-700 mb-1">定員（名） <span class="text-red-500">*</span></label>
              <input id="capacity" v-model="form.capacity" type="number" min="1" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <p v-if="form.errors.capacity" class="mt-1 text-sm text-red-500">{{ form.errors.capacity }}</p>
            </div>

            <!-- 最小宿泊人数 -->
            <div>
              <label for="min_guests" class="block text-sm font-medium text-slate-700 mb-1">最小宿泊人数（名） <span class="text-red-500">*</span></label>
              <input id="min_guests" v-model="form.min_guests" type="number" min="1" required
                     class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition">
              <p class="text-xs text-slate-500 mt-1">大人の最低人数です。通常は1名です。</p>
            </div>

            <!-- 料金設定セクション -->
            <div class="md:col-span-2 pt-4">
              <h3 class="text-lg font-bold text-slate-800">料金設定</h3>
              <p class="text-sm text-slate-500 mt-1">特定日料金は別画面で設定できます。ここには常に適用される基本の料金を入力してください。</p>
            </div>

            <!-- 料金タイプ選択 -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-slate-700 mb-3">料金タイプ <span class="text-red-500">*</span></label>
              <div class="flex gap-4">
                <label class="flex-1 relative cursor-pointer">
                  <input type="radio" v-model="form.pricing_type" value="room" class="sr-only peer">
                  <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary-500 peer-checked:bg-primary-50 transition">
                    <div class="font-bold text-slate-800">室単価（ルームチャージ）</div>
                    <p class="text-xs text-slate-500 mt-1">1部屋あたりの料金。人数に関係なく一定です。</p>
                  </div>
                </label>
                <label class="flex-1 relative cursor-pointer">
                  <input type="radio" v-model="form.pricing_type" value="person" class="sr-only peer">
                  <div class="p-4 rounded-xl border-2 border-slate-200 peer-checked:border-primary-500 peer-checked:bg-primary-50 transition">
                    <div class="font-bold text-slate-800">人数単価（パーソンチャージ）</div>
                    <p class="text-xs text-slate-500 mt-1">大人1名あたりの基本料金 + 追加人数分の加算。</p>
                  </div>
                </label>
              </div>
              <p v-if="form.errors.pricing_type" class="mt-1 text-sm text-red-500">{{ form.errors.pricing_type }}</p>
            </div>

            <!-- 基本料金 -->
            <div>
              <label for="base_price_per_night" class="block text-sm font-medium text-slate-700 mb-1">
                {{ form.pricing_type === 'person' ? '基本料金 (大人1名・1泊)' : '基本料金 (1泊)' }}
                <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">¥</span>
                </div>
                <input id="base_price_per_night" v-model="form.base_price_per_night" type="number" min="0" required
                       class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                       placeholder="10000">
              </div>
              <p v-if="form.errors.base_price_per_night" class="mt-1 text-sm text-red-500">{{ form.errors.base_price_per_night }}</p>
            </div>

            <!-- 週末料金 -->
            <div>
              <label for="weekend_price_per_night" class="block text-sm font-medium text-slate-700 mb-1">
                {{ form.pricing_type === 'person' ? '週末料金 (大人1名・1泊・任意)' : '週末料金 (1泊・任意)' }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">¥</span>
                </div>
                <input id="weekend_price_per_night" v-model="form.weekend_price_per_night" type="number" min="0"
                       class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                       placeholder="12000">
              </div>
              <p class="text-xs text-slate-500 mt-1">金・土・祝前日に適用されます。空欄の場合は基本料金が適用されます。</p>
            </div>

            <!-- 清掃費 -->
            <div>
              <label for="cleaning_fee" class="block text-sm font-medium text-slate-700 mb-1">清掃費 (1滞在あたり) <span class="text-red-500">*</span></label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-slate-500 sm:text-sm">¥</span>
                </div>
                <input id="cleaning_fee" v-model="form.cleaning_fee" type="number" min="0" required
                       class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"
                       placeholder="3000">
              </div>
            </div>

            <template v-if="form.pricing_type === 'person'">

              <!-- 子供料金トグル -->
              <div class="md:col-span-2 mt-2">
                <label class="flex items-center gap-3 cursor-pointer">
                  <div class="relative">
                    <input type="checkbox" v-model="showChildSettings" class="sr-only peer">
                    <div class="w-11 h-6 bg-slate-200 rounded-full peer-checked:bg-primary-500 transition"></div>
                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transition peer-checked:translate-x-5"></div>
                  </div>
                  <span class="text-sm font-medium text-slate-700">子供料金を設定する</span>
                </label>
              </div>

              <!-- 子供A設定 -->
              <template v-if="showChildSettings">
                <div class="md:col-span-2 mt-2 p-4 bg-slate-50 rounded-xl border border-slate-200">
                  <h4 class="font-bold text-slate-700 mb-3">子供A</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">ラベル名</label>
                      <input v-model="form.child_a_label" type="text"
                             class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                             placeholder="例：小学生">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">ポリシー（表示テキスト）</label>
                      <input v-model="form.child_a_policy" type="text"
                             class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                             placeholder="例：12歳まで・定員に含む">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">追加料金 (1名)</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-slate-500 text-sm">¥</span>
                        </div>
                        <input v-model="form.add_child_a_fee" type="number" min="0"
                               class="block w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                               placeholder="3000">
                      </div>
                    </div>
                    <div class="flex items-center">
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.child_a_is_counted"
                               class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-slate-700">定員にカウントする</span>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- 子供B設定 -->
                <div class="md:col-span-2 p-4 bg-slate-50 rounded-xl border border-slate-200">
                  <h4 class="font-bold text-slate-700 mb-3">子供B</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">ラベル名</label>
                      <input v-model="form.child_b_label" type="text"
                             class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                             placeholder="例：乳幼児">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">ポリシー（表示テキスト）</label>
                      <input v-model="form.child_b_policy" type="text"
                             class="block w-full px-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                             placeholder="例：3歳まで・添い寝">
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-slate-600 mb-1">追加料金 (1名)</label>
                      <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                          <span class="text-slate-500 text-sm">¥</span>
                        </div>
                        <input v-model="form.add_child_b_fee" type="number" min="0"
                               class="block w-full pl-8 pr-3 py-2 border border-slate-200 rounded-lg bg-white text-sm"
                               placeholder="0">
                      </div>
                      <p v-if="Number(form.add_child_b_fee) === 0" class="text-xs text-green-600 mt-1">※ 無料</p>
                    </div>
                    <div class="flex items-center">
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" v-model="form.child_b_is_counted"
                               class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                        <span class="text-sm text-slate-700">定員にカウントする</span>
                      </label>
                    </div>
                  </div>
                </div>
              </template>
            </template>

            <!-- 説明 -->
            <div class="md:col-span-2 mt-4">
              <label for="description" class="block text-sm font-medium text-slate-700 mb-1">部屋の説明</label>
              <textarea id="description" v-model="form.description" rows="3"
                        class="block w-full px-4 py-2 border border-slate-200 rounded-xl leading-5 bg-slate-50 placeholder-slate-400 focus:outline-none focus:bg-white focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition"></textarea>
            </div>

            <!-- 部屋画像 -->
            <div class="md:col-span-2 mt-4 pt-6 border-t border-slate-100">
              <h3 class="text-lg font-bold text-slate-800">部屋画像 (最大3枚)</h3>
              <p class="text-sm text-slate-500 mt-1">
                お部屋の雰囲気が伝わる画像を登録してください。WebP形式に自動変換・圧縮（最大200KB）されます。
              </p>
              
              <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div v-for="i in 3" :key="i" class="space-y-2">
                  <label :for="'image' + i" class="block text-sm font-medium text-slate-600">画像 {{ i }}</label>
                  
                  <div class="relative aspect-video rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 overflow-hidden group hover:border-primary-400 transition cursor-pointer">
                    <!-- プレビュー表示 -->
                    <img v-if="previews[i-1]" :src="previews[i-1]" class="w-full h-full object-cover" />
                    <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                      <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span class="text-xs">クリックしてアップロード</span>
                    </div>

                    <input :id="'image' + i" type="file" @change="handleImageChange($event, i)" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer" />
                    
                    <!-- 削除ボタン -->
                    <button v-if="previews[i-1]" type="button" @click.stop="clearImage(i)"
                            class="absolute top-2 right-2 p-1 bg-white/90 rounded-full shadow-sm text-red-500 hover:text-red-700 hover:bg-white transition opacity-0 group-hover:opacity-100">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                  <p v-if="form.errors['image' + i]" class="mt-1 text-xs text-red-500">{{ form.errors['image' + i] }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-between pt-6 border-t border-slate-100">
            <Link :href="route('owner.rooms.index')" class="px-6 py-3 text-slate-600 font-medium hover:bg-slate-50 rounded-xl transition">
              キャンセル
            </Link>
            <button type="submit" :disabled="form.processing"
                    class="px-8 py-3 bg-primary-600 text-white font-semibold rounded-xl hover:bg-primary-700 transition disabled:opacity-50">
              <span v-if="form.processing">保存中...</span>
              <span v-else>部屋を追加</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </OwnerLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import OwnerLayout from '@/Layouts/OwnerLayout.vue';

const previews = ref([null, null, null]);
const showChildSettings = ref(false);

const form = useForm({
    name: '',
    capacity: 2,
    min_guests: 1,
    base_price_per_night: '',
    weekend_price_per_night: '',
    cleaning_fee: 0,
    description: '',
    is_active: true,
    pricing_type: 'room',
    child_a_label: '小学生',
    child_a_policy: '12歳まで・定員に含む',
    child_a_is_counted: true,
    add_child_a_fee: 0,
    child_b_label: '乳幼児',
    child_b_policy: '3歳まで・添い寝',
    child_b_is_counted: false,
    add_child_b_fee: 0,
    image1: null,
    image2: null,
    image3: null,
});

const handleImageChange = (e, index) => {
    const file = e.target.files[0];
    if (!file) return;

    form['image' + index] = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        previews.value[index - 1] = e.target.result;
    };
    reader.readAsDataURL(file);
};

const clearImage = (index) => {
    form['image' + index] = null;
    previews.value[index - 1] = null;
};

const submit = () => {
    form.post(route('owner.rooms.store'), {
        forceFormData: true,
    });
};
</script>
