<script setup lang="ts">
import { ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/composables/useTranslation';

const props = defineProps<{ languages?: string[]; error?: string }>();
const selected = ref(props.languages ?? []);
const { t } = useTranslation();
</script>

<template>
    <fieldset class="grid gap-3">
        <legend class="text-sm font-medium">
            {{ t('Reading languages') }}
        </legend>
        <p class="text-sm text-muted-foreground">
            {{
                t(
                    'Choose every language in which you can comfortably beta-read.',
                )
            }}
        </p>
        <Label class="gap-3 font-normal">
            <input
                v-model="selected"
                name="reading_languages[]"
                type="checkbox"
                value="*"
                :disabled="selected.some((language) => language !== '*')"
            />
            {{ t('Any language') }}
        </Label>
        <Label
            v-for="(name, code) in $page.props.contentLanguages"
            :key="code"
            class="gap-3 font-normal"
        >
            <input
                v-model="selected"
                name="reading_languages[]"
                type="checkbox"
                :value="code"
                :disabled="selected.includes('*')"
            />
            {{ name }}
        </Label>
        <InputError :message="error" />
    </fieldset>
</template>
