<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/composables/useTranslation';

defineProps<{
    field: 'content_warnings' | 'content_boundaries';
    selected?: string[];
    errors?: Record<string, string>;
}>();
const { t } = useTranslation();
</script>

<template>
    <fieldset class="grid gap-3">
        <legend class="text-sm font-medium">
            {{
                t(
                    field === 'content_warnings'
                        ? 'Content warnings'
                        : 'Content boundaries',
                )
            }}
        </legend>
        <p class="text-sm text-muted-foreground">
            {{
                t(
                    field === 'content_warnings'
                        ? 'Select every applicable topic. No selection means no warnings declared.'
                        : 'Listings with any selected topic will be hidden from your feed. Only you can see these boundaries.',
                )
            }}
        </p>
        <Label
            v-for="(name, code) in $page.props.contentTopics"
            :key="code"
            class="gap-3 font-normal"
        >
            <input
                type="checkbox"
                :name="`${field}[]`"
                :value="code"
                :checked="selected?.includes(code)"
            />
            {{ name }}
        </Label>
        <InputError
            :message="
                errors?.[field] ??
                Object.entries(errors ?? {}).find(([key]) =>
                    key.startsWith(`${field}.`),
                )?.[1]
            "
        />
    </fieldset>
</template>
