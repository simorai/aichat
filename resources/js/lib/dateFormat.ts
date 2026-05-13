export function formatConversationDate(iso: string): string {
    const date = new Date(iso);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffMs / 86_400_000);

    if (diffDays === 0) {
        return date.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    if (diffDays === 1) {
        return 'Yesterday';
    }

    if (diffDays < 7) {
        return date.toLocaleDateString('en-GB', { weekday: 'short' });
    }

    return date.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
    });
}
