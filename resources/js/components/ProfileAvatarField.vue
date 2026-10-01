<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Label } from '@/components/ui/label';
import { useTranslation } from '@/composables/useTranslation';

defineProps<{
    avatarUrl?: string | null;
    error?: string;
    removalError?: string;
}>();
const { t } = useTranslation();
const preview = ref<string | null>(null);
const remove = ref(false);

function selectImage(event: Event) {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }

    const file = (event.target as HTMLInputElement).files?.[0];
    preview.value = file ? URL.createObjectURL(file) : null;

    if (file) {
        remove.value = false;
    }
}

onBeforeUnmount(() => {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
    }
});
</script>

<template>
    <div class="grid gap-2">
        <Label for="avatar">
            {{ t('Profile avatar') }}
            <span class="text-muted-foreground">{{ t('(optional)') }}</span>
        </Label>
        <img
            v-if="preview || (avatarUrl && !remove)"
            :src="preview ?? avatarUrl ?? ''"
            :alt="t('Profile avatar')"
            class="size-20 rounded-full object-cover object-center"
        />
        <input
            id="avatar"
            name="avatar"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="block h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50"
            @change="selectImage"
        />
        <p class="text-sm text-muted-foreground">
            {{ t('JPEG, PNG, or WebP, up to 5 MB.') }}
        </p>
        <InputError :message="error" />
        <label v-if="avatarUrl" class="flex items-center gap-2 text-sm">
            <input
                v-model="remove"
                type="checkbox"
                name="remove_avatar"
                value="1"
            />
            {{ t('Remove avatar') }}
        </label>
        <InputError :message="removalError" />
    </div>
</template>
