import { router } from '@inertiajs/vue3';

let refreshing = false;
let pending = false;

export function refreshUnreadMessages(): void {
    if (refreshing) {
        pending = true;

        return;
    }

    refreshing = true;
    router.reload({
        only: ['unreadMessageCount', 'matches'],
        onFinish: () => {
            refreshing = false;

            if (pending) {
                pending = false;
                refreshUnreadMessages();
            }
        },
    });
}
