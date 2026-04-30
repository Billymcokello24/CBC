<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, User as UserIcon } from 'lucide-vue-next';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';

type Props = {
    user: User;
};

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="flex w-full items-center cursor-pointer px-3 py-2.5 transition-colors hover:bg-muted/50" :href="edit()" prefetch>
                <UserIcon class="mr-3 h-4 w-4 text-primary" />
                <span class="text-[10px] font-bold tracking-widest uppercase">Profile</span>
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="flex w-full items-center cursor-pointer px-3 py-2.5 transition-colors hover:bg-muted/50" :href="edit()" prefetch>
                <Settings class="mr-3 h-4 w-4 text-slate-400" />
                <span class="text-[10px] font-bold tracking-widest uppercase">Settings</span>
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true" class="p-0">
        <Link
            class="flex w-full items-center cursor-pointer px-3 py-3 text-rose-500 transition-all hover:bg-rose-50 hover:text-rose-600 active:scale-[0.98]"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-3 h-4 w-4" />
            <span class="text-[10px] font-black tracking-widest uppercase">Sign Out</span>
        </Link>
    </DropdownMenuItem>
</template>
