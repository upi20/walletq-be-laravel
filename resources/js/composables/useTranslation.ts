import { computed } from 'vue';
import { useLanguage } from './useLanguage';
import { translations, type TranslationKeys } from '@/locales/translations';

export function useTranslation() {
  const { language } = useLanguage();

  // Get translation object for current language
  const t = computed(() => translations[language.value]);

  // Helper function to get nested translation
  const trans = (key: string): string => {
    try {
      const keys = key.split('.');
      let result: any = t.value;
      
      for (const k of keys) {
        result = result[k];
        if (result === undefined) {
          console.warn(`Translation key not found: ${key}`);
          return key;
        }
      }
      
      return result as string;
    } catch (error) {
      console.warn(`Translation error for key: ${key}`, error);
      return key;
    }
  };

  // Helper function with parameters (simple placeholder replacement)
  const transWithParams = (key: string, params: Record<string, string | number>): string => {
    let translation = trans(key);
    
    Object.entries(params).forEach(([param, value]) => {
      translation = translation.replace(new RegExp(`:${param}`, 'g'), String(value));
    });
    
    return translation;
  };

  return {
    t,
    trans,
    transWithParams,
    language
  };
}