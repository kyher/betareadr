<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { getInitials } from '@/composables/useInitials';

defineProps<{ profile: { display_name: string; avatar_url: string | null } }>();
</script>

<template>
    <Avatar>
        <AvatarImage
            v-if="profile.avatar_url"
            :src="profile.avatar_url"
            :alt="profile.display_name"
            class="object-cover object-center"
        />
        <AvatarFallback>{{ getInitials(profile.display_name) }}</AvatarFallback>
    </Avatar>
</template>
