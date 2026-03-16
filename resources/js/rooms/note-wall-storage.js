export const createNoteWallStorage = (roomSlug) => {
    const storageNameKey = `egoboard.author.${roomSlug}`;
    const storageParticipantKey = `egoboard.participant.${roomSlug}`;

    const getParticipantKey = () => localStorage.getItem(storageParticipantKey) ?? '';
    const getAuthorName = () => localStorage.getItem(storageNameKey) ?? '';

    const ensureParticipantKey = () => {
        let key = getParticipantKey();

        if (!key) {
            key = window.crypto?.randomUUID?.() ?? `participant-${Date.now()}-${Math.random().toString(36).slice(2, 10)}`;
            localStorage.setItem(storageParticipantKey, key);
        }

        return key;
    };

    const saveAuthorName = (name) => {
        localStorage.setItem(storageNameKey, name);
    };

    return {
        ensureParticipantKey,
        getParticipantKey,
        getAuthorName,
        saveAuthorName,
    };
};
