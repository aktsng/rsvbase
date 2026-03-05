import { ref, computed } from 'vue';
import ja from '@/i18n/ja.js';
import en from '@/i18n/en.js';

const messages = { ja, en };

// ブラウザ言語を判定（ja から始まる場合は日本語、それ以外は英語）
const detectLocale = () => {
    const lang = (navigator.language || navigator.userLanguage || 'ja').toLowerCase();
    return lang.startsWith('ja') ? 'ja' : 'en';
};

// グローバルに共有されるリアクティブなlocale
const currentLocale = ref(detectLocale());

/**
 * i18n composable
 * 
 * Usage:
 *   const { t, locale, isEn, localizedField } = useI18n();
 *   t('check_in')  // => 'チェックイン' or 'Check-in'
 *   localizedField(facility, 'name') // => facility.name_en || facility.name
 */
export function useI18n() {
    const locale = computed(() => currentLocale.value);
    const isEn = computed(() => currentLocale.value === 'en');
    const isJa = computed(() => currentLocale.value === 'ja');

    /**
     * 翻訳キーからテキストを取得
     */
    const t = (key) => {
        const dict = messages[currentLocale.value] || messages.ja;
        return dict[key] ?? key;
    };

    /**
     * 施設データの英語フィールドフォールバック
     * 英語環境で_enフィールドがある場合はそれを使用、なければ日本語を表示
     * 
     * @param {Object} data - facility等のデータオブジェクト
     * @param {string} field - フィールド名（例: 'name', 'address', 'description'）
     */
    const localizedField = (data, field) => {
        if (!data) return '';
        if (currentLocale.value === 'en') {
            const enField = `${field}_en`;
            return data[enField] || data[field] || '';
        }
        return data[field] || '';
    };

    /**
     * ロケールを手動で切り替え
     */
    const setLocale = (loc) => {
        if (messages[loc]) {
            currentLocale.value = loc;
        }
    };

    return {
        locale,
        isEn,
        isJa,
        t,
        localizedField,
        setLocale,
    };
}
