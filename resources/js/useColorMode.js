import { useCallback, useState } from 'react';

const CLE_STOCKAGE = 'theme';

function lireModeInitial() {
    try {
        const enregistre = localStorage.getItem(CLE_STOCKAGE);
        if (enregistre === 'light' || enregistre === 'dark') {
            return enregistre;
        }
    } catch (e) {
        // stockage indisponible : on retombe sur la préférence du système
    }

    return window.matchMedia?.('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export default function useColorMode() {
    const [mode, setMode] = useState(lireModeInitial);

    const basculer = useCallback(() => {
        const suivant = mode === 'dark' ? 'light' : 'dark';
        setMode(suivant);

        try {
            localStorage.setItem(CLE_STOCKAGE, suivant);
        } catch (e) {
            // le choix ne sera simplement pas mémorisé
        }
    }, [mode]);

    return [mode, basculer];
}
